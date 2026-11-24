<?php

namespace App\Console\Commands;

use App\Enums\IdentityType;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetMitraPasswordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-mitra-passwords {--password=Mitra3321 : Password baru untuk semua akun mitra}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update password seluruh akun mitra statistik menjadi password default (Mitra3321)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $newPassword = (string) $this->option('password');
        $hashed = Hash::make($newPassword);

        $this->info('Memulai pembaruan password seluruh akun mitra...');

        $count = User::where('identity_type', IdentityType::Mitra)->count();

        if ($count === 0) {
            $this->warn('Tidak ditemukan akun mitra.');

            return Command::SUCCESS;
        }

        User::where('identity_type', IdentityType::Mitra)
            ->update([
                'password' => $hashed,
            ]);

        $this->info("Berhasil memperbarui password untuk {$count} akun mitra.");

        return Command::SUCCESS;
    }
}
