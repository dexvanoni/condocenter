<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:user-search');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'min:2', 'max:100'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'max:50'],
        ]);

        $term = $validated['term'];
        $roles = $validated['roles'] ?? [];
        /** @var User $authUser */
        $authUser = $request->user();
        $canViewSensitiveData = $this->canViewSensitiveUserData($authUser);

        $query = User::query()
            ->select('id', 'name', 'cpf', 'email', 'unit_id')
            ->byCondominium($authUser->tenantCondominiumId())
            ->where(function (Builder $q) use ($term, $canViewSensitiveData) {
                $q->where('name', 'like', "%{$term}%");

                if ($canViewSensitiveData) {
                    $q->orWhere('cpf', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                }
            })
            ->when(!empty($roles), function (Builder $q) use ($roles) {
                $q->whereHas('roles', fn ($r) => $r->whereIn('name', $roles));
            })
            ->limit(20);

        $users = $query->get()->map(function (User $user) use ($canViewSensitiveData) {
            $payload = [
                'id' => $user->id,
                'name' => $user->name,
            ];

            if ($canViewSensitiveData) {
                $payload['cpf'] = $user->cpf;
                $payload['email'] = $user->email;
            }

            return $payload;
        });

        return response()->json($users);
    }

    protected function canViewSensitiveUserData(User $user): bool
    {
        return $user->isAdmin()
            || $user->isSindico()
            || $user->can('view_users')
            || $user->can('manage_users');
    }
}
