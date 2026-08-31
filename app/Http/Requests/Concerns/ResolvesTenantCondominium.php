<?php

namespace App\Http\Requests\Concerns;

use App\Support\TenantContext;

trait ResolvesTenantCondominium
{
    protected function tenantCondominiumId(): int
    {
        return TenantContext::requireId($this->user());
    }
}
