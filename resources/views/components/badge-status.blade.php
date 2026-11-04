@props(['status' => 'diajukan'])

@php
    $konfigurasi = [
        'diajukan' => [
            'label' => 'Diajukan',
            'classes' => 'bg-status-diajukan text-status-diajukan-text',
        ],
        'disetujui' => [
            'label' => 'Disetujui · Siap Diambil',
            'classes' => 'bg-status-disetujui text-status-disetujui-text',
        ],
        'dipinjam' => [
            'label' => 'Sedang Dipinjam',
            'classes' => 'bg-status-dipinjam text-status-dipinjam-text',
        ],
        'pengajuan_kembali' => [
            'label' => 'Pengajuan Kembali',
            'classes' => 'bg-status-kembali text-status-kembali-text',
        ],
        'kembali_disetujui' => [
            'label' => 'Kembali Disetujui',
            'classes' => 'bg-status-disetujui text-status-disetujui-text',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'classes' => 'bg-status-selesai text-status-selesai-text',
        ],
        'ditolak' => [
            'label' => 'Ditolak',
            'classes' => 'bg-status-ditolak text-status-ditolak-text',
        ],
        'dibatalkan' => [
            'label' => 'Dibatalkan',
            'classes' => 'bg-status-dibatalkan text-status-dibatalkan-text',
        ],
        'aktif' => [
            'label' => 'Aktif',
            'classes' => 'bg-status-disetujui text-status-disetujui-text',
        ],
        'lunas' => [
            'label' => 'Lunas',
            'classes' => 'bg-status-selesai text-status-selesai-text',
        ],
        'pending' => [
            'label' => 'Menunggu Persetujuan',
            'classes' => 'bg-status-diajukan text-status-diajukan-text',
        ],
        'tepat_waktu' => [
            'label' => 'Tepat Waktu',
            'classes' => 'bg-status-selesai text-status-selesai-text',
        ],
        'terlambat' => [
            'label' => 'Terlambat',
            'classes' => 'bg-status-ditolak text-status-ditolak-text',
        ],
        'baik' => [
            'label' => 'Kondisi Baik',
            'classes' => 'bg-status-selesai text-status-selesai-text',
        ],
        'rusak' => [
            'label' => 'Kondisi Rusak',
            'classes' => 'bg-status-diajukan text-status-diajukan-text',
        ],
        'hilang' => [
            'label' => 'Hilang',
            'classes' => 'bg-status-ditolak text-status-ditolak-text',
        ],
    ];

    $item = $konfigurasi[$status] ?? [
        'label' => ucfirst(str_replace('_', ' ', (string) $status)),
        'classes' => 'bg-ivory-dark text-charcoal',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-1 rounded-card text-xs font-medium tracking-wide ' . $item['classes']]) }}>
    {{ $item['label'] }}
</span>

