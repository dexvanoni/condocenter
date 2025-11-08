<?php

namespace App\Services\Assembly;

use App\Models\AssemblyItem;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AssemblyItemService
{
    public function openItem(AssemblyItem $item, User $actor, array $context = []): AssemblyItem
    {
        if ($item->status === 'open') {
            return $item;
        }

        if ($item->status === 'closed') {
            throw ValidationException::withMessages([
                'item' => 'Não é possível reabrir um item já encerrado.',
            ]);
        }

        $payload = [
            'status' => 'open',
            'opens_at' => $context['opens_at'] ?? now(),
        ];

        if (isset($context['closes_at'])) {
            $payload['closes_at'] = $context['closes_at'];
        }

        $item->update($payload);

        return $item->fresh();
    }

    public function closeItem(AssemblyItem $item, User $actor, array $context = []): AssemblyItem
    {
        if ($item->status === 'closed') {
            return $item;
        }

        if ($item->status !== 'open') {
            throw ValidationException::withMessages([
                'item' => 'Apenas itens abertos podem ser encerrados.',
            ]);
        }

        $item->update([
            'status' => 'closed',
            'closes_at' => $context['closes_at'] ?? now(),
        ]);

        return $item->fresh();
    }
}

