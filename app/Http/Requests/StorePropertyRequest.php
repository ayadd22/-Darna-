<?php

namespace App\Http\Requests;

use App\Enums\CurrencyEnum;
use App\Enums\ListingTypeEnum;
use App\Enums\PropertyTypeEnum;
use App\Enums\UserRoleEnum;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Property::class) ?? false;
    }

    public function rules(): array
    {
        $isAdmin = $this->user()?->role === UserRoleEnum::ADMIN;

        return [
            'office_id' => [
                Rule::requiredIf($isAdmin),
                Rule::prohibitedIf(! $isAdmin),
                'integer',
                'exists:offices,id',
            ],

            'owner_id' => [
                'required',
                'integer',
                'exists:owners,id',
            ],

            'governorate_id' => [
                'required',
                'integer',
                'exists:governorates,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'property_type' => [
                'required',
                Rule::enum(PropertyTypeEnum::class),
            ],

            'listing_type' => [
                'required',
                Rule::enum(ListingTypeEnum::class),
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                Rule::enum(CurrencyEnum::class),
            ],

            'area' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'address' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_phone' => [
                'nullable',
                'string',
                'max:30',
            ],
        ];
    }
}
