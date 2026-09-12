<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinancialEntryGrouper
{
    /**
     * Agrupa lançamentos pela data da transação.
     *
     * @param  Collection<int, array<string, mixed>|object>  $entries
     */
    public static function groupByDate(Collection $entries, string $dateKey = 'transaction_date'): Collection
    {
        if ($entries->isEmpty()) {
            return collect();
        }

        return $entries
            ->groupBy(function ($entry) use ($dateKey) {
                $date = self::value($entry, $dateKey);

                return Carbon::parse($date)->format('Y-m-d');
            })
            ->map(function (Collection $group, string $groupDateKey) {
                $amounts = $group->map(fn ($entry) => (float) self::value($entry, 'amount', 0));
                $activeAmounts = $group
                    ->filter(fn ($entry) => ! self::isCancelled($entry))
                    ->map(fn ($entry) => (float) self::value($entry, 'amount', 0));
                $cancelledItems = $group->filter(fn ($entry) => self::isCancelled($entry));

                return [
                    'date' => Carbon::parse($groupDateKey),
                    'date_key' => $groupDateKey,
                    'count' => $group->count(),
                    'total' => round($amounts->sum(), 2),
                    'active_total' => round($activeAmounts->sum(), 2),
                    'cancelled_count' => $cancelledItems->count(),
                    'items' => $group->values(),
                ];
            })
            ->sortByDesc('date')
            ->values();
    }

    private static function value(mixed $entry, string $key, mixed $default = null): mixed
    {
        if (is_array($entry)) {
            return $entry[$key] ?? $default;
        }

        return $entry->{$key} ?? $default;
    }

    private static function isCancelled(mixed $entry): bool
    {
        if (is_array($entry)) {
            return (bool) ($entry['is_cancelled'] ?? false);
        }

        if (method_exists($entry, 'isCancelled')) {
            return $entry->isCancelled();
        }

        return false;
    }
}
