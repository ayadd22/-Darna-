<?php

namespace App\Http\Requests;

use App\Enums\ListingTypeEnum;
use App\Enums\PropertyTypeEnum;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterPropertyRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'governorate_id' => [
                'sometimes',
                'integer',
                'exists:governorates,id',
            ],

            'listing_type' => [
                'sometimes',
                Rule::enum(ListingTypeEnum::class),
            ],

            'property_type' => [
                'sometimes',
                Rule::enum(PropertyTypeEnum::class),
            ],

            'min_price' => [
                'sometimes',
                'numeric',
                'min:0',
             
                function (string $attribute, mixed $value, Closure $fail) {
                    $max = $this->query('max_price');
                    if ($max !== null && $max !== '' && is_numeric($max)) {
                        if ((float) $value > (float) $max) {
                            $fail('الحد الأدنى للسعر يجب أن يكون أقل من أو يساوي الحد الأقصى.');
                        }
                    }
                },
            ],

            'max_price' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'governorate_id.integer' => 'معرّف المحافظة يجب أن يكون رقماً صحيحاً.',
            'governorate_id.exists'  => 'المحافظة المحددة غير موجودة.',

            'listing_type.enum'  => 'نوع الإعلان غير صالح. القيم المقبولة: sale، rent.',

            'property_type.enum' => 'نوع العقار غير صالح. القيم المقبولة: apartment، shop، land.',

            'min_price.numeric'  => 'الحد الأدنى للسعر يجب أن يكون قيمة رقمية.',
            'min_price.min'      => 'الحد الأدنى للسعر لا يمكن أن يكون سالباً.',

            'max_price.numeric'  => 'الحد الأقصى للسعر يجب أن يكون قيمة رقمية.',
            'max_price.min'      => 'الحد الأقصى للسعر لا يمكن أن يكون سالباً.',
        ];
    }

    public function attributes(): array
    {
        return [
            'governorate_id' => 'المحافظة',
            'listing_type'   => 'نوع الإعلان',
            'property_type'  => 'نوع العقار',
            'min_price'      => 'الحد الأدنى للسعر',
            'max_price'      => 'الحد الأقصى للسعر',
        ];
    }
}
