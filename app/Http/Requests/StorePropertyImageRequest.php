<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class StorePropertyImageRequest extends FormRequest
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
          
            'image'      => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'alt_text'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'يجب تحديد ملف الصورة.',
            'image.image'    => 'يجب أن يكون الملف المرفوع صورة.',
            'image.mimes'    => 'الصيغ المقبولة: jpg, jpeg, png, webp, gif.',
            'image.max'      => 'لا يجب أن يتجاوز حجم الصورة 5 ميغابايت.',
        ];
    }
}
