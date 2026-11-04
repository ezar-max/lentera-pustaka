<?php

namespace App\Console\Commands;

use App\Services\PeminjamanService;
use Illuminate\Console\Command;

class BatalkanPengajuanKadaluarsaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perpustakaan:batalkan-kadaluarsa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membatalkan pengajuan peminjaman yang sudah disetujui namun tidak diambil melewati batas hari pengambilan dan mengembalikan stok tersedia.';

    public function handle(PeminjamanService $peminjamanService): int
    {
        $jumlah = $peminjamanService->batalkanPengajuanKadaluarsa();

        $this->info("Berhasil membatalkan {$jumlah} pengajuan peminjaman yang melewati batas waktu pengambilan.");

        return self::SUCCESS;
    }
}
