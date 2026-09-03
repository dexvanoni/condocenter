<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Http\Requests\PayChargeWithCardRequest;
use App\Models\Charge;
use App\Services\ChargePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChargePaymentController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly ChargePaymentService $paymentService,
    ) {}

    public function checkout(Request $request, Charge $charge): JsonResponse
    {
        $user = $request->user();
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $charge->condominium_id);

        $request->validate([
            'billing_type' => ['nullable', 'in:PIX,CREDIT_CARD,BOLETO,UNDEFINED'],
        ]);

        $checkout = $this->paymentService->getCheckout(
            $user,
            $charge,
            $request->input('billing_type', 'PIX')
        );

        return response()->json($checkout);
    }

    public function payWithCard(PayChargeWithCardRequest $request, Charge $charge): JsonResponse
    {
        $user = $request->user();
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $charge->condominium_id);

        $result = $this->paymentService->payWithCreditCard(
            $user,
            $charge,
            $request->validated()
        );

        return response()->json($result);
    }

    public function status(Request $request, Charge $charge): JsonResponse
    {
        $user = $request->user();
        $this->ensureResourceBelongsToActiveCondominium($user, (int) $charge->condominium_id);

        if (!$user->unit_id || (int) $charge->unit_id !== (int) $user->unit_id) {
            abort(403);
        }

        return response()->json(
            $this->paymentService->refreshStatus($charge)
        );
    }
}
