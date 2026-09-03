<?php

namespace App\Services;

use App\Models\Fine;
use App\Support\CondominiumDocuments;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class FineNoticeService
{
    public function download(Fine $fine): Response
    {
        $fine->load([
            'condominium',
            'appliedBy',
            'cancelledBy',
            'recipients.user.roles',
            'recipients.unit',
            'recipients.notifiedUser',
            'recipients.charge',
        ]);

        $condominium = $fine->condominium;
        $sindico = CondominiumDocuments::resolveSindico((int) $fine->condominium_id);

        $recipients = $fine->recipients->map(function ($recipient) {
            $infractor = $recipient->user;
            $notified = $recipient->notifiedUser;
            $isAgregado = $infractor?->isAgregado();

            return [
                'infractor_name' => $infractor?->name,
                'infractor_role' => $isAgregado ? 'Agregado' : 'Morador',
                'unit' => $recipient->unit?->full_identifier,
                'notified_name' => $notified?->name,
                'notified_label' => ($isAgregado && $notified?->id !== $infractor?->id)
                    ? 'Responsável notificado'
                    : 'Notificado',
                'charge_status' => $recipient->charge?->status,
                'charge_status_label' => $this->chargeStatusLabel($recipient->charge?->status),
            ];
        });

        $data = [
            'fine' => $fine,
            'condominium' => CondominiumDocuments::presentCondominium($condominium),
            'sindico' => ['name' => $sindico?->name],
            'recipients' => $recipients,
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'app_name' => config('app.name', 'SindCON'),
        ];

        $pdf = Pdf::loadView('fines.pdf.notice', $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = sprintf('multa_%s_%s.pdf', $fine->reference, now()->format('Y-m-d'));

        return $pdf->download($filename);
    }

    protected function chargeStatusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'overdue' => 'Em atraso',
            'paid' => 'Paga',
            'cancelled' => 'Cancelada',
            default => '—',
        };
    }
}
