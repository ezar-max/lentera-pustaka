<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buku_id')->constrained('buku')->cascadeOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'diajukan',
                'ditolak',
                'disetujui',
                'dibatalkan',
                'dipinjam',
                'pengajuan_kembali',
                'kembali_disetujui',
                'selesai',
            ])->default('diajukan');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_disetujui')->nullable();
            $table->date('tanggal_pinjam')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->date('tanggal_pengajuan_kembali')->nullable();
            $table->date('tanggal_kembali')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->enum('kondisi_buku', ['baik', 'rusak', 'hilang'])->nullable();
            $table->enum('ketepatan_waktu', ['tepat_waktu', 'terlambat'])->nullable();
            $table->integer('hari_terlambat')->default(0);
            $table->text('catatan_petugas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
