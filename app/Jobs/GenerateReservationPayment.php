<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\Charge;
use App\Services\AsaasService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReservationPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reservation;
    public $space;

    /**
     * Create a new job instance.
     */
    public function __construct(Reservation $reservation, Space $space)
    {
        $this->reservation = $reservation;
        $this->space = $space;
    }

    /**
     * Execute the job.
     */
    public function handle(AsaasService $asaasService): void
    {
        try {
            $user = $this->reservation->user;
            $amount = $this->space->price_per_hour;

            // Criar cobrança local primeiro
            $charge = Charge::create([
                'condominium_id' => $this->reservation->unit->condominium_id,
                'unit_id' => $this->reservation->unit_id,
                'title' => "Taxa de Reserva - {$this->space->name}",
                'description' => "Reserva do(a) {$this->space->name} para o dia {$this->reservation->reservation_date->format('d/m/Y')}",
                'amount' => $amount,
                'due_date' => $this->reservation->reservation_date->copy()->subDays(1), // 1 dia antes
                'recurrence_period' => $this->reservation->reservation_date->format('Y-m-d'),
                'fine_percentage' => 0, // Sem multa para taxa de reserva
                'interest_rate' => 0, // Sem juros
                'type' => 'extra',
                'status' => 'pending',
                'generated_by' => 'reservation',
                'metadata' => [
                    'reservation_id' => $this->reservation->id,
                    'space_id' => $this->space->id,
                ],
            ]);

            // Criar ou atualizar cliente no Asaas
            $customerData = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'mobilePhone' => $user->phone,
                'cpfCnpj' => $user->cpf,
                'externalReference' => 'USER-' . $user->id,
            ];

            $asaasCustomer = $asaasService->createOrUpdateCustomer($customerData);

            if (!$asaasCustomer) {
                throw new \Exception('Falha ao criar cliente no Asaas');
            }

            // Criar cobrança no Asaas
            $paymentData = [
                'customer' => $asaasCustomer['id'],
                'billingType' => 'PIX', // Pode ser alterado para CREDIT_CARD, BOLETO
                'dueDate' => $charge->due_date->format('Y-m-d'),
                'value' => $amount,
                'description' => $charge->title,
                'externalReference' => 'RESERVATION-' . $this->reservation->id,
            ];

            $payment = $asaasService->createPayment($paymentData);

            if (!$payment) {
                throw new \Exception('Falha ao criar pagamento no Asaas');
            }

            // Atualizar cobrança com dados do Asaas
            $charge->update([
                'asaas_payment_id' => $payment['id'],
                'boleto_url' => $payment['bankSlipUrl'] ?? null,
            ]);

            // Gerar PIX QR Code
            $pixData = $asaasService->getPixQRCode($payment['id']);
            
            if ($pixData) {
                $charge->update([
                    'pix_code' => $pixData['payload'] ?? null,
                    'pix_qrcode' => $pixData['encodedImage'] ?? null,
                ]);
            }

            Log::info("Cobrança de reserva gerada via Asaas", [
                'reservation_id' => $this->reservation->id,
                'charge_id' => $charge->id,
                'asaas_payment_id' => $payment['id'],
                'amount' => $amount,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar pagamento de reserva: ' . $e->getMessage(), [
                'reservation_id' => $this->reservation->id,
                'space_id' => $this->space->id,
            ]);

            // Retentar
            $this->release(60);
        }
    }
}
