<?php

namespace App\Http\Requests;

class UpdateCondominiumLandingItemRequest extends StoreCondominiumLandingItemRequest
{
    public function rules(): array
    {
        $rules = $this->itemRules();
        $rules['type'][0] = 'sometimes';

        return $rules;
    }
}
