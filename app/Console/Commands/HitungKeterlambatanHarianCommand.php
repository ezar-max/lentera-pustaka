<?php

namespace App\Console\Commands;

use App\Services\PeminjamanService;
use Illuminate\Console\Command;

class HitungKeterlambatanHarianCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perpustakaan:hitung-keterlambatan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghitung dan memperbarui jumlah hari keterlambatan harian pada peminjaman buku yang sedang berjalan.';

    public function handle(PeminjamanService $peminjamanService): int
    {
        $jumlah = $peminjamanService->hitungKeterlambatanHarian();

        $this->info("Berhasil memperbarui keterlambatan harian pada {$jumlah} peminjaman.");

        return self::SUCCESS;
    }
}
