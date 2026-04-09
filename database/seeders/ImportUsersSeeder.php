<?php

namespace Database\Seeders;

use App\Enums\IdentityType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->importPegawai();
        $this->importMitra();
    }

    private function importPegawai(): void
    {
        $jsonPath = base_path('pegawai.json');
        if (!file_exists($jsonPath)) {
            $this->command->warn('File pegawai.json tidak ditemukan.');
            return;
        }

        $this->command->info('Memproses data Pegawai...');
        $pegawaiData = json_decode(file_get_contents($jsonPath), true)['rows'] ?? [];
        $count = 0;
        $defaultPassword = Hash::make('3321');

        foreach ($pegawaiData as $pegawai) {
            $email = !empty($pegawai['email']) 
                ? $pegawai['email'] . '@bps.go.id' 
                : $pegawai['niplama'] . '@bps.go.id';

            User::updateOrCreate(
                ['nip' => $pegawai['niplama']],
                [
                    'name' => trim($pegawai['namagelar']),
                    'email' => $email,
                    'password' => $defaultPassword,
                    'identity_type' => IdentityType::Pegawai,
                    'nip_baru' => trim($pegawai['nipbaru']) ?: null,
                    'kd_satker' => $pegawai['kdprop'] . $pegawai['kdkab'],
                    'jabatan' => trim($pegawai['nmjab']),
                    'unit_kerja' => trim($pegawai['nmorg']),
                    'golongan' => trim($pegawai['nmgol']),
                    'jenis_kelamin' => strtoupper(trim($pegawai['jk'])) === 'LK' ? 'L' : 'P',
                    'tempat_lahir' => trim($pegawai['tempatlhr']),
                    'tanggal_lahir' => Carbon::parse($pegawai['tgllahir'])->format('Y-m-d'),
                    'pendidikan' => trim($pegawai['nmpend']),
                    'is_active' => true,
                ]
            );
            $count++;
        }

        $this->command->info("Selesai import {$count} data Pegawai.");
    }

    private function importMitra(): void
    {
        $excelPath = base_path('2026_3321_exportmitrakepka_2026-03-31_143309.xlsx');
        if (!file_exists($excelPath)) {
            $this->command->warn('File Excel mitra tidak ditemukan.');
            return;
        }

        $this->command->info('Memproses data Mitra dari Excel...');
        $reader = IOFactory::createReaderForFile($excelPath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($excelPath);

        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        array_shift($rows); // Remove header

        $count = 0;
        $defaultPassword = Hash::make('3321');
        
        foreach ($rows as $row) {
            $sobatIdRaw = isset($row[12]) ? trim((string)$row[12]) : '';
            if (empty($row[0]) || empty($sobatIdRaw)) {
                continue; // Skip empty rows or rows without SOBAT ID
            }

            // Remove trailing .0 if it was formatted as float
            $sobatId = preg_replace('/\.0+$/', '', $sobatIdRaw);

            $emailRaw = isset($row[13]) ? trim((string)$row[13]) : '';
            $email = !empty($emailRaw) ? $emailRaw : $sobatId . '@mitra.bps.go.id';
            
            // Parse Tempat, Tanggal Lahir (Umur)*
            $ttlRaw = isset($row[6]) ? trim((string)$row[6]) : '';
            $tempatLahir = null;
            $tanggalLahir = null;

            if ($ttlRaw) {
                // Example format: "BANJARNEGARA, 31 Desember 1993 (33)"
                $parts = explode(',', $ttlRaw);
                $tempatLahir = trim($parts[0] ?? '');
                
                if (isset($parts[1])) {
                    $tanggalParts = explode('(', trim($parts[1]));
                    $tanggalRaw = trim($tanggalParts[0] ?? '');
                    $tanggalLahir = $this->parseIndonesianDate($tanggalRaw);
                }
            }

            User::updateOrCreate(
                ['sobat_id' => $sobatId],
                [
                    'name' => trim((string)$row[0]),
                    'email' => $email,
                    'password' => $defaultPassword,
                    'identity_type' => IdentityType::Mitra,
                    'kd_satker' => trim((string)($row[2] ?? '')) . trim((string)($row[3] ?? '')),
                    'jenis_kelamin' => strtoupper(substr(trim((string)($row[7] ?? 'L')), 0, 1)) === 'L' ? 'L' : 'P',
                    'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'pendidikan' => trim((string)($row[8] ?? '')),
                    'is_active' => true,
                ]
            );
            $count++;
        }

        $this->command->info("Selesai import {$count} data Mitra.");
    }

    private function parseIndonesianDate(?string $dateRaw): ?string
    {
        if (!$dateRaw) {
            return null;
        }

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

        $dateRaw = str_ireplace(array_keys($months), array_values($months), $dateRaw);
        
        try {
            // Trim extra spaces inside
            $dateRaw = preg_replace('/\s+/', ' ', trim($dateRaw));
            if (!$dateRaw) return null;
            $date = Carbon::createFromFormat('d m Y', $dateRaw);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
