<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateEmployeeProfiles extends Command
{
    protected $signature = 'app:update-employee-profiles';

    protected $description = 'Sinkronisasi data kepegawaian dari pegawai.json ke tabel employee_profiles';

    public function handle()
    {
        $jsonPath = base_path('pegawai.json');

        if (! file_exists($jsonPath)) {
            $this->error('File pegawai.json tidak ditemukan.');

            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $rows = $data['rows'] ?? [];

        $this->info('Memulai sinkronisasi '.count($rows).' data...');
        $bar = $this->output->createProgressBar(count($rows));

        foreach ($rows as $row) {
            $nipLama = trim($row['niplama'] ?? '');
            $nipBaru = trim($row['nipbaru'] ?? '');

            // Cari user berdasarkan NIP Baru atau NIP Lama
            $user = User::where('nip_baru', $nipBaru)
                ->orWhere('nip', $nipLama)
                ->first();

            if ($user) {
                // 1. Update Status Aktif (Pensiun = Inaktif)
                $nmstpeg = strtolower(trim($row['nmstpeg'] ?? ''));
                $isActive = ! in_array($nmstpeg, ['pensiun', 'meninggal', 'melimpah']);

                $user->update([
                    'is_active' => $isActive,
                ]);

                // 2. Hitung Masa Kerja
                $mkTahun = 0;
                $mkBulan = 0;
                if (! empty($row['tmtcpns'])) {
                    try {
                        $tmtCpns = Carbon::createFromFormat('d-m-Y', $row['tmtcpns']);
                        $diff = $tmtCpns->diff(Carbon::now());
                        $mkTahun = $diff->y;
                        $mkBulan = $diff->m;
                    } catch (\Throwable) {
                        // ignore
                    }
                }

                // 3. Mapping Agama
                $agamaCode = trim($row['agama'] ?? '');
                $agamaMap = [
                    '1' => 'Islam',
                    '2' => 'Protestan',
                    '3' => 'Katolik',
                    '4' => 'Hindu',
                    '5' => 'Budha',
                    '6' => 'Konghucu',
                ];
                $agama = $agamaMap[$agamaCode] ?? $agamaCode;

                // 4. Update/Create Profile
                $user->employeeProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'tmt_cpns' => $this->parseDate($row['tmtcpns'] ?? null),
                        'tmt_pns' => $this->parseDate($row['tmtpns'] ?? null),
                        'tmt_golongan' => $this->parseDate($row['tmtgol'] ?? null),
                        'tmt_jabatan' => $this->parseDate($row['tmtjab'] ?? null),
                        'kd_gol' => trim($row['kdgol'] ?? null),
                        'kd_jab' => trim($row['kdstjab'] ?? null),
                        'status_pegawai' => trim($row['nmstpeg'] ?? null),
                        'mk_tahun' => $mkTahun,
                        'mk_bulan' => $mkBulan,
                        'agama' => $agama,
                        'tgl_ijazah' => $this->parseDate($row['tglijazah'] ?? null),
                    ]
                );
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Sinkronisasi selesai.');
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
