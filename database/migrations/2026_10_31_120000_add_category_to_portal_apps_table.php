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
        Schema::table('portal_apps', function (Blueprint $table) {
            $table->string('category')->default('Umum & Kepegawaian')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_apps', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
