<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'    => 'required|exists:categories,id',
            'name'           => 'required|string|max:255',
            'description'    => 'required|string',
            'price'          => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'weight'         => 'nullable|numeric|min:0',
            'origin_country' => 'nullable|string|max:255',
            'certification'  => 'nullable|string|max:255',
            'images'         => 'nullable|array|max:10',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'  => 'الفئة مطلوبة.',
            'category_id.exists'    => 'الفئة المحددة غير موجودة.',
            'name.required'         => 'اسم المنتج مطلوب.',
            'name.max'              => 'اسم المنتج يجب ألا يتجاوز 255 حرفاً.',
            'description.required'  => 'وصف المنتج مطلوب.',
            'price.required'        => 'سعر المنتج مطلوب.',
            'price.numeric'         => 'السعر يجب أن يكون رقماً.',
            'price.min'             => 'السعر يجب أن يكون صفراً أو أكثر.',
            'stock.required'        => 'المخزون مطلوب.',
            'stock.integer'         => 'المخزون يجب أن يكون عدداً صحيحاً.',
            'images.*.image'        => 'الملفات المرفوعة يجب أن تكون صور.',
            'images.*.mimes'        => 'الصور يجب أن تكون بصيغة: jpeg, png, jpg, gif, webp.',
            'images.*.max'          => 'حجم الصورة يجب ألا يتجاوز 5 ميغابايت.',
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
