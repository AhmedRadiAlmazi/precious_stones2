<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'    => 'sometimes|exists:categories,id',
            'name'           => 'sometimes|string|max:255',
            'description'    => 'sometimes|string',
            'price'          => 'sometimes|numeric|min:0',
            'stock'          => 'sometimes|integer|min:0',
            'weight'         => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:255',
            'certification'  => 'nullable|string|max:255',
            'images'         => 'nullable|array|max:10',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'بيانات غير صالحة.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
