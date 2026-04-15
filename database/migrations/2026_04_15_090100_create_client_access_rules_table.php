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
            $table->foreignUuid('client_id')->constrained('oauth_clients')->cascadeOnDelete();
            $table->string('rule_type', 50);
            $table->string('rule_value');
            $table->timestamps();

            $table->index(['client_id', 'rule_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_access_rules');
    }
};
