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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Tanggal Terhitung Mulai Tugas
            $table->date('tmt_cpns')->nullable();
            $table->date('tmt_pns')->nullable();
            $table->date('tmt_golongan')->nullable();
            $table->date('tmt_jabatan')->nullable();

            // Kode & Label Tambahan
            $table->string('kd_gol', 10)->nullable();
            $table->string('kd_jab', 10)->nullable();
            $table->string('status_pegawai')->nullable(); // PNS, CPNS, PPPK, dll

            // Masa Kerja
            $table->integer('mk_tahun')->nullable();
            $table->integer('mk_bulan')->nullable();

            // Data Pribadi Tambahan
            $table->string('agama', 20)->nullable();
            $table->string('no_ijazah')->nullable();
            $table->date('tgl_ijazah')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
