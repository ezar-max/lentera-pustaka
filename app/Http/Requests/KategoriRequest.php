<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KategoriRequest extends FormRequest
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
        $kategoriId = $this->route('kategori')?->id ?? $this->route('id');

        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:150',
                Rule::unique('kategori_buku', 'nama_kategori')->ignore($kategoriId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori buku wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori tersebut sudah tersedia.',
        ];
    }
}
