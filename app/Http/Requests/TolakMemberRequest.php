<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TolakMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alasan_penolakan' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alasan_penolakan.required' => 'Alasan penolakan registrasi member wajib diisi.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal terdiri dari 5 karakter.',
        ];
    }
}
