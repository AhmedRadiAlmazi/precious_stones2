<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'     => 'required|exists:products,id',
            'starting_price' => 'required|numeric|min:0',
            'reserve_price'  => 'nullable|numeric|min:0',
            'start_time'     => 'required|date|after:now',
            'end_time'       => 'required|date|after:start_time',
            'bid_increment'  => 'nullable|numeric|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'     => 'معرّف المنتج مطلوب.',
            'product_id.exists'       => 'المنتج غير موجود.',
            'starting_price.required' => 'السعر الابتدائي مطلوب.',
            'starting_price.numeric'  => 'السعر الابتدائي يجب أن يكون رقماً.',
            'starting_price.min'      => 'السعر الابتدائي يجب أن يكون صفراً أو أكثر.',
            'start_time.required'     => 'وقت البدء مطلوب.',
            'start_time.after'        => 'وقت البدء يجب أن يكون في المستقبل.',
            'end_time.required'       => 'وقت الانتهاء مطلوب.',
            'end_time.after'          => 'وقت الانتهاء يجب أن يكون بعد وقت البدء.',
            'bid_increment.min'       => 'الحد الأدنى لزيادة المزايدة يجب أن يكون 1 على الأقل.',
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
