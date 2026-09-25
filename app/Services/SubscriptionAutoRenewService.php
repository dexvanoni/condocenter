<?php

namespace App\Services;

use App\Mail\ContractRenewedMail;
use App\Models\CondominiumSubscription;
use App\Models\OrganizationSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SubscriptionAutoRenewService
{
    public function renewDue(): int
    {
        $count = 0;

        OrganizationSubscription::query()
            ->with(['organization', 'plan'])
            ->where('auto_renew', true)
            ->whereIn('status', [OrganizationSubscription::STATUS_ACTIVE, OrganizationSubscription::STATUS_TRIAL])
            ->whereNotNull('contract_ends_at')
            ->orderBy('id')
            ->each(function (OrganizationSubscription $subscription) use (&$count) {
                if ($this->renewOrganization($subscription)) {
                    $count++;
                }
            });

        CondominiumSubscription::query()
            ->with(['condominium', 'plan', 'financialResponsible'])
            ->where('auto_renew', true)
            ->whereIn('status', [CondominiumSubscription::STATUS_ACTIVE, CondominiumSubscription::STATUS_TRIAL])
            ->whereNotNull('contract_ends_at')
            ->orderBy('id')
            ->each(function (CondominiumSubscription $subscription) use (&$count) {
                if ($this->renewCondominium($subscription)) {
                    $count++;
                }
            });

        return $count;
    }

    public function renewOrganization(OrganizationSubscription $subscription): bool
    {
        $window = $this->nextWindow($subscription->contract_starts_at, $subscription->extended_until ?? $subscription->contract_ends_at, $subscription->billing_cycle);
        if (!$window) {
            return false;
        }

        $subscription->forceFill([
            'contract_starts_at' => $window['start'],
            'contract_ends_at' => $window['end'],
            'extended_until' => null,
        ])->save();

        $this->notify(
            $subscription->financial_contact_email ?: $subscription->organization?->email,
            $subscription->financial_contact_name ?: $subscription->organization?->displayName() ?: 'Cliente',
            $subscription->organization?->displayName() ?: 'Administradora',
            $subscription->plan?->name,
            $window['start'],
            $window['end'],
        );

        return true;
    }

    public function renewCondominium(CondominiumSubscription $subscription): bool
    {
        $window = $this->nextWindow($subscription->contract_starts_at, $subscription->extended_until ?? $subscription->contract_ends_at, $subscription->billing_cycle);
        if (!$window) {
            return false;
        }

        $subscription->forceFill([
            'contract_starts_at' => $window['start'],
            'contract_ends_at' => $window['end'],
            'extended_until' => null,
        ])->save();

        $this->notify(
            $subscription->financial_contact_email ?: $subscription->financialResponsible?->email ?: $subscription->condominium?->email,
            $subscription->financial_contact_name ?: $subscription->financialResponsible?->name ?: 'Síndico',
            $subscription->condominium?->name ?: 'Condomínio',
            $subscription->plan?->name,
            $window['start'],
            $window['end'],
        );

        return true;
    }

    /**
     * @return array{start: Carbon, end: Carbon}|null
     */
    public function nextWindow(?Carbon $start, ?Carbon $end, ?string $billingCycle): ?array
    {
        if (!$end || !$end->copy()->endOfDay()->isPast()) {
            return null;
        }

        $end = $end->copy()->startOfDay();
        $start = ($start ?? $this->fallbackStart($end, $billingCycle))->copy()->startOfDay();

        if ($start->gt($end)) {
            $start = $this->fallbackStart($end, $billingCycle);
        }

        $inclusiveDays = $start->diffInDays($end) + 1;
        $newStart = $end->copy()->addDay();
        $newEnd = $newStart->copy()->addDays($inclusiveDays - 1);

        return ['start' => $newStart, 'end' => $newEnd];
    }

    protected function fallbackStart(Carbon $end, ?string $billingCycle): Carbon
    {
        return match ($billingCycle) {
            'quarterly' => $end->copy()->subMonthsNoOverflow(3)->addDay(),
            'semiannual' => $end->copy()->subMonthsNoOverflow(6)->addDay(),
            'annual' => $end->copy()->subYearNoOverflow()->addDay(),
            default => $end->copy()->subMonthNoOverflow()->addDay(),
        };
    }

    protected function notify(?string $email, string $name, string $clientName, ?string $planName, Carbon $start, Carbon $end): void
    {
        if (!filled($email)) {
            return;
        }

        Mail::to($email)->send(new ContractRenewedMail(
            $name,
            $clientName,
            $planName,
            $start->format('d/m/Y'),
            $end->format('d/m/Y'),
        ));
    }
}
