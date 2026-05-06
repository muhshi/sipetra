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
            if (! Schema::hasColumn('users', 'period')) {
                $table->string('period', 50)->nullable()->after('is_active');
                $table->index('period');
            }

            if (! Schema::hasColumn('users', 'contract_start')) {
                $table->date('contract_start')->nullable()->after('period');
            }

            if (! Schema::hasColumn('users', 'contract_end')) {
                $table->date('contract_end')->nullable()->after('contract_start');
            }
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
