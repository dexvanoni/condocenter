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

    public $charge;
    public $customer;

    /**
     * Create a new job instance.
     */
    public function __construct(Charge $charge, User $customer)
    {
        $this->charge = $charge;
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     */
    public function handle(AsaasService $asaasService): void
    {
        try {
            // Criar ou atualizar cliente no Asaas
            $customerData = [
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'mobilePhone' => $this->customer->phone,
                'cpfCnpj' => $this->customer->cpf,
                'postalCode' => $this->charge->condominium->zip_code,
                'address' => $this->charge->condominium->address,
                'addressNumber' => 'S/N',
                'province' => $this->charge->condominium->city,
                'externalReference' => 'USER-' . $this->customer->id,
            ];

            $asaasCustomer = $asaasService->createOrUpdateCustomer($customerData);

            if (!$asaasCustomer) {
                throw new \Exception('Falha ao criar cliente no Asaas');
            }

            // Criar cobrança
            $paymentData = [
                'customer' => $asaasCustomer['id'],
                'billingType' => 'BOLETO', // Pode ser PIX, CREDIT_CARD, etc
                'dueDate' => $this->charge->due_date->format('Y-m-d'),
                'value' => $this->charge->amount,
                'description' => $this->charge->title,
                'externalReference' => 'CHARGE-' . $this->charge->id,
                'fine' => [
                    'value' => $this->charge->fine_percentage,
                ],
                'interest' => [
                    'value' => $this->charge->interest_rate,
                ],
            ];

            $payment = $asaasService->createPayment($paymentData);

            if (!$payment) {
                throw new \Exception('Falha ao criar pagamento no Asaas');
            }

            // Atualizar cobrança com dados do Asaas
            $this->charge->update([
                'asaas_payment_id' => $payment['id'],
                'boleto_url' => $payment['bankSlipUrl'] ?? null,
            ]);

            // Gerar PIX se solicitado
            if ($request->billingType ?? 'BOLETO' === 'PIX') {
                $pixData = $asaasService->getPixQRCode($payment['id']);
                
                if ($pixData) {
                    $this->charge->update([
                        'pix_code' => $pixData['payload'] ?? null,
                        'pix_qrcode' => $pixData['encodedImage'] ?? null,
                    ]);
                }
            }

            Log::info("Pagamento Asaas gerado com sucesso", [
                'charge_id' => $this->charge->id,
                'asaas_payment_id' => $payment['id']
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar pagamento Asaas: ' . $e->getMessage(), [
                'charge_id' => $this->charge->id,
                'customer_id' => $this->customer->id,
            ]);

            // Retentar o job
            $this->release(60); // Tentar novamente em 60 segundos
        }
    }
}
