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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nama_bank')) {
                $table->string('nama_bank', 50)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'nomor_rekening')) {
                $table->string('nomor_rekening', 50)->nullable()->after('nama_bank');
            }
            if (! Schema::hasColumn('users', 'nama_pemilik_rekening')) {
                $table->string('nama_pemilik_rekening', 255)->nullable()->after('nomor_rekening');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'nomor_rekening', 'nama_pemilik_rekening']);
        });
    }
};
