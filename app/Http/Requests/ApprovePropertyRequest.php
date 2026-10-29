<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class ApprovePropertyRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return $this->user()?->can('approve', Property::class) ?? false;
    }

 
    public function rules(): array
    {
        return [];
    }
}
