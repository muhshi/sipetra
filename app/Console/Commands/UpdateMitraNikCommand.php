<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use OpenSpout\Reader\XLSX\Reader;

class UpdateMitraNikCommand extends Command
{
    protected $signature = 'app:update-mitra-nik {file? : Path ke file Excel mitra}';

    protected $description = 'Update data NIK, alamat, no telp, dan data profil mitra dari file rekrutmen/export mitra.';

    public function handle(): int
    {
        $filePath = $this->argument('file') ?? base_path('database/data/export_mitra_nik.xlsx');

        if (! file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");

            return self::FAILURE;
        }

        $this->info("Membaca file Excel: {$filePath}");

        $reader = new Reader;
        $reader->open($filePath);

        $updatedCount = 0;
        $notFoundCount = 0;
        $totalProcessed = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                if ($index === 1) {
                    // Header row
                    continue;
                }

                $cells = $row->toArray();
                $totalProcessed++;

                $sobatId = isset($cells[24]) ? trim((string) $cells[24]) : '';
                $nik = isset($cells[2]) ? trim((string) $cells[2]) : '';
                $email = isset($cells[25]) ? trim((string) $cells[25]) : '';
                $phone = isset($cells[23]) ? trim((string) $cells[23]) : '';
                $alamat = isset($cells[9]) ? trim((string) $cells[9]) : '';
                $nama = isset($cells[0]) ? trim((string) $cells[0]) : '';
                $jkRaw = isset($cells[16]) ? strtolower(trim((string) $cells[16])) : '';
                $pendidikan = isset($cells[17]) ? trim((string) $cells[17]) : '';
                $ttlRaw = isset($cells[14]) ? trim((string) $cells[14]) : '';

                // Cari user di database berdasarkan sobat_id atau email
                $user = null;
                if ($sobatId !== '') {
                    $user = User::where('sobat_id', $sobatId)->first();
                }
                if (! $user && $email !== '') {
                    $user = User::where('email', $email)->where('identity_type', 'mitra')->first();
                }

                if (! $user) {
                    $notFoundCount++;

                    continue;
                }

                $updateData = [];

                if ($nik !== '') {
                    $updateData['nik'] = $nik;
                }

                if ($alamat !== '') {
                    $updateData['alamat'] = $alamat;
                }

                if ($phone !== '') {
                    // Normalisasi no telp
                    $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
                    $updateData['phone'] = substr($cleanPhone, 0, 20);
                }

                if ($pendidikan !== '' && empty($user->pendidikan)) {
                    $updateData['pendidikan'] = $pendidikan;
                }

                if ($jkRaw !== '' && empty($user->jenis_kelamin)) {
                    $updateData['jenis_kelamin'] = in_array($jkRaw, ['lk', 'l', 'laki-laki']) ? 'L' : 'P';
                }

                // Parse TTL jika belum ada
                if ($ttlRaw !== '' && (empty($user->tempat_lahir) || empty($user->tanggal_lahir))) {
                    $parts = explode(',', $ttlRaw, 2);
                    if (count($parts) === 2) {
                        if (empty($user->tempat_lahir)) {
                            $updateData['tempat_lahir'] = trim($parts[0]);
                        }
                        if (empty($user->tanggal_lahir)) {
                            $tglString = trim($parts[1]);
                            $parsedDate = $this->parseIndonesianDate($tglString);
                            if ($parsedDate) {
                                $updateData['tanggal_lahir'] = $parsedDate;
                            }
                        }
                    } else {
                        // Hanya tanggal lahir tanpa tempat
                        if (empty($user->tanggal_lahir)) {
                            $parsedDate = $this->parseIndonesianDate(trim($ttlRaw));
                            if ($parsedDate) {
                                $updateData['tanggal_lahir'] = $parsedDate;
                            }
                        }
                    }
                }

                if (! empty($updateData)) {
                    $user->update($updateData);
                    $updatedCount++;
                }
            }
            break; // Hanya sheet pertama
        }

        $reader->close();

        $this->info('Proses selesai!');
        $this->info("Total data di Excel: {$totalProcessed}");
        $this->info("Data mitra di database yang berhasil di-update: {$updatedCount}");
        $this->info("Data di Excel yang belum terdaftar di database: {$notFoundCount}");

        return self::SUCCESS;
    }

    private function parseIndonesianDate(string $raw): ?string
    {
        $months = [
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12',
        ];

        $str = str_ireplace(array_keys($months), array_values($months), $raw);
        $str = preg_replace('/\s+/', ' ', trim($str));

        try {
            return Carbon::createFromFormat('d m Y', $str)->toDateString();
        } catch (\Throwable) {
            try {
                return Carbon::parse($raw)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }
    }
}
