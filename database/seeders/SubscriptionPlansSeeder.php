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
                'fixed_price' => 0,
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
                'fixed_price' => 0,
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
                'fixed_price' => 0,
                'billing_cycle' => 'annual',
                'trial_days' => 0,
                'payment_method' => 'credit_card',
                'sort_order' => 30,
            ],
            [
                'name' => 'Fixo — Mensal',
                'slug' => 'fixo-mensal',
                'description' => 'Valor fixo mensal independente de unidades ou usuários.',
                'billing_metric' => 'fixed',
                'unit_price' => 0,
                'user_price' => 0,
                'fixed_price' => 299.90,
                'billing_cycle' => 'monthly',
                'trial_days' => 7,
                'payment_method' => 'boleto',
                'sort_order' => 40,
            ],
            [
                'name' => 'Fixo — Trimestral',
                'slug' => 'fixo-trimestral',
                'description' => 'Valor fixo a cada 3 meses. Ideal para contratos padronizados.',
                'billing_metric' => 'fixed',
                'unit_price' => 0,
                'user_price' => 0,
                'fixed_price' => 849.90,
                'billing_cycle' => 'quarterly',
                'trial_days' => 7,
                'payment_method' => 'boleto',
                'sort_order' => 50,
            ],
            [
                'name' => 'Fixo — Anual',
                'slug' => 'fixo-anual',
                'description' => 'Valor fixo anual com melhor previsibilidade de caixa.',
                'billing_metric' => 'fixed',
                'unit_price' => 0,
                'user_price' => 0,
                'fixed_price' => 3199.90,
                'billing_cycle' => 'annual',
                'trial_days' => 14,
                'payment_method' => 'credit_card',
                'sort_order' => 60,
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
