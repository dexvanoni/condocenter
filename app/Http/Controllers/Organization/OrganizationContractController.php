<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Services\ActiveCondominiumService;
use App\Services\PlatformAsaasService;
use App\Services\SubscriptionBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrganizationContractController extends Controller
{
    public function __construct(
        private ActiveCondominiumService $activeCondominium,
        private SubscriptionBillingService $billing,
        private PlatformAsaasService $asaas,
    ) {}

    public function show(Request $request): View
    {
        $organization = $this->organization($request);
        $contracts = $organization->subscriptions()->with('plan')->orderByDesc('id')->get();
        $subscription = $this->selected($contracts, $request->integer('contract'));

        $billingFilters = $this->billing->filtersFromRequest($request);
        $billingReport = $subscription
            ? $this->billing->getBillingReport($subscription, $billingFilters)
            : null;

        return view('organization.contract', [
            'organization' => $organization,
            'contracts' => $contracts,
            'subscription' => $subscription,
            'billingReport' => $billingReport,
            'billingFilters' => $billingFilters,
            'exportUrl' => $subscription
                ? route('organization.contract.charges.export', array_merge(['contract' => $subscription->id], $billingFilters))
                : null,
            'asaasSummary' => $this->asaasSummary($subscription),
        ]);
    }

    public function exportCharges(Request $request): StreamedResponse
    {
        $organization = $this->organization($request);
        $subscription = $this->selected(
            $organization->subscriptions()->get(),
            $request->integer('contract')
        );
        abort_unless($subscription, 404);

        return $this->billing->exportCsv($subscription, $this->billing->filtersFromRequest($request), 'contrato-administradora');
    }

    public function pixCheckout(Request $request, string $paymentId): JsonResponse
    {
        $organization = $this->organization($request);
        $subscription = $this->selected(
            $organization->subscriptions()->get(),
            $request->integer('contract')
        );
        abort_unless($subscription?->usesAsaas() && $subscription->asaas_customer_id, 404);

        $payment = $this->asaas->getPayment($paymentId);
        if (!$payment || ($payment['customer'] ?? null) !== $subscription->asaas_customer_id) {
            throw ValidationException::withMessages([
                'payment' => 'Cobrança não encontrada para este contrato.',
            ]);
        }

        $pix = $this->asaas->getPixQRCode($paymentId);
        abort_unless($pix, 422, 'Não foi possível gerar o PIX para esta cobrança.');

        return response()->json([
            'payment_id' => $paymentId,
            'encoded_image' => $pix['encodedImage'] ?? null,
            'payload' => $pix['payload'] ?? null,
            'expiration_date' => $pix['expirationDate'] ?? null,
        ]);
    }

    protected function organization(Request $request): Organization
    {
        $user = $request->user();
        abort_unless($user?->canUseManagementCompanyProfile(), 403);

        $organizationId = $this->activeCondominium->getActiveOrganizationId($user)
            ?: $this->activeCondominium->accessibleOrganizationIds($user)->first();

        abort_unless($organizationId, 403);
        $organization = Organization::query()->findOrFail($organizationId);
        abort_unless($organization->isManagementCompany(), 404);

        return $organization;
    }

    protected function selected($contracts, int $contractId): ?OrganizationSubscription
    {
        if ($contracts->isEmpty()) {
            return null;
        }

        $subscription = $contractId
            ? $contracts->firstWhere('id', $contractId)
            : $contracts->first();

        abort_if($contractId && !$subscription, 404);

        return $subscription;
    }

    protected function asaasSummary(?OrganizationSubscription $subscription): ?array
    {
        if (!$subscription?->asaas_subscription_id) {
            return null;
        }

        $data = $this->asaas->getSubscription($subscription->asaas_subscription_id);
        if (!$data) {
            return null;
        }

        return [
            'next_due_date' => $data['nextDueDate'] ?? null,
            'credit_card_brand' => $data['creditCard']['creditCardBrand'] ?? null,
            'credit_card_number' => $data['creditCard']['creditCardNumber'] ?? null,
        ];
    }
}
