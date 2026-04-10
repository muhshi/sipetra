<?php

namespace App\Console\Commands;

use App\Enums\IdentityType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use function Laravel\Prompts\text;

class ImportPegawaiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pegawai {file? : The path to the JSON file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Data Pegawai dari API JSON Export BPS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        
        if (!$file) {
            $file = text(
                label: 'Masukkan nama/path file JSON Pegawai:',
                default: 'pegawai.json',
                required: true
            );
        }

        if (! File::exists(base_path($file))) {
            $this->error("File $file tidak ditemukan di root folder proyek.");
            return Command::FAILURE;
        }

        $this->info("Membaca file JSON data Pegawai...");
        $json = File::get(base_path($file));
        $data = json_decode($json, true);

        if (! is_array($data)) {
            $this->error("Format JSON tidak valid.");
            return Command::FAILURE;
        }

        // Handle format API BPS yang menaruh data di key 'rows'
        $rows = isset($data['rows']) && is_array($data['rows']) ? $data['rows'] : $data;

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $count = 0;

        foreach ($rows as $row) {
            $nip = trim($row['niplama'] ?? '');
            $nipBaru = trim($row['nipbaru'] ?? '');

            // Skip invalid ones if completely empty NIPs
            if (empty($nip) && empty($nipBaru)) {
                $bar->advance();
                continue;
            }

            try {
                $tglLahir = !empty($row['tgllahir']) ? Carbon::createFromFormat('d-m-Y', trim($row['tgllahir']))->format('Y-m-d') : null;
            } catch (\Exception $e) {
                $tglLahir = null;
            }

            // Normalisasi Format Email
            $emailRaw = trim($row['email'] ?? '');
            if (!empty($emailRaw)) {
                $emailFinal = str_contains($emailRaw, '@') ? $emailRaw : $emailRaw . '@bps.go.id';
            } else {
                $emailFinal = $nip . '@bps.go.id';
            }

            $user = User::updateOrCreate(
                // Matching criteria
                ['nip' => $nip],
                // Update or Create Attributes
                [
                    'nip_baru' => empty($nipBaru) ? null : $nipBaru,
                    'name' => trim($row['namagelar'] ?? 'Tanpa Nama'),
                    'email' => $emailFinal,
                    'password' => bcrypt('password'), // password default
                    'identity_type' => IdentityType::Pegawai,
                    'golongan' => trim($row['nmgol'] ?? ''),
                    'jabatan' => trim($row['nmjab'] ?? ''),
                    'unit_kerja' => trim($row['nmorg'] ?? ''),
                    'kd_satker' => trim($row['kdprop'] ?? '') . trim($row['kdkab'] ?? ''),
                    'jenis_kelamin' => trim($row['jk'] ?? ''),
                    'tempat_lahir' => trim($row['tempatlhr'] ?? ''),
                    'tanggal_lahir' => $tglLahir,
                    'pendidikan' => trim($row['nmpend'] ?? ''),
                    'is_active' => true,
                ]
            );

            // Assign role operator for normal pegawai if you want, else just standard user.
            // BPS usually sets 'operator' conditionally, but we leave it standard (no admin roles).

            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Berhasil meng-import/update $count data Pegawai!");

        return Command::SUCCESS;
    }
}
