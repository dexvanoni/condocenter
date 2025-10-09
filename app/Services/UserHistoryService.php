<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class UserHistoryService
{
    /**
     * Obtém histórico completo do usuário
     *
     * @param User $user
     * @return array
     */
    public function getCompleteHistory(User $user): array
    {
        return [
            'user' => $this->getUserData($user),
            'reservations' => $this->getReservationsHistory($user),
            'transactions' => $this->getTransactionsHistory($user),
            'charges' => $this->getChargesHistory($user),
            'payments' => $this->getPaymentsHistory($user),
            'assemblies' => $this->getAssembliesHistory($user),
            'messages' => $this->getMessagesHistory($user),
            'packages' => $this->getPackagesHistory($user),
            'pets' => $this->getPetsHistory($user),
            'marketplace' => $this->getMarketplaceHistory($user),
            'entries' => $this->getEntriesHistory($user),
            'activity_logs' => $this->getActivityLogs($user),
            'audits' => $this->getAudits($user),
        ];
    }

    /**
     * Dados básicos do usuário
     */
    protected function getUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'cpf' => $user->cpf,
            'phone' => $user->phone,
            'telefones' => [
                'residencial' => $user->telefone_residencial,
                'celular' => $user->telefone_celular,
                'comercial' => $user->telefone_comercial,
            ],
            'unit' => $user->unit ? [
                'id' => $user->unit->id,
                'identifier' => $user->unit->full_identifier,
                'address' => $user->unit->full_address,
            ] : null,
            'roles' => $user->roles->pluck('name')->toArray(),
            'data_entrada' => $user->data_entrada?->format('d/m/Y'),
            'idade' => $user->idade,
            'is_active' => $user->is_active,
            'possui_dividas' => $user->possui_dividas,
        ];
    }

    /**
     * Histórico de reservas
     */
    protected function getReservationsHistory(User $user): Collection
    {
        return $user->reservations()
            ->with('space')
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'space' => $r->space->name ?? 'N/A',
                'date' => $r->date->format('d/m/Y'),
                'start_time' => $r->start_time,
                'end_time' => $r->end_time,
                'status' => $r->status,
                'amount' => $r->amount,
                'created_at' => $r->created_at->format('d/m/Y H:i'),
            ]);
    }

    /**
     * Histórico de transações
     */
    protected function getTransactionsHistory(User $user): Collection
    {
        return $user->transactions()
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'description' => $t->description,
                'amount' => $t->amount,
                'date' => $t->date->format('d/m/Y'),
                'category' => $t->category,
                'created_at' => $t->created_at->format('d/m/Y H:i'),
            ]);
    }

    /**
     * Histórico de cobranças
     */
    protected function getChargesHistory(User $user): Collection
    {
        if (!$user->unit_id) {
            return collect();
        }

        return $user->unit->charges()
            ->orderBy('due_date', 'desc')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'type' => $c->type,
                'description' => $c->description,
                'amount' => $c->amount,
                'due_date' => $c->due_date->format('d/m/Y'),
                'status' => $c->status,
                'created_at' => $c->created_at->format('d/m/Y H:i'),
            ]);
    }

    /**
     * Histórico de pagamentos
     */
    protected function getPaymentsHistory(User $user): Collection
    {
        if (!$user->unit_id) {
            return collect();
        }

        return $user->unit->charges()
            ->whereNotNull('paid_at')
            ->with('payment')
            ->orderBy('paid_at', 'desc')
            ->get()
            ->map(fn($c) => [
                'charge_id' => $c->id,
                'description' => $c->description,
                'amount' => $c->amount,
                'paid_at' => $c->paid_at->format('d/m/Y'),
                'payment_method' => $c->payment?->payment_method ?? 'N/A',
            ]);
    }

    /**
     * Histórico de assembleias
     */
    protected function getAssembliesHistory(User $user): Collection
    {
        return $user->votes()
            ->with('assembly')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($v) => [
                'assembly_id' => $v->assembly->id,
                'title' => $v->assembly->title,
                'date' => $v->assembly->date->format('d/m/Y'),
                'vote' => $v->vote,
                'voted_at' => $v->created_at->format('d/m/Y H:i'),
            ]);
    }

    /**
     * Histórico de mensagens
     */
    protected function getMessagesHistory(User $user): Collection
    {
        return $user->sentMessages()
            ->with('toUser')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'to' => $m->toUser->name ?? 'N/A',
                'subject' => $m->subject,
                'sent_at' => $m->created_at->format('d/m/Y H:i'),
            ]);
    }

    /**
     * Histórico de encomendas
     */
    protected function getPackagesHistory(User $user): Collection
    {
        if (!$user->unit_id) {
            return collect();
        }

        return $user->unit->packages()
            ->orderBy('received_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'description' => $p->description,
                'sender' => $p->sender,
                'received_at' => $p->received_at->format('d/m/Y H:i'),
                'collected_at' => $p->collected_at?->format('d/m/Y H:i'),
                'status' => $p->status,
            ]);
    }

    /**
     * Histórico de pets
     */
    protected function getPetsHistory(User $user): Collection
    {
        return $user->pets()
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'type' => $p->type,
                'breed' => $p->breed,
                'registered_at' => $p->created_at->format('d/m/Y'),
            ]);
    }

    /**
     * Histórico do marketplace
     */
    protected function getMarketplaceHistory(User $user): Collection
    {
        return $user->marketplaceItems()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'title' => $i->title,
                'price' => $i->price,
                'status' => $i->status,
                'created_at' => $i->created_at->format('d/m/Y'),
            ]);
    }

    /**
     * Histórico de entradas/visitantes
     */
    protected function getEntriesHistory(User $user): Collection
    {
        if (!$user->unit_id) {
            return collect();
        }

        return $user->unit->entries()
            ->orderBy('entry_time', 'desc')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'visitor_name' => $e->visitor_name,
                'visitor_document' => $e->visitor_document,
                'entry_time' => $e->entry_time->format('d/m/Y H:i'),
                'exit_time' => $e->exit_time?->format('d/m/Y H:i'),
            ]);
    }

    /**
     * Logs de atividade
     */
    protected function getActivityLogs(User $user): Collection
    {
        return $user->activityLogs()
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(fn($l) => [
                'module' => $l->module,
                'action' => $l->action,
                'description' => $l->description,
                'created_at' => $l->created_at->format('d/m/Y H:i:s'),
            ]);
    }

    /**
     * Auditoria (via spatie/laravel-auditing)
     */
    protected function getAudits(User $user): Collection
    {
        return $user->audits()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($a) => [
                'event' => $a->event,
                'auditable_type' => class_basename($a->auditable_type),
                'old_values' => $a->old_values,
                'new_values' => $a->new_values,
                'created_at' => $a->created_at->format('d/m/Y H:i:s'),
            ]);
    }
}

