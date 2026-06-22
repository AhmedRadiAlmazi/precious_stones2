<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'auction_id' => 'required|integer|exists:auctions,id',
            'amount'     => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'auction_id.required' => 'معرّف المزاد مطلوب.',
            'auction_id.exists'   => 'المزاد غير موجود.',
            'amount.required'     => 'قيمة المزايدة مطلوبة.',
            'amount.numeric'      => 'قيمة المزايدة يجب أن تكون رقماً.',
            'amount.min'          => 'قيمة المزايدة يجب أن تكون أكبر من صفر.',
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
