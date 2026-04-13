<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_user_accesses', function (Blueprint $table) {
            $table->id();
            $table->char('client_id', 36);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_role_id');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('oauth_clients')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('client_role_id')->references('id')->on('client_roles')->cascadeOnDelete();
            $table->unique(['client_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_user_accesses');
    }
};
