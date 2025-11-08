<?php

namespace App\Services\Assembly;

use App\Models\Assembly;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class AssemblyMinutesService
{
    public function generateAndPersistMinutes(Assembly $assembly): void
    {
        $assembly->loadMissing([
            'items.votes.voter.roles',
            'items.votes.unit',
            'attachments',
            'allowedRoles',
            'creator',
        ]);

        $minutesData = $this->buildMinutesData($assembly);
        $markdown = $this->renderMarkdown($minutesData);

        $assembly->update([
            'minutes' => $markdown,
        ]);

        $pdfPath = $this->generatePdf($assembly, $minutesData);

        $assembly->update([
            'minutes_pdf' => $pdfPath,
        ]);
    }

    public function buildMinutesData(Assembly $assembly): array
    {
        $items = $assembly->items->sortBy('position')->map(function ($item) use ($assembly) {
            $votes = $item->votes;
            $totals = $votes->groupBy('choice')->map->count();
            $totalVotes = $votes->count();

            $entries = $votes->map(function ($vote) use ($assembly) {
                $unitIdentifier = $vote->unit?->full_identifier ?? $vote->unit?->number ?? 'N/A';

                return [
                    'voter' => $assembly->voting_type === 'secret' ? 'Votante confidencial' : ($vote->voter->name ?? 'N/A'),
                    'unit' => $assembly->voting_type === 'secret' ? 'Confidencial' : $unitIdentifier,
                    'choice' => $assembly->voting_type === 'secret' ? 'Registrado em confidencial' : $vote->choice,
                    'comment' => $assembly->allow_comments ? $vote->comment : null,
                ];
            })->values();

            $threshold = $totalVotes > 0 ? intdiv($totalVotes, 2) + 1 : null;
            $breakdown = $totals->map(fn ($count, $choice) => [
                'choice' => $choice,
                'count' => $count,
                'percentage' => $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0,
            ])->sortByDesc('count')->values();

            $leading = $breakdown->first();
            $winner = null;
            if ($leading && $threshold && $leading['count'] >= $threshold) {
                $isTie = $breakdown->where('count', $leading['count'])->count() > 1;
                if (!$isTie) {
                    $winner = [
                        'choice' => $leading['choice'],
                        'count' => $leading['count'],
                        'percentage' => $leading['percentage'],
                        'threshold' => $threshold,
                    ];
                }
            }

            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'status' => $item->status,
                'totals' => [
                    'options' => $totals,
                    'total_votes' => $totalVotes,
                ],
                'breakdown' => $breakdown,
                'winner' => $winner,
                'threshold' => $threshold,
                'votes' => $entries,
            ];
        })->values();

        return [
            'assembly' => [
                'id' => $assembly->id,
                'title' => $assembly->title,
                'description' => $assembly->description,
                'status' => $assembly->status,
                'scheduled_at' => optional($assembly->scheduled_at)?->toIso8601String(),
                'started_at' => optional($assembly->started_at)?->toIso8601String(),
                'ended_at' => optional($assembly->ended_at)?->toIso8601String(),
                'voting_opens_at' => optional($assembly->voting_opens_at)?->toIso8601String(),
                'voting_closes_at' => optional($assembly->voting_closes_at)?->toIso8601String(),
                'voting_type' => $assembly->voting_type,
                'urgency' => $assembly->urgency,
                'allow_delegation' => $assembly->allow_delegation,
                'allow_comments' => $assembly->allow_comments,
                'voter_scope' => $assembly->allowedRoles->pluck('name')->values()->all(),
                'created_at' => optional($assembly->created_at)?->toIso8601String(),
                'updated_at' => optional($assembly->updated_at)?->toIso8601String(),
            ],
            'items' => $items,
            'attachments' => $assembly->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'collection' => $attachment->collection,
                    'original_name' => $attachment->original_name,
                    'url' => Storage::disk($attachment->disk ?? 'public')->url($attachment->path),
                ];
            })->values(),
        ];
    }

    public function renderMarkdown(array $minutesData): string
    {
        $assembly = $minutesData['assembly'];
        $items = $minutesData['items'];

        $lines = [];
        $lines[] = '# Ata da Assembleia';
        $lines[] = '';
        $lines[] = "**Título:** {$assembly['title']}";
        $lines[] = "**Situação:** {$assembly['status']}";
        $lines[] = "**Urgência:** {$assembly['urgency']}";

        if ($assembly['scheduled_at']) {
            $lines[] = '**Programada para:** ' . $this->formatDate($assembly['scheduled_at']);
        }

        if ($assembly['started_at']) {
            $lines[] = '**Iniciada em:** ' . $this->formatDate($assembly['started_at']);
        }

        if ($assembly['ended_at']) {
            $lines[] = '**Encerrada em:** ' . $this->formatDate($assembly['ended_at']);
        }

        $lines[] = '';
        $lines[] = '## Itens da Pauta';
        $lines[] = '';

        foreach ($items as $index => $item) {
            $lines[] = sprintf('### %d. %s', $index + 1, $item['title']);
            if (!empty($item['description'])) {
                $lines[] = $item['description'];
            }
            $lines[] = '';
            $lines[] = '**Resultados:**';
            foreach ($item['totals']['options'] as $option => $count) {
                $lines[] = "- {$option}: {$count}";
            }
            $lines[] = '- Total de votos: ' . $item['totals']['total_votes'];
            if ($item['winner']) {
                $lines[] = sprintf(
                    '- Resultado declaratório: %s com %d votos (%.1f%%). Maioria mínima: %d votos.',
                    $item['winner']['choice'],
                    $item['winner']['count'],
                    $item['winner']['percentage'],
                    $item['winner']['threshold']
                );
            } else {
                $lines[] = '- Resultado declaratório: Sem maioria absoluta (necessário 50%% + 1).';
            }
            $lines[] = '';
            $lines[] = '**Votos Registrados:**';

            if (empty($item['votes'])) {
                $lines[] = '- Nenhum voto registrado.';
            } else {
                foreach ($item['votes'] as $vote) {
                    $lines[] = sprintf(
                        '- %s (Unidade: %s) → %s%s',
                        $vote['voter'],
                        $vote['unit'],
                        $vote['choice'],
                        $vote['comment'] ? " — Comentário: {$vote['comment']}" : ''
                    );
                }
            }

            $lines[] = '';
        }

        if (!empty($minutesData['attachments'])) {
            $lines[] = '## Anexos';
            foreach ($minutesData['attachments'] as $attachment) {
                $lines[] = "- [{$attachment['original_name']}]({$attachment['url']})";
            }
            $lines[] = '';
        }

        return implode(PHP_EOL, $lines);
    }

    protected function formatDate(?string $isoDate): string
    {
        if (!$isoDate) {
            return 'N/A';
        }

        return \Carbon\Carbon::parse($isoDate)->timezone(config('app.timezone', 'America/Sao_Paulo'))->format('d/m/Y H:i');
    }

    protected function generatePdf(Assembly $assembly, array $minutesData): string
    {
        $minutesData['generated_at'] = now()->timezone(config('app.timezone', 'America/Sao_Paulo'))->format('d/m/Y H:i');
        $minutesData['assembly']['status_label'] = match ($assembly->status) {
            'scheduled' => 'Agendada',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
            default => ucfirst($assembly->status),
        };

        $pdf = Pdf::loadView('assemblies.pdf.minutes', $minutesData)->setPaper('a4');

        $directory = "assemblies/{$assembly->id}/minutes";
        Storage::disk('public')->makeDirectory($directory);
        $filename = "ata-assembleia-{$assembly->id}-" . now()->format('YmdHis') . '.pdf';
        $path = "{$directory}/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}

