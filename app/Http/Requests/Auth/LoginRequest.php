<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "email" => "required|email|exists:users,email",
            "password" => "required|min:8|max:50"
        ];
    }
    public function messages()
    {
        return [
            "email.required" => "Kolom email wajib diisi.",
            "email.email" => "Format email tidak valid.",
            "email.exists" => "Email yang dimasukkan tidak terdaftar.",
            "password.required" => "Kolom password wajib diisi.",
            "password.min" => "Password minimal harus terdiri dari :min karakter.",
            "password.max" => "Password maksimal harus terdiri dari :max karakter."
        ];
    }
}
