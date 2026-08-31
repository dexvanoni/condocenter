<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AccountabilityReportUpload extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id',
        'uploaded_by',
        'month',
        'year',
        'file_path',
        'original_filename',
        'mime_type',
        'size',
        'notes',
        'council_status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'size' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public const COUNCIL_STATUS_PENDING = 'pending';
    public const COUNCIL_STATUS_APPROVED = 'approved';

    public const MONTH_NAMES = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isCouncilApproved(): bool
    {
        return $this->council_status === self::COUNCIL_STATUS_APPROVED;
    }

    public function isCouncilPending(): bool
    {
        return $this->council_status === self::COUNCIL_STATUS_PENDING;
    }

    public function councilStatusLabel(): string
    {
        return $this->isCouncilApproved()
            ? 'APROVADO PELO CONSELHO FISCAL'
            : 'AGUARDANDO CONSELHO FISCAL';
    }

    public function councilStatusBadgeClass(): string
    {
        return $this->isCouncilApproved() ? 'success' : 'warning';
    }

    public function getPeriodLabelAttribute(): string
    {
        $monthName = self::MONTH_NAMES[$this->month] ?? (string) $this->month;

        return "{$monthName}/{$this->year}";
    }

    public function getFormattedSizeAttribute(): string
    {
        if (!$this->size) {
            return '-';
        }

        if ($this->size >= 1048576) {
            return number_format($this->size / 1048576, 1, ',', '.') . ' MB';
        }

        return number_format($this->size / 1024, 1, ',', '.') . ' KB';
    }

    public function deleteStoredFile(): void
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
    }
}
