<?php

namespace Database\Seeders;

use App\Enums\IdentityType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use OpenSpout\Reader\XLSX\Reader;

class MitraSeeder extends Seeder
{
    /**
     * Seed users dari data mitra di file xlsx exportmitrakepka.
     *
     * Kolom xlsx (0-indexed):
     *  0  = Nama Lengkap
     *  1  = Alamat Detail
     *  6  = Tempat, Tanggal Lahir (Umur)* → format "KOTA, DD Bulan YYYY (umur)"
     *  7  = Jenis Kelamin  → Lk / Pr
     *  8  = Pendidikan
     *  9  = Pekerjaan
     * 11  = No Telp
     * 12  = SOBAT ID
     * 13  = Email (pribadi mitra, bisa null)
     *
     * Email Sipetra: sobat_id@bps.go.id
     * Password default: sobat_id
     */
    public function run(): void
    {
        $xlsxPath = base_path('2026_3321_exportmitrakepka_2026-03-31_143309.xlsx');

        if (! file_exists($xlsxPath)) {
            $this->command->warn('File xlsx mitra tidak ditemukan, skip MitraSeeder.');

            return;
        }

        $reader = new Reader;
        $reader->open($xlsxPath);

        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            break; // Sheet pertama saja
        }
        $reader->close();

        // Lewati baris header (baris 0)
        $dataRows = array_slice($rows, 1);

        $created = 0;
        $skipped = 0;

        foreach ($dataRows as $row) {
            $namaLengkap = trim($row[0] ?? '');
            $sobatId = trim($row[12] ?? '');
            $phone = trim($row[11] ?? '');

            // SOBAT ID wajib ada
            if ($sobatId === '') {
                $skipped++;

                continue;
            }

            $email = strtolower($sobatId).'@bps.go.id';

            // Skip jika sudah ada (idempoten)
            if (User::where('email', $email)->exists() || User::where('sobat_id', $sobatId)->exists()) {
                $skipped++;

                continue;
            }

            // Parse tanggal lahir dari format "KOTA, DD Bulan YYYY (umur)"
            $tanggalLahir = null;
            $tempatLahir = null;
            $tglLahirRaw = trim($row[6] ?? '');

            if ($tglLahirRaw !== '') {
                // Pisah di koma pertama → "KOTA" | "DD Bulan YYYY (umur)"
                $parts = explode(',', $tglLahirRaw, 2);
                $tempatLahir = ucwords(strtolower(trim($parts[0] ?? '')));

                if (isset($parts[1])) {
                    // Buang "(umur)" di akhir
                    $tglString = preg_replace('/\s*\(\d+\)\s*$/', '', trim($parts[1]));
                    try {
                        $tanggalLahir = Carbon::createFromFormat('d F Y', $tglString)->toDateString();
                    } catch (\Throwable) {
                        // Coba format lain jika gagal
                        $tanggalLahir = null;
                    }
                }
            }

            // Jenis kelamin: normalkan "Lk"→"LK", "Pr"→"PR"
            $jkRaw = strtolower(trim($row[7] ?? ''));
            $jk = match ($jkRaw) {
                'lk', 'l', 'laki-laki', 'laki' => 'LK',
                'pr', 'p', 'perempuan' => 'PR',
                default => null,
            };

            User::create([
                'name' => $namaLengkap,
                'email' => $email,
                'password' => Hash::make($sobatId),
                'identity_type' => IdentityType::Mitra->value,
                'sobat_id' => $sobatId,
                'pendidikan' => trim($row[8] ?? '') ?: null,
                'phone' => $phone !== '' ? $phone : null,
                'jenis_kelamin' => $jk,
                'tempat_lahir' => $tempatLahir,
                'tanggal_lahir' => $tanggalLahir,
                'is_active' => true,
            ]);

            $created++;
        }

        $this->command->info("MitraSeeder: {$created} user dibuat, {$skipped} dilewati (sudah ada / SOBAT ID kosong).");
    }
}
