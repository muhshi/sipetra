<?php

namespace Database\Seeders;

use App\Enums\IdentityType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PegawaiSeeder extends Seeder
{
    /**
     * Seed users dari data pegawai.json.
     *
     * Aturan generate email:
     * - Jika field "email" di JSON tidak null/kosong → pakai nilai itu + @bps.go.id
     * - Jika null/kosong → generate dari nip lama (fallback)
     *
     * Password default: nip_baru (18 digit) atau nip lama bila nip_baru kosong.
     */
    public function run(): void
    {
        $jsonPath = base_path('pegawai.json');

        if (! file_exists($jsonPath)) {
            $this->command->warn('File pegawai.json tidak ditemukan, skip PegawaiSeeder.');

            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $rows = $data['rows'] ?? [];

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $nipLama = trim($row['niplama'] ?? '');
            $nipBaru = trim($row['nipbaru'] ?? '');
            $emailPrefix = trim($row['email'] ?? '');

            // Tentukan email: pakai prefix dari JSON jika ada, fallback ke nip lama
            $email = ($emailPrefix !== '' && $emailPrefix !== null)
                ? strtolower($emailPrefix).'@bps.go.id'
                : strtolower($nipLama).'@bps.go.id';

            // Skip jika email sudah ada agar idempoten
            if (User::where('email', $email)->exists()) {
                $skipped++;

                continue;
            }

            // Password: nip baru (strip spasi) jika tersedia, lain pakai nip lama
            $nipBaruClean = trim($nipBaru);
            $password = ($nipBaruClean !== '') ? $nipBaruClean : $nipLama;

            // Parse tanggal lahir (format d-m-Y)
            $tanggalLahir = null;
            if (! empty($row['tgllahir'])) {
                try {
                    $tanggalLahir = Carbon::createFromFormat('d-m-Y', $row['tgllahir'])->toDateString();
                } catch (\Throwable) {
                    $tanggalLahir = null;
                }
            }

            // Jenis kelamin: normalkan ke 'LK' atau 'PR'
            $jk = strtoupper(trim($row['jk'] ?? ''));

            // Status aktif: non-aktif jika nmstpeg = Pensiun atau Melimpah
            $nmstpeg = strtolower(trim($row['nmstpeg'] ?? ''));
            $isActive = ! in_array($nmstpeg, ['pensiun', 'meninggal', 'melimpah']);

            $user = User::create([
                'name' => trim($row['namagelar'] ?? ''),
                'email' => $email,
                'password' => Hash::make($password),
                'identity_type' => IdentityType::Pegawai->value,
                'nip' => $nipLama !== '' ? $nipLama : null,
                'nip_baru' => $nipBaruClean !== '' ? $nipBaruClean : null,
                'kd_satker' => trim($row['kdorg'] ?? '') ?: null,
                'jabatan' => trim($row['nmjab'] ?? '') ?: null,
                'unit_kerja' => trim($row['nmorg'] ?? '') ?: null,
                'golongan' => trim($row['nmgol'] ?? '') ?: null,
                'jenis_kelamin' => in_array($jk, ['LK', 'PR']) ? $jk : null,
                'tempat_lahir' => trim($row['tempatlhr'] ?? '') ?: null,
                'tanggal_lahir' => $tanggalLahir,
                'pendidikan' => trim($row['nmpend'] ?? '') ?: null,
                'is_active' => $isActive,
            ]);

            $user->assignRole('pegawai');

            // Create Employee Profile
            $mkTahun = 0;
            $mkBulan = 0;
            if (! empty($row['tmtcpns'])) {
                try {
                    $tmtCpns = Carbon::createFromFormat('d-m-Y', $row['tmtcpns']);
                    $diff = $tmtCpns->diff(Carbon::now());
                    $mkTahun = $diff->y;
                    $mkBulan = $diff->m;
                } catch (\Throwable) {
                }
            }

            $agamaCode = trim($row['agama'] ?? '');
            $agamaMap = [
                '1' => 'Islam',
                '2' => 'Protestan',
                '3' => 'Katolik',
                '4' => 'Hindu',
                '5' => 'Budha',
                '6' => 'Konghucu',
            ];

            $user->employeeProfile()->create([
                'tmt_cpns' => $this->parseDate($row['tmtcpns'] ?? null),
                'tmt_pns' => $this->parseDate($row['tmtpns'] ?? null),
                'tmt_golongan' => $this->parseDate($row['tmtgol'] ?? null),
                'tmt_jabatan' => $this->parseDate($row['tmtjab'] ?? null),
                'kd_gol' => trim($row['kdgol'] ?? null),
                'kd_jab' => trim($row['kdstjab'] ?? null),
                'status_pegawai' => trim($row['nmstpeg'] ?? null),
                'mk_tahun' => $mkTahun,
                'mk_bulan' => $mkBulan,
                'agama' => $agamaMap[$agamaCode] ?? $agamaCode,
                'tgl_ijazah' => $this->parseDate($row['tglijazah'] ?? null),
            ]);

            $created++;
        }

        $this->command->info("PegawaiSeeder: {$created} user dibuat, {$skipped} dilewati (sudah ada).");
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('d-m-Y', $date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
