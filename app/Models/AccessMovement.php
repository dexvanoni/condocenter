<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessMovement extends Model
{
    public const SOURCE_AUTHORIZATION = 'authorization';
    public const SOURCE_LIST_ITEM = 'list_item';
    public const SOURCE_SERVICE_PROVIDER = 'service_provider';

    public const ACTION_ENTERED = 'entered';
    public const ACTION_DENIED = 'denied';

    protected $fillable = [
        'condominium_id',
        'unit_id',
        'notify_user_id',
        'authorized_by',
        'processed_by',
        'source_type',
        'source_id',
        'action',
        'visitor_name',
        'reference_label',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function notifyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notify_user_id');
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopeForCondominium($query, int $condominiumId)
    {
        return $query->where('condominium_id', $condominiumId);
    }

    public function actionLabel(): string
    {
        if ($this->isProhibitionAlert()) {
            return 'Alerta de proibição';
        }

        $label = $this->action === self::ACTION_ENTERED ? 'Entrou' : 'Acesso negado';

        if ($this->isEarlyEntry()) {
            $label .= ' (antecipada)';
        }

        return $label;
    }

    public function isProhibitionAlert(): bool
    {
        return (bool) ($this->metadata['prohibition_alert'] ?? false);
    }

    public function isEarlyEntry(): bool
    {
        return (bool) ($this->metadata['early_entry'] ?? false);
    }

    public function earlyEntryReportNote(): ?string
    {
        if ($this->isProhibitionAlert()) {
            return $this->prohibitionReportNote();
        }

        if (!$this->isEarlyEntry()) {
            return null;
        }

        $scheduledAt = $this->metadata['scheduled_at'] ?? null;
        $scheduledLabel = $scheduledAt
            ? \Carbon\Carbon::parse($scheduledAt)->format('d/m/Y H:i')
            : 'horário liberado';

        return "Entrada antes do horário liberado ({$scheduledLabel}). Porteiro confirmou autorização do morador.";
    }

    public function prohibitionReportNote(): ?string
    {
        if (!$this->isProhibitionAlert()) {
            return null;
        }

        $prohibitedBy = $this->metadata['prohibited_by'] ?? 'morador';
        $note = "Pessoa proibida tentou entrar. Proibição registrada por {$prohibitedBy}. Morador alertado pelo porteiro.";

        if (!empty($this->metadata['porteiro_notes'])) {
            $note .= ' Obs.: ' . $this->metadata['porteiro_notes'];
        }

        return $note;
    }
}
