<?php

namespace App\Http\Requests\Concerns;

use App\Support\PublicPropertyKinds;
use App\Support\UnitOccupancyRegimes;
use App\Support\UnitRentalPeriods;

trait ValidatesUnitOccupancy
{
    protected function occupancyRegimeRules(): array
    {
        return [
            'occupancy_regime' => ['required', UnitOccupancyRegimes::validationRule()],
            'rental_period' => [
                'nullable',
                UnitRentalPeriods::validationRule(),
                'required_if:occupancy_regime,' . UnitOccupancyRegimes::ALUGUEL,
            ],
            'public_property_kind' => [
                'nullable',
                PublicPropertyKinds::validationRule(),
                'required_if:occupancy_regime,' . UnitOccupancyRegimes::IMOVEL_PUBLICO,
            ],
            'owner_user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                'required_if:occupancy_regime,' . UnitOccupancyRegimes::ALUGUEL,
            ],
            'lease_contract_ends_at' => ['nullable', 'date'],
        ];
    }
}
