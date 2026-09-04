<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\OccurrenceBookEntry;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class OccurrenceBookService
{
    public function __construct(
        private readonly OccurrenceBookNotificationService $notifications,
    ) {}

    public function create(User $author, array $data, ?UploadedFile $photo = null): OccurrenceBookEntry
    {
        $photoPath = null;

        if ($photo) {
            $photoPath = $photo->store(
                'occurrence-book/'.$author->tenantCondominiumId(),
                'public'
            );
        }

        $entry = OccurrenceBookEntry::create([
            'condominium_id' => $author->tenantCondominiumId(),
            'user_id' => $author->id,
            'unit_id' => $author->unit_id,
            'type' => $data['type'],
            'title' => $data['title'],
            'body' => $data['body'],
            'photo_path' => $photoPath,
            'notify_whatsapp' => (bool) ($data['notify_whatsapp'] ?? false),
        ]);

        $this->notifications->notifySyndics($entry);

        return $entry;
    }

    public function acknowledge(OccurrenceBookEntry $entry, User $syndic, ?string $note = null): OccurrenceBookEntry
    {
        $entry->update([
            'acknowledged_at' => now(),
            'acknowledged_by' => $syndic->id,
            'acknowledgment_note' => $note,
        ]);

        $this->notifications->notifyResidentAcknowledged($entry->fresh(), $syndic);

        return $entry->fresh(['author', 'unit', 'acknowledgedBy']);
    }

    public function updatePublicSetting(User $syndic, bool $enabled): Condominium
    {
        $condominium = Condominium::query()->findOrFail($syndic->tenantCondominiumId());
        $condominium->update(['occurrence_book_public_enabled' => $enabled]);

        return $condominium->fresh();
    }

    public function saveSyndicComment(
        OccurrenceBookEntry $entry,
        User $syndic,
        string $comment,
        bool $showPublicly = false,
    ): OccurrenceBookEntry {
        $entry->update([
            'syndic_comment' => $comment,
            'show_syndic_comment_publicly' => $showPublicly,
            'syndic_commented_at' => now(),
            'syndic_commented_by' => $syndic->id,
        ]);

        return $entry->fresh(['syndicCommentedBy']);
    }

    public function paginatePublicBook(User $user, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        if (!$this->isPublicBookEnabled($user->tenantCondominiumId())) {
            abort(404);
        }

        return $this->publicQuery($user, $filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function isPublicBookEnabled(?int $condominiumId): bool
    {
        if (!$condominiumId) {
            return false;
        }

        return (bool) Condominium::query()
            ->whereKey($condominiumId)
            ->value('occurrence_book_public_enabled');
    }

    public function pendingCountForCondominium(int $condominiumId): int
    {
        return OccurrenceBookEntry::query()
            ->forCondominium($condominiumId)
            ->pendingAcknowledgment()
            ->count();
    }

    public function paginateForResident(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return OccurrenceBookEntry::query()
            ->with(['acknowledgedBy'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function paginateForSyndic(User $syndic, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->syndicQuery($syndic, $filters)
            ->with(['author', 'unit', 'acknowledgedBy'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function exportCollection(User $syndic, array $filters = []): Collection
    {
        return $this->syndicQuery($syndic, $filters)
            ->with(['author', 'unit', 'acknowledgedBy'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function statsForSyndic(User $syndic): array
    {
        $base = OccurrenceBookEntry::query()->forCondominium($syndic->tenantCondominiumId());

        return [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->pendingAcknowledgment()->count(),
            'occurrences' => (clone $base)->where('type', OccurrenceBookEntry::TYPE_OCCURRENCE)->count(),
            'criticisms' => (clone $base)->where('type', OccurrenceBookEntry::TYPE_CRITICISM)->count(),
            'suggestions' => (clone $base)->where('type', OccurrenceBookEntry::TYPE_SUGGESTION)->count(),
        ];
    }

    private function syndicQuery(User $syndic, array $filters): Builder
    {
        $query = OccurrenceBookEntry::query()->forCondominium($syndic->tenantCondominiumId());

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status']) && $filters['status'] === 'pending') {
            $query->pendingAcknowledgment();
        }

        if (!empty($filters['status']) && $filters['status'] === 'acknowledged') {
            $query->whereNotNull('acknowledged_at');
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function (Builder $builder) use ($term) {
                $builder->where('title', 'like', $term)
                    ->orWhere('body', 'like', $term);
            });
        }

        return $query;
    }

    private function publicQuery(User $user, array $filters): Builder
    {
        $query = OccurrenceBookEntry::query()
            ->forCondominium($user->tenantCondominiumId())
            ->select([
                'id',
                'condominium_id',
                'type',
                'title',
                'body',
                'acknowledged_at',
                'syndic_comment',
                'show_syndic_comment_publicly',
                'created_at',
            ]);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function (Builder $builder) use ($term) {
                $builder->where('title', 'like', $term)
                    ->orWhere('body', 'like', $term);
            });
        }

        return $query;
    }
}
