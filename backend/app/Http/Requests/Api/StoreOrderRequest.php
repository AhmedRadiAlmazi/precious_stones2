<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'      => 'required|exists:products,id',
            'quantity'        => 'required|integer|min:1',
            'notes'           => 'nullable|string|max:1000',
            'shipping_address'=> 'required|string|max:500',
            'payment_method'  => 'required|in:bank_transfer,cash_on_delivery,credit_card',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'       => 'معرّف المنتج مطلوب.',
            'product_id.exists'         => 'المنتج غير موجود.',
            'quantity.required'         => 'الكمية مطلوبة.',
            'quantity.integer'          => 'الكمية يجب أن تكون عدداً صحيحاً.',
            'quantity.min'              => 'الكمية يجب أن تكون 1 على الأقل.',
            'shipping_address.required' => 'عنوان الشحن مطلوب.',
            'payment_method.required'   => 'طريقة الدفع مطلوبة.',
            'payment_method.in'         => 'طريقة الدفع يجب أن تكون: bank_transfer أو cash_on_delivery أو credit_card.',
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
