<?php

use App\Enums\ClientAccessPolicy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('access_policy')
                ->default(ClientAccessPolicy::Open->value)
                ->after('grant_types');
        });
    }

    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn('access_policy');
        });
    }
};
