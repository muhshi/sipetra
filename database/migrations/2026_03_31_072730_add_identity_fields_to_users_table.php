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
            // Tipe identitas
            $table->string('identity_type')->default('admin')->after('email');

            // Identitas pegawai
            $table->string('nip')->nullable()->unique()->after('identity_type');
            $table->string('nip_baru')->nullable()->unique()->after('nip');

            // Identitas mitra
            $table->string('sobat_id')->nullable()->unique()->after('nip_baru');

            // Data organisasi / wilayah
            $table->string('kd_satker', 10)->nullable()->after('sobat_id');
            $table->string('jabatan')->nullable()->after('kd_satker');
            $table->string('unit_kerja')->nullable()->after('jabatan');
            $table->string('golongan', 10)->nullable()->after('unit_kerja');

            // Data pribadi
            $table->string('jenis_kelamin', 2)->nullable()->after('golongan');
            $table->string('tempat_lahir')->nullable()->after('jenis_kelamin');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('pendidikan')->nullable()->after('tanggal_lahir');
            $table->string('phone', 20)->nullable()->after('pendidikan');
            $table->string('avatar_url')->nullable()->after('phone');

            // Status
            $table->boolean('is_active')->default(true)->after('avatar_url');

            // Indexes
            $table->index('identity_type');
            $table->index('kd_satker');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['identity_type']);
            $table->dropIndex(['kd_satker']);
            $table->dropIndex(['is_active']);

            $table->dropColumn([
                'identity_type',
                'nip',
                'nip_baru',
                'sobat_id',
                'kd_satker',
                'jabatan',
                'unit_kerja',
                'golongan',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'pendidikan',
                'phone',
                'avatar_url',
                'is_active',
            ]);
        });
    }
};
