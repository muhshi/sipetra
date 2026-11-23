<?php

namespace App\Console\Commands;

use App\Enums\IdentityType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use OpenSpout\Reader\XLSX\Reader;

class UpdateMitraNikCommand extends Command
{
    protected $signature = 'app:update-mitra-nik 
                            {file? : Path ke file Excel mitra} 
                            {--no-insert : Hanya update mitra yang sudah ada di database, jangan buat baru}';

    protected $description = 'Update dan sinkronisasi data NIK, rekening bank, alamat, kontak, dan profil mitra dari file Excel rekrutmen.';

    public function handle(): int
    {
        $filePath = $this->argument('file') ?? base_path('database/data/export_mitra_nik.xlsx');

        if (! file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");

            return self::FAILURE;
        }

        $allowInsert = ! $this->option('no-insert');

        $this->info("Membaca file Excel: {$filePath}");
        $this->info($allowInsert ? 'Mode: Update data yang ada & Tambahkan mitra baru jika belum terdaftar.' : 'Mode: Hanya update data yang sudah ada.');

        $reader = new Reader;
        $reader->open($filePath);

        $updatedCount = 0;
        $createdCount = 0;
        $totalProcessed = 0;
        $defaultPassword = Hash::make('3321');

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                if ($index === 1) {
                    // Header row
                    continue;
                }

                $cells = $row->toArray();
                $totalProcessed++;

                $nama = isset($cells[0]) ? trim((string) $cells[0]) : '';
                $nik = isset($cells[2]) ? trim((string) $cells[2]) : '';
                $nomorRekening = isset($cells[3]) ? trim((string) $cells[3]) : '';
                $namaBank = isset($cells[4]) ? trim((string) $cells[4]) : '';
                $namaPemilikRekening = isset($cells[5]) ? trim((string) $cells[5]) : '';
                $alamat = isset($cells[9]) ? trim((string) $cells[9]) : '';
                $ttlRaw = isset($cells[14]) ? trim((string) $cells[14]) : '';
                $jkRaw = isset($cells[16]) ? strtolower(trim((string) $cells[16])) : '';
                $pendidikan = isset($cells[17]) ? trim((string) $cells[17]) : '';
                $phone = isset($cells[23]) ? trim((string) $cells[23]) : '';
                $sobatId = isset($cells[24]) ? trim((string) $cells[24]) : '';
                $email = isset($cells[25]) ? trim((string) $cells[25]) : '';

                // Bersihkan nilai '-' atau kosong
                $nik = ($nik === '-' || $nik === '') ? null : $nik;
                $nomorRekening = ($nomorRekening === '-' || $nomorRekening === '') ? null : $nomorRekening;
                $namaBank = ($namaBank === '-' || $namaBank === '') ? null : $namaBank;
                $namaPemilikRekening = ($namaPemilikRekening === '-' || $namaPemilikRekening === '') ? null : $namaPemilikRekening;
                $alamat = ($alamat === '-' || $alamat === '') ? null : $alamat;
                $sobatId = ($sobatId === '-' || $sobatId === '' || $sobatId === 'None') ? null : $sobatId;
                $email = ($email === '-' || $email === '') ? null : strtolower($email);

                // Normalisasi No Telp
                $cleanPhone = null;
                if ($phone !== '' && $phone !== '-') {
                    $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
                    $cleanPhone = substr($cleanPhone, 0, 20);
                }

                // Normalisasi Jenis Kelamin
                $jk = null;
                if ($jkRaw !== '') {
                    $jk = in_array($jkRaw, ['lk', 'l', 'laki-laki']) ? 'L' : 'P';
                }

                // Parse Tempat dan Tanggal Lahir
                $tempatLahir = null;
                $tanggalLahir = null;
                if ($ttlRaw !== '' && $ttlRaw !== '-') {
                    $parts = explode(',', $ttlRaw, 2);
                    if (count($parts) === 2) {
                        $tempatLahir = trim($parts[0]);
                        $tanggalLahir = $this->parseIndonesianDate(trim($parts[1]));
                    } else {
                        $tanggalLahir = $this->parseIndonesianDate(trim($ttlRaw));
                    }
                }

                // Cari user di database
                $user = null;
                if ($sobatId) {
                    $user = User::where('sobat_id', $sobatId)->first();
                }
                if (! $user && $email) {
                    $user = User::where('email', $email)->first();
                }
                if (! $user && $nik) {
                    $user = User::where('nik', $nik)->first();
                }

                if ($user) {
                    // Update data mitra yang sudah ada
                    $updateData = [];

                    if ($nik && $user->nik !== $nik) {
                        $updateData['nik'] = $nik;
                    }
                    if ($nomorRekening && $user->nomor_rekening !== $nomorRekening) {
                        $updateData['nomor_rekening'] = $nomorRekening;
                    }
                    if ($namaBank && $user->nama_bank !== $namaBank) {
                        $updateData['nama_bank'] = $namaBank;
                    }
                    if ($namaPemilikRekening && $user->nama_pemilik_rekening !== $namaPemilikRekening) {
                        $updateData['nama_pemilik_rekening'] = $namaPemilikRekening;
                    }
                    if ($alamat && empty($user->alamat)) {
                        $updateData['alamat'] = $alamat;
                    }
                    if ($cleanPhone && empty($user->phone)) {
                        $updateData['phone'] = $cleanPhone;
                    }
                    if ($jk && empty($user->jenis_kelamin)) {
                        $updateData['jenis_kelamin'] = $jk;
                    }
                    if ($tempatLahir && empty($user->tempat_lahir)) {
                        $updateData['tempat_lahir'] = $tempatLahir;
                    }
                    if ($tanggalLahir && empty($user->tanggal_lahir)) {
                        $updateData['tanggal_lahir'] = $tanggalLahir;
                    }
                    if ($pendidikan && empty($user->pendidikan)) {
                        $updateData['pendidikan'] = $pendidikan;
                    }

                    if (! empty($updateData)) {
                        $user->update($updateData);
                        $updatedCount++;
                    }
                } elseif ($allowInsert && $nama !== '') {
                    // Buat akun mitra baru
                    $userEmail = $email ?: ($sobatId ? "{$sobatId}@mitra.bps.go.id" : ($nik ? "{$nik}@mitra.bps.go.id" : null));

                    if (! $userEmail) {
                        continue;
                    }

                    // Pastikan email unik jika ada collision
                    if (User::where('email', $userEmail)->exists()) {
                        $userEmail = $sobatId ? "{$sobatId}@mitra.bps.go.id" : "mitra_{$totalProcessed}@mitra.bps.go.id";
                    }

                    $newUser = User::create([
                        'name' => $nama,
                        'email' => $userEmail,
                        'password' => $defaultPassword,
                        'identity_type' => IdentityType::Mitra->value,
                        'sobat_id' => $sobatId,
                        'nik' => $nik,
                        'nama_bank' => $namaBank,
                        'nomor_rekening' => $nomorRekening,
                        'nama_pemilik_rekening' => $namaPemilikRekening,
                        'alamat' => $alamat,
                        'phone' => $cleanPhone,
                        'jenis_kelamin' => $jk,
                        'tempat_lahir' => $tempatLahir,
                        'tanggal_lahir' => $tanggalLahir,
                        'pendidikan' => $pendidikan ?: null,
                        'kd_satker' => '3321',
                        'is_active' => true,
                    ]);

                    $newUser->assignRole('mitra');
                    $createdCount++;
                }
            }
            break; // Hanya sheet pertama
        }

        $reader->close();

        $this->info('=== Proses Sinkronisasi Berhasil ===');
        $this->info("Total data diproses dari Excel : {$totalProcessed}");
        $this->info("Data mitra yang di-update     : {$updatedCount}");
        $this->info("Data mitra baru yang di-create : {$createdCount}");

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
