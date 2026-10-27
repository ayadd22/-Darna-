<?php

namespace App\Http\Requests;

use App\Enums\CurrencyEnum;
use App\Enums\ListingTypeEnum;
use App\Enums\PropertyTypeEnum;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property instanceof Property
            && ($this->user()?->can('update', $property) ?? false);
    }

    public function rules(): array
    {
        return [
            'owner_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:owners,id',
            ],

            'governorate_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:governorates,id',
            ],

            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'sometimes',
                'required',
                'string',
            ],

            'property_type' => [
                'sometimes',
                'required',
                Rule::enum(PropertyTypeEnum::class),
            ],

            'listing_type' => [
                'sometimes',
                'required',
                Rule::enum(ListingTypeEnum::class),
            ],

            'price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'sometimes',
                'required',
                Rule::enum(CurrencyEnum::class),
            ],

            'area' => [
                'sometimes',
                'required',
                'numeric',
                'gt:0',
            ],

            'address' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'contact_phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],
        ];
    }
}
