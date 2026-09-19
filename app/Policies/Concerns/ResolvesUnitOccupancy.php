<?php

namespace App\Policies\Concerns;

use App\Models\Unit;
use App\Models\User;
use App\Services\UnitOccupancyService;

trait ResolvesUnitOccupancy
{
    protected function occupancyService(): UnitOccupancyService
    {
        return app(UnitOccupancyService::class);
    }

    protected function userCanCreateServiceOrderForUnit(User $user, ?Unit $unit): bool
    {
        if (!$unit) {
            return true;
        }

        if (!$this->occupancyService()->isRental($unit)) {
            return true;
        }

        return $this->occupancyService()->userOwnsUnit($user, $unit);
    }
}
