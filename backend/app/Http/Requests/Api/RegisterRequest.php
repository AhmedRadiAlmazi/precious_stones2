<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'phone'        => 'required|string|max:20|unique:users',
            'password'     => 'required|string|min:8|confirmed',
            'account_type' => 'required|in:buyer,seller',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'   => 'الاسم الأول مطلوب.',
            'last_name.required'    => 'اسم العائلة مطلوب.',
            'email.required'        => 'البريد الإلكتروني مطلوب.',
            'email.email'           => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'          => 'البريد الإلكتروني مستخدم بالفعل.',
            'phone.required'        => 'رقم الهاتف مطلوب.',
            'phone.unique'          => 'رقم الهاتف مستخدم بالفعل.',
            'password.required'     => 'كلمة المرور مطلوبة.',
            'password.min'          => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'password.confirmed'    => 'كلمة المرور وتأكيدها غير متطابقتان.',
            'account_type.required' => 'نوع الحساب مطلوب.',
            'account_type.in'       => 'نوع الحساب يجب أن يكون buyer أو seller.',
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
