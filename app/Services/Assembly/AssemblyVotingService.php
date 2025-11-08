<?php

namespace App\Services\Assembly;

use App\Models\Assembly;
use App\Models\AssemblyItem;
use App\Models\AssemblyVote;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class AssemblyVotingService
{
    public function __construct(
        private readonly DatabaseManager $db
    ) {
    }

    public function recordVote(Assembly $assembly, AssemblyItem $item, User $voter, string $choice, ?string $comment = null): AssemblyVote
    {
        if ($assembly->id !== $item->assembly_id) {
            throw ValidationException::withMessages([
                'item' => 'O item informado não pertence à assembleia.',
            ]);
        }

        if ($assembly->condominium_id !== $voter->condominium_id) {
            throw ValidationException::withMessages([
                'user' => 'Votante não pertence ao condomínio da assembleia.',
            ]);
        }

        if (!$assembly->isVotingOpen()) {
            throw ValidationException::withMessages([
                'assembly' => 'A votação não está aberta para esta assembleia.',
            ]);
        }

        if (!$item->isOpen() && !($item->status === 'pending' && $assembly->isVotingOpen())) {
            throw ValidationException::withMessages([
                'item' => 'A votação para este item não está aberta.',
            ]);
        }

        if (!$this->userCanVote($assembly, $voter)) {
            throw ValidationException::withMessages([
                'user' => 'Você não possui permissão para votar nesta assembleia.',
            ]);
        }

        $availableOptions = $item->availableOptions();
        if (!in_array($choice, $availableOptions, true)) {
            throw ValidationException::withMessages([
                'choice' => 'Opção de voto inválida. Opções disponíveis: ' . implode(', ', $availableOptions),
            ]);
        }

        if (!$assembly->allow_comments) {
            $comment = null;
        }

        return $this->db->transaction(function () use ($assembly, $item, $voter, $choice, $comment) {
            $existingVote = AssemblyVote::query()
                ->where('assembly_item_id', $item->id)
                ->where('voter_id', $voter->id)
                ->lockForUpdate()
                ->first();

            if ($existingVote) {
                throw ValidationException::withMessages([
                    'vote' => 'Você já registrou voto para este item.',
                ]);
            }

            return AssemblyVote::create([
                'assembly_id' => $assembly->id,
                'assembly_item_id' => $item->id,
                'voter_id' => $voter->id,
                'unit_id' => $voter->unit_id,
                'choice' => $choice,
                'encrypted_choice' => $assembly->voting_type === 'secret' ? encrypt($choice) : null,
                'comment' => $comment,
            ]);
        });
    }

    public function revokeVote(Assembly $assembly, AssemblyItem $item, User $voter): void
    {
        $vote = AssemblyVote::query()
            ->where('assembly_item_id', $item->id)
            ->where('voter_id', $voter->id)
            ->first();

        if (!$vote) {
            throw ValidationException::withMessages([
                'vote' => 'Nenhum voto encontrado para remoção.',
            ]);
        }

        if (!$assembly->allow_delegation) {
            throw ValidationException::withMessages([
                'assembly' => 'Não é permitido remover votos nesta assembleia.',
            ]);
        }

        $vote->delete();
    }

    protected function userCanVote(Assembly $assembly, User $user): bool
    {
        $allowedRoles = $assembly->allowedRoles()
            ->pluck('name')
            ->merge(Arr::wrap($assembly->voter_scope))
            ->filter()
            ->unique()
            ->values();

        if ($allowedRoles->isEmpty()) {
            // fallback padrão
            $allowedRoles = collect(['Morador', 'Agregado', 'Síndico']);
        }

        return $user->roles()->whereIn('name', $allowedRoles)->exists();
    }
}

