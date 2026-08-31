<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $term = (string) $request->get('term', '');
        $roles = (array) $request->get('roles', []);
        /** @var \App\Models\User $authUser */
        $authUser = $request->user();

        $query = User::query()
            ->select('id', 'name', 'cpf', 'email', 'unit_id')
            ->byCondominium($authUser->tenantCondominiumId())
            ->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('cpf', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->when(!empty($roles), function (Builder $q) use ($roles) {
                $q->whereHas('roles', fn ($r) => $r->whereIn('name', $roles));
            })
            ->limit(20);

        $users = $query->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'cpf' => $user->cpf,
                    'email' => $user->email,
                ];
            });

        return response()->json($users);
    }
}


