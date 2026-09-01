<?php

namespace App\Services\Assembly;

use App\Models\Assembly;
use App\Models\User;
use Illuminate\Support\Collection;

class AssemblyResponseService
{
    public function sanitize(Assembly $assembly, User $user): Assembly
    {
        if (!$assembly->relationLoaded('items')) {
            return $assembly;
        }

        $canManage = $user->can('manage_assemblies')
            || $assembly->created_by === $user->id
            || $user->hasAnyRole(['Síndico', 'Administrador']);

        foreach ($assembly->items as $item) {
            if (!$item->relationLoaded('votes')) {
                continue;
            }

            $votes = $item->votes;

            if ($assembly->voting_type === 'secret' && !$canManage) {
                $item->setRelation('votes', $votes->map(function ($vote) use ($user) {
                    if ((int) $vote->voter_id === (int) $user->id) {
                        return $vote->makeHidden(['choice', 'encrypted_choice']);
                    }

                    return $vote->makeHidden(['choice', 'encrypted_choice', 'voter_id', 'comment'])
                        ->setRelation('voter', null)
                        ->setRelation('unit', null);
                }));

                continue;
            }

            if ($assembly->results_visibility === 'final_only' && $assembly->status !== 'completed' && !$canManage) {
                $item->setRelation('votes', collect());
            }
        }

        return $assembly;
    }

    public function sanitizeCollection(Collection $assemblies, User $user): Collection
    {
        return $assemblies->map(fn (Assembly $assembly) => $this->sanitize($assembly, $user));
    }
}
