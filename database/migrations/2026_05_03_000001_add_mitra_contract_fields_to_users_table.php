<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom siklus kontrak mitra ke tabel users.
 *
 * Kolom ini dibutuhkan untuk:
 * 1. Membedakan mitra reguler tahunan dari mitra adhoc (sensus, dll)
 * 2. Memungkinkan aplikasi client memfilter mitra berdasarkan periode aktif
 * 3. Sinkronisasi status kontrak secara otomatis via Master Data API
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Periode aktif mitra. Contoh: "2026", "sensus_ekonomi_2026"
            // null untuk pegawai PNS/PPPK
            $table->string('period', 50)->nullable()->after('is_active');

            // Tanggal mulai kontrak mitra
            $table->date('contract_start')->nullable()->after('period');

            // Tanggal akhir kontrak mitra.
            // Aplikasi client dapat menggunakan field ini untuk menampilkan warning.
            $table->date('contract_end')->nullable()->after('contract_start');

            // Index untuk query filter per periode (banyak dipakai di Master Data API)
            $table->index('period');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['period']);
            $table->dropColumn(['period', 'contract_start', 'contract_end']);
        });
    }
};
