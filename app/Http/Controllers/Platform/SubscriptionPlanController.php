<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $plans = SubscriptionPlan::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('platform.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);

        SubscriptionPlan::create($data);

        return back()->with('success', 'Plano criado.');
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $this->validated($request, $plan->id);
        $data['slug'] = Str::slug($data['name']);

        $plan->update($data);

        return back()->with('success', 'Plano atualizado.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'Plano vinculado a contratos. Desative em vez de excluir.');
        }

        $plan->delete();

        return back()->with('success', 'Plano removido.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'billing_metric' => ['required', Rule::in(['unit', 'user', 'fixed'])],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'user_price' => ['nullable', 'numeric', 'min:0'],
            'fixed_price' => ['nullable', 'numeric', 'min:0', 'required_if:billing_metric,fixed'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'quarterly', 'semiannual', 'annual'])],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'payment_method' => ['required', Rule::in(['boleto', 'credit_card', 'pix_recurring', 'bank_deposit'])],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + [
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) $request->input('sort_order', 0),
            'unit_price' => $request->input('unit_price', 0),
            'user_price' => $request->input('user_price', 0),
            'fixed_price' => $request->input('fixed_price', 0),
            'trial_days' => (int) $request->input('trial_days', 0),
        ];
    }
}
