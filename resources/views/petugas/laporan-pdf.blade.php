<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judulLaporan }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #292D2A; margin: 20px; }
        .header { border-bottom: 2px solid #183B32; padding-bottom: 10px; margin-bottom: 18px; }
        .title { font-size: 18px; font-weight: bold; color: #183B32; margin: 0; }
        .subtitle { font-size: 11px; color: #6B7C70; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #183B32; color: #F7F3E8; text-align: left; padding: 7px 8px; font-size: 10px; text-transform: uppercase; }
        td { border-bottom: 1px solid #DCE2DD; padding: 6px 8px; font-size: 10px; vertical-align: top; }
        .footer { margin-top: 24px; font-size: 10px; color: #6B7C70; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">LENTERA PUSTAKA — {{ strtoupper($judulLaporan) }}</p>
        <p class="subtitle">
            Dicetak pada: {{ date('d M Y H:i') }} WIB · Oleh: {{ $pencetak->nama_lengkap }} ({{ ucfirst($pencetak->role) }})
        </p>
    </div>

    @if ($jenis === 'peminjaman')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tgl Pengajuan</th>
                    <th>Peminjam</th>
                    <th>Judul Buku</th>
                    <th>Status</th>
                    <th>Jatuh Tempo</th>
                    <th>Tgl Kembali</th>
                    <th>Kondisi</th>
                    <th>Ketepatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->tanggal_pengajuan->format('d/m/Y') }}</td>
                        <td>{{ $row->user->nama_lengkap }}</td>
                        <td>{{ $row->buku->judul }}</td>
                        <td>{{ strtoupper(str_replace('_', ' ', $row->status)) }}</td>
                        <td>{{ $row->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $row->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $row->kondisi_buku ?? '-' }}</td>
                        <td>{{ str_replace('_', ' ', $row->ketepatan_waktu ?? '-') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif ($jenis === 'stok')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th>Kategori</th>
                    <th>Stok Total</th>
                    <th>Stok Tersedia</th>
                    <th>Sedang Dipinjam</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->judul }}</td>
                        <td>{{ $row->penulis }}</td>
                        <td>{{ $row->kategori->pluck('nama_kategori')->join(', ') }}</td>
                        <td>{{ $row->stok_total }}</td>
                        <td>{{ $row->stok_tersedia }}</td>
                        <td>{{ $row->sedang_dipinjam_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif ($jenis === 'member')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status Akun</th>
                    <th>Tanggal Daftar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->nama_lengkap }}</td>
                        <td>{{ $row->username }}</td>
                        <td>{{ $row->email }}</td>
                        <td>{{ strtoupper($row->status) }}</td>
                        <td>{{ $row->created_at?->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dokumen resmi Sistem Informasi Perpustakaan Digital Lentera Pustaka
    </div>
</body>
</html>

