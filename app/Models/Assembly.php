<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Assembly extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id',
        'created_by',
        'title',
        'description',
        'agenda',
        'scheduled_at',
        'voting_opens_at',
        'voting_closes_at',
        'started_at',
        'ended_at',
        'duration_minutes',
        'status',
        'voting_type',
        'urgency',
        'allow_delegation',
        'allow_comments',
        'results_visibility',
        'voter_scope',
        'minutes',
        'minutes_pdf',
    ];

    protected $casts = [
        'agenda' => 'array',
        'scheduled_at' => 'datetime',
        'voting_opens_at' => 'datetime',
        'voting_closes_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'allow_delegation' => 'boolean',
        'allow_comments' => 'boolean',
        'results_visibility' => 'string',
        'voter_scope' => 'array',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(AssemblyItem::class);
    }

    public function votes()
    {
        return $this->hasMany(AssemblyVote::class);
    }

    public function attachments()
    {
        return $this->hasMany(AssemblyAttachment::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(AssemblyStatusLog::class);
    }

    public function allowedRoles()
    {
        return $this->belongsToMany(
            \Spatie\Permission\Models\Role::class,
            'assembly_allowed_roles'
        );
    }

    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);
    }

    public function markCancelled(?int $userId = null, array $context = []): void
    {
        $this->update([
            'status' => 'cancelled',
            'ended_at' => now(),
        ]);

        $this->statusLogs()->create([
            'changed_by' => $userId,
            'from_status' => 'in_progress',
            'to_status' => 'cancelled',
            'context' => $context,
        ]);
    }

    public function isVotingOpen(): bool
    {
        $now = now();

        if ($this->status === 'cancelled') {
            return false;
        }

        if ($this->voting_opens_at && $now->lt($this->voting_opens_at)) {
            return false;
        }

        if ($this->voting_closes_at && $now->gt($this->voting_closes_at)) {
            return false;
        }

        return in_array($this->status, ['scheduled', 'in_progress'], true);
    }

    public function syncAllowedRoles(array $roleIds): void
    {
        $this->allowedRoles()->sync($roleIds);
        $this->updateQuietly([
            'voter_scope' => $this->allowedRoles()
                ->pluck('name')
                ->values()
                ->all(),
        ]);
    }

    public function canUserVote(User $user): bool
    {
        $allowedRoles = $this->relationLoaded('allowedRoles')
            ? $this->allowedRoles->pluck('name')
            : $this->allowedRoles()->pluck('name');

        $scopedRoles = collect(Arr::wrap($this->voter_scope));

        $permitted = $allowedRoles
            ->merge($scopedRoles)
            ->filter()
            ->unique()
            ->values();

        if ($permitted->isEmpty()) {
            $permitted = collect(['Morador', 'Agregado', 'Síndico']);
        }

        return $user->roles()->whereIn('name', $permitted)->exists();
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'scheduled'
            && $this->scheduled_at
            && now()->greaterThanOrEqualTo($this->scheduled_at)
        ) {
            return 'in_progress';
        }

        return $this->status;
    }

    public function getVoteSummaryAttribute(): array
    {
        $this->loadMissing('items.votes');

        return $this->items
            ->mapWithKeys(function (AssemblyItem $item) {
                $totals = $item->votes
                    ->groupBy('choice')
                    ->map->count();

                $totalVotes = $item->votes->count();
                $threshold = $totalVotes > 0 ? intdiv($totalVotes, 2) + 1 : null;

                $breakdown = $totals
                    ->map(fn ($count, $choice) => [
                        'choice' => $choice,
                        'count' => $count,
                        'percentage' => $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0,
                    ])
                    ->sortByDesc('count')
                    ->values()
                    ->all();

                $leading = $breakdown[0] ?? null;
                $winner = null;

                if ($leading && $threshold && $leading['count'] >= $threshold) {
                    // ensure unique majority (> threshold or equals threshold but unique)
                    $isTie = collect($breakdown)
                        ->filter(fn ($detail) => $detail['count'] === $leading['count'])
                        ->count() > 1;

                    if (!$isTie) {
                        $winner = $leading;
                        $winner['threshold'] = $threshold;
                    }
                }

                return [
                    $item->id => [
                        'title' => $item->title,
                        'totals' => $totals->toArray(),
                        'total_votes' => $totalVotes,
                        'breakdown' => $breakdown,
                        'winner' => $winner,
                        'threshold' => $threshold,
                    ],
                ];
            })
            ->toArray();
    }

    public function getIsVotingOpenAttribute(): bool
    {
        return $this->isVotingOpen();
    }
}
