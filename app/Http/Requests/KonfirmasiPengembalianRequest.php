<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KonfirmasiPengembalianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isStaf();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kondisi_buku' => ['required', 'in:baik,rusak,hilang'],
            'catatan_petugas' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kondisi_buku.required' => 'Kondisi fisik buku saat dikembalikan wajib dipilih.',
            'kondisi_buku.in' => 'Pilihan kondisi buku tidak valid.',
        ];
    }
}
