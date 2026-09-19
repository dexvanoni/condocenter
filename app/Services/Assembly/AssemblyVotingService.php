<?php

namespace App\Services\Assembly;

use App\Models\Assembly;
use App\Models\AssemblyItem;
use App\Models\AssemblyVote;
use App\Models\User;
use App\Services\UnitOccupancyService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class AssemblyVotingService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly UnitOccupancyService $unitOccupancyService,
    ) {
    }

    public function recordVote(
        Assembly $assembly,
        AssemblyItem $item,
        User $voter,
        string $choice,
        ?string $comment = null,
        ?int $unitId = null,
    ): AssemblyVote
    {
        if ($assembly->id !== $item->assembly_id) {
            throw ValidationException::withMessages([
                'item' => 'O item informado não pertence à assembleia.',
            ]);
        }

        if (!$voter->can('vote_assemblies')) {
            throw ValidationException::withMessages([
                'user' => 'Você não possui permissão para votar em assembleias.',
            ]);
        }

        $tenantId = $voter->tenantCondominiumId();
        if ($assembly->condominium_id !== $tenantId) {
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

        $voteUnitIds = $this->unitOccupancyService->assemblyVoteUnitIdsForVoter($voter, $unitId);
        $voteUnitId = $voteUnitIds[0] ?? null;

        if ($voteUnitId === null) {
            throw ValidationException::withMessages([
                'user' => 'Não foi possível identificar a unidade para o seu voto.',
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

        return $this->db->transaction(function () use ($assembly, $item, $voter, $choice, $comment, $voteUnitId) {
            $existingVote = AssemblyVote::query()
                ->where('assembly_item_id', $item->id)
                ->where('voter_id', $voter->id)
                ->where('unit_id', $voteUnitId)
                ->lockForUpdate()
                ->first();

            if ($existingVote) {
                throw ValidationException::withMessages([
                    'vote' => 'Você já registrou voto para este item.',
                ]);
            }

            $unitVoteExists = AssemblyVote::query()
                ->where('assembly_item_id', $item->id)
                ->where('unit_id', $voteUnitId)
                ->lockForUpdate()
                ->exists();

            if ($unitVoteExists) {
                throw ValidationException::withMessages([
                    'vote' => 'Já existe um voto registrado para esta unidade neste item.',
                ]);
            }

            $isSecret = $assembly->voting_type === 'secret';

            return AssemblyVote::create([
                'assembly_id' => $assembly->id,
                'assembly_item_id' => $item->id,
                'voter_id' => $voter->id,
                'unit_id' => $voteUnitId,
                'choice' => $isSecret ? 'confidential' : $choice,
                'encrypted_choice' => $isSecret ? encrypt($choice) : null,
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
            $allowedRoles = collect(['Morador', 'Proprietário', 'Síndico']);
        }

        if (!$user->roles()->whereIn('name', $allowedRoles)->exists()) {
            return false;
        }

        if ($user->isProprietario()) {
            return $this->unitOccupancyService->ownedRentalUnits($user, $user->tenantCondominiumId())->isNotEmpty();
        }

        if ($user->isMorador() && $user->unit_id) {
            return $this->unitOccupancyService->moradorCanVoteInAssembly($user);
        }

        return true;
    }
}
