<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Essencial — por unidade',
                'slug' => 'essencial-unidade',
                'description' => 'Ideal para condomínios pequenos. Cobrança mensal por unidade cadastrada.',
                'billing_metric' => 'unit',
                'unit_price' => 4.90,
                'user_price' => 0,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'payment_method' => 'boleto',
                'sort_order' => 10,
            ],
            [
                'name' => 'Profissional — por unidade',
                'slug' => 'profissional-unidade',
                'description' => 'Condomínios médios. Cobrança trimestral com desconto implícito no ciclo.',
                'billing_metric' => 'unit',
                'unit_price' => 4.50,
                'user_price' => 0,
                'billing_cycle' => 'quarterly',
                'trial_days' => 7,
                'payment_method' => 'boleto',
                'sort_order' => 20,
            ],
            [
                'name' => 'Corporativo — por usuário',
                'slug' => 'corporativo-usuario',
                'description' => 'Grandes condomínios ou administradoras. Cobrança anual por usuário ativo.',
                'billing_metric' => 'user',
                'unit_price' => 0,
                'user_price' => 2.90,
                'billing_cycle' => 'annual',
                'trial_days' => 0,
                'payment_method' => 'credit_card',
                'sort_order' => 30,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['is_active' => true]),
            );
        }
    }
}
