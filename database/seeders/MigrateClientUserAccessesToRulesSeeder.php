<?php

namespace Database\Seeders;

use App\Enums\AccessRuleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateClientUserAccessesToRulesSeeder extends Seeder
{
    /**
     * Pindahkan semua entri client_user_accesses ke client_access_rules
     * sebagai rule_type = 'user'.
     * Jalankan seeder ini SEKALI setelah migrasi schema berjalan.
     */
    public function run(): void
    {
        $accesses = DB::table('client_user_accesses')->get();

        if ($accesses->isEmpty()) {
            $this->command->info('Tidak ada data client_user_accesses yang perlu dimigrasikan.');

            return;
        }

        $now = now();
        $rows = $accesses->map(fn ($access) => [
            'client_id' => $access->client_id,
            'rule_type' => AccessRuleType::User->value,
            'rule_value' => (string) $access->user_id,
            'client_role_id' => $access->client_role_id,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        DB::table('client_access_rules')->insert($rows);

        $this->command->info("Berhasil memigrasikan {$accesses->count()} entri ke client_access_rules.");
    }
}
