<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\DirectClientOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientOnboardingController extends Controller
{
    public function createDirect(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $plans = SubscriptionPlan::query()
            ->activeForAudience(SubscriptionPlan::AUDIENCE_CONDOMINIUM)
            ->get();

        return view('platform.clients.create-direct', compact('plans'));
    }

    public function storeDirect(Request $request, DirectClientOnboardingService $onboarding): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $request->validate([
            'condo_name' => ['required', 'string', 'max:255'],
            'condo_cnpj' => ['nullable', 'string', 'max:18'],
            'condo_email' => ['nullable', 'email', 'max:255'],
            'condo_phone' => ['nullable', 'string', 'max:30'],
            'condo_address' => ['nullable', 'string', 'max:255'],
            'condo_neighborhood' => ['nullable', 'string', 'max:120'],
            'condo_city' => ['nullable', 'string', 'max:120'],
            'condo_state' => ['nullable', 'string', 'size:2'],
            'condo_zip_code' => ['nullable', 'string', 'max:12'],
            'financial_mode' => ['nullable', Rule::in(['simplified', 'full'])],
            'units_limit' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'user_name' => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'user_phone' => ['nullable', 'string', 'max:30'],
            'subscription_plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
        ]);

        $result = $onboarding->onboardDirect([
            'condominium' => [
                'name' => $data['condo_name'],
                'cnpj' => $data['condo_cnpj'] ?? null,
                'email' => $data['condo_email'] ?? null,
                'phone' => $data['condo_phone'] ?? null,
                'address' => $data['condo_address'] ?? null,
                'neighborhood' => $data['condo_neighborhood'] ?? null,
                'city' => $data['condo_city'] ?? null,
                'state' => $data['condo_state'] ?? null,
                'zip_code' => $data['condo_zip_code'] ?? null,
                'financial_mode' => $data['financial_mode'] ?? 'simplified',
                'units_limit' => $data['units_limit'] ?? null,
            ],
            'user' => [
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'phone' => $data['user_phone'] ?? null,
            ],
            'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
        ], $request->user());

        return redirect()
            ->route('platform.organizations.show', $result['organization'])
            ->with('success', 'Cliente direto (Modelo A) cadastrado. E-mail de definição de senha enviado ao síndico.');
    }

    public function createManagement(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $plans = SubscriptionPlan::query()
            ->activeForAudience(SubscriptionPlan::AUDIENCE_MANAGEMENT_COMPANY)
            ->get();

        return view('platform.clients.create-management', compact('plans'));
    }

    public function storeManagement(Request $request, DirectClientOnboardingService $onboarding): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $request->validate([
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:18'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip_code' => ['nullable', 'string', 'max:12'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'user_name' => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'user_phone' => ['nullable', 'string', 'max:30'],
            'subscription_plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
        ]);

        $result = $onboarding->onboardManagement([
            'organization' => [
                'legal_name' => $data['legal_name'],
                'trade_name' => $data['trade_name'] ?? null,
                'document' => $data['document'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'neighborhood' => $data['neighborhood'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'zip_code' => $data['zip_code'] ?? null,
                'notes' => $data['notes'] ?? null,
            ],
            'user' => [
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'phone' => $data['user_phone'] ?? null,
            ],
            'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
        ], $request->user());

        return redirect()
            ->route('platform.organizations.show', $result['organization'])
            ->with('success', 'Administradora (Modelo B) cadastrada. E-mail de definição de senha enviado ao proprietário.');
    }
}
