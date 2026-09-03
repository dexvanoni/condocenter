<?php

namespace App\Jobs;

use App\Models\Charge;
use App\Models\User;
use App\Services\AsaasService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAsaasPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Charge $charge,
        public User $customer,
        public string $billingType = 'PIX',
    ) {}

    public function handle(AsaasService $asaasService): void
    {
        try {
            $charge = $this->charge->loadMissing('condominium');
            $asaas = $asaasService->forCondominium((int) $charge->condominium_id);

            $customerData = [
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'mobilePhone' => $this->customer->phone,
                'cpfCnpj' => $this->customer->cpf,
                'postalCode' => $charge->condominium->zip_code,
                'address' => $charge->condominium->address,
                'addressNumber' => 'S/N',
                'province' => $charge->condominium->city,
                'externalReference' => 'USER-' . $this->customer->id,
            ];

            $asaasCustomer = $asaas->createOrUpdateCustomer($customerData);

            if (!$asaasCustomer) {
                throw new \Exception('Falha ao criar cliente no Asaas');
            }

            $paymentData = [
                'customer' => $asaasCustomer['id'],
                'billingType' => $this->billingType,
                'dueDate' => $charge->due_date->format('Y-m-d'),
                'value' => $charge->amount,
                'description' => $charge->title,
                'externalReference' => 'CHARGE-' . $charge->id,
            ];

            if ($charge->fine_percentage > 0) {
                $paymentData['fine'] = ['value' => (float) $charge->fine_percentage];
            }

            if ($charge->interest_rate > 0) {
                $paymentData['interest'] = ['value' => (float) $charge->interest_rate];
            }

            $payment = $asaas->createPayment($paymentData);

            if (!$payment) {
                throw new \Exception('Falha ao criar pagamento no Asaas');
            }

            $charge->update([
                'asaas_payment_id' => $payment['id'],
                'boleto_url' => $payment['bankSlipUrl'] ?? null,
            ]);

            if (in_array(strtoupper($this->billingType), ['PIX', 'UNDEFINED'], true)) {
                $pixData = $asaas->getPixQRCode($payment['id']);

                if ($pixData) {
                    $charge->update([
                        'pix_code' => $pixData['payload'] ?? null,
                        'pix_qrcode' => $pixData['encodedImage'] ?? null,
                    ]);
                }
            }

            Log::info('Pagamento Asaas gerado com sucesso', [
                'charge_id' => $charge->id,
                'asaas_payment_id' => $payment['id'],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar pagamento Asaas: ' . $e->getMessage(), [
                'charge_id' => $this->charge->id,
                'customer_id' => $this->customer->id,
            ]);

            $this->release(60);
        }
    }
}
