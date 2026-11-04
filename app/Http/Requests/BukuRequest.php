<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BukuRequest extends FormRequest
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
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['required', 'string', 'max:255'],
            'penerbit' => ['required', 'string', 'max:255'],
            'tahun_terbit' => ['required', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'isbn' => ['nullable', 'string', 'max:100'],
            'sinopsis' => ['nullable', 'string', 'max:5000'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'stok_total' => ['required', 'integer', 'min:0'],
            'kategori_ids' => ['required', 'array', 'min:1'],
            'kategori_ids.*' => ['exists:kategori_buku,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul buku wajib diisi.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'penerbit.required' => 'Nama penerbit wajib diisi.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.integer' => 'Tahun terbit harus berupa angka tahun yang valid.',
            'cover.image' => 'File sampul harus berupa gambar.',
            'cover.max' => 'Ukuran gambar sampul maksimal 2 MB.',
            'stok_total.required' => 'Stok total buku wajib diisi.',
            'stok_total.min' => 'Stok total tidak boleh kurang dari 0.',
            'kategori_ids.required' => 'Pilih minimal satu kategori untuk buku ini.',
            'kategori_ids.min' => 'Pilih minimal satu kategori untuk buku ini.',
        ];
    }
}
