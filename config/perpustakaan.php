<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Parameter Aturan Peminjaman & Denda Lentera Pustaka
    |--------------------------------------------------------------------------
    */
    'kuota_pinjam_aktif' => (int) env('PERPUSTAKAAN_KUOTA_PINJAM', 3),
    'durasi_pinjam_hari' => (int) env('PERPUSTAKAAN_DURASI_PINJAM', 7),
    'batas_ambil_hari' => (int) env('PERPUSTAKAAN_BATAS_AMBIL', 2),

    'denda' => [
        'terlambat_per_hari' => (int) env('PERPUSTAKAAN_DENDA_TERLAMBAT', 2),
        'rusak' => (int) env('PERPUSTAKAAN_DENDA_RUSAK', 25),
        'hilang' => (int) env('PERPUSTAKAAN_DENDA_HILANG', 100),
    ],

    'batas_poin_blokir' => (int) env('PERPUSTAKAAN_BATAS_POIN_BLOKIR', 50),
    'batas_poin_peringatan' => (int) env('PERPUSTAKAAN_BATAS_POIN_PERINGATAN', 30),
    'batas_stok_menipis' => (int) env('PERPUSTAKAAN_BATAS_STOK_MENIPIS', 2),

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],
];
