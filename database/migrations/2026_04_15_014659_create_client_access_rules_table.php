<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_access_rules', function (Blueprint $table) {
            $table->id();
            $table->char('client_id', 36);
            $table->string('rule_type', 50); // 'user' | 'sipetra_role' | 'identity_type'
            $table->string('rule_value', 255);
            $table->unsignedBigInteger('client_role_id')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('oauth_clients')->cascadeOnDelete();
            $table->foreign('client_role_id')->references('id')->on('client_roles')->nullOnDelete();
            $table->index(['client_id', 'rule_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_access_rules');
    }
};
