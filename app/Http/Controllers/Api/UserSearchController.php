<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
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

        $condominiumId = (int) $authUser->tenantCondominiumId();
        $matchingUnitIds = $this->matchingUnitIds($condominiumId, $term);
        $ownerUserIds = $matchingUnitIds === []
            ? []
            : Unit::query()
                ->whereIn('id', $matchingUnitIds)
                ->whereNotNull('owner_user_id')
                ->pluck('owner_user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

        $query = User::query()
            ->select('id', 'name', 'cpf', 'email', 'unit_id')
            ->with(['unit:id,block,number,condominium_id'])
            ->byCondominium($condominiumId)
            ->where(function (Builder $q) use ($term, $canViewSensitiveData, $matchingUnitIds, $ownerUserIds) {
                $q->where('name', 'like', "%{$term}%");

                if ($canViewSensitiveData) {
                    $q->orWhere('cpf', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                }

                if ($matchingUnitIds !== []) {
                    $q->orWhereIn('unit_id', $matchingUnitIds);
                }

                if ($ownerUserIds !== []) {
                    $q->orWhereIn('id', $ownerUserIds);
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
                'unit_label' => $user->unit?->full_identifier,
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

    /**
     * @return list<int>
     */
    protected function matchingUnitIds(int $condominiumId, string $term): array
    {
        $normalized = trim($term);
        if ($normalized === '') {
            return [];
        }

        $query = Unit::query()
            ->where('condominium_id', $condominiumId)
            ->where(function (Builder $q) use ($normalized) {
                $q->where('number', 'like', "%{$normalized}%")
                    ->orWhere('block', 'like', "%{$normalized}%");

                if (str_contains($normalized, '-')) {
                    $parts = array_map('trim', explode('-', $normalized, 2));
                    if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
                        $q->orWhere(function (Builder $pair) use ($parts) {
                            $pair->where('block', 'like', "%{$parts[0]}%")
                                ->where('number', 'like', "%{$parts[1]}%");
                        });
                    }
                }
            });

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
