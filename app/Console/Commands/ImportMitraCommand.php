<?php

namespace App\Console\Commands;

use App\Enums\IdentityType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OpenSpout\Reader\XLSX\Reader;
use function Laravel\Prompts\text;

class ImportMitraCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:mitra {file? : The path to the Excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Data Mitra dari Export Excel SOBAT BPS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        
        if (!$file) {
            $file = text(
                label: 'Masukkan nama/path file Excel Mitra:',
                default: '2026_3321_exportmitrakepka_2026-03-31_143309.xlsx',
                required: true
            );
        }

        $filePath = base_path($file);

        if (! File::exists($filePath)) {
            $this->error("File $file tidak ditemukan.");
            return Command::FAILURE;
        }

        $this->info("Membaca file Excel $file...");

        $reader = new Reader();
        
        try {
            $reader->open($filePath);
        } catch (\Exception $e) {
            $this->error("Gagal membuka file Excel: " . $e->getMessage());
            return Command::FAILURE;
        }

        $count = 0;
        $headers = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells = $row->toArray();

                // Header mapping
                if ($rowIndex === 1) {
                    foreach ($cells as $index => $colName) {
                        $headers[trim($colName)] = $index;
                    }
                    continue;
                }

                // Make sure we have SOBAT ID column mapped
                if (!isset($headers['SOBAT ID'])) {
                    $this->error("File Excel tidak memiliki kolom 'SOBAT ID'. Pastikan format export benar.");
                    return Command::FAILURE;
                }

                // Data mapping
                $sobatId = isset($headers['SOBAT ID']) && isset($cells[$headers['SOBAT ID']]) ? trim((string)$cells[$headers['SOBAT ID']]) : '';
                
                if (empty($sobatId)) {
                    continue; // Skip if no SOBAT ID
                }

                $nama = isset($headers['Nama Lengkap']) && isset($cells[$headers['Nama Lengkap']]) ? trim((string)$cells[$headers['Nama Lengkap']]) : 'Tanpa Nama';
                $email = isset($headers['Email']) && isset($cells[$headers['Email']]) ? trim((string)$cells[$headers['Email']]) : null;
                $phone = isset($headers['No Telp']) && isset($cells[$headers['No Telp']]) ? trim((string)$cells[$headers['No Telp']]) : null;
                
                $prov = isset($headers['Alamat Prov']) && isset($cells[$headers['Alamat Prov']]) ? trim((string)$cells[$headers['Alamat Prov']]) : '';
                $kab = isset($headers['Alamat Kab']) && isset($cells[$headers['Alamat Kab']]) ? trim((string)$cells[$headers['Alamat Kab']]) : '';
                $kdSatker = $prov . $kab;

                $jkStr = isset($headers['Jenis Kelamin']) && isset($cells[$headers['Jenis Kelamin']]) ? trim((string)$cells[$headers['Jenis Kelamin']]) : '';
                $jenisKelamin = strtoupper($jkStr) === 'L' || strtoupper($jkStr) === 'LK' || strtoupper($jkStr) === 'LAKI-LAKI' ? 'LK' : 
                                (strtoupper($jkStr) === 'P' || strtoupper($jkStr) === 'PR' || strtoupper($jkStr) === 'PEREMPUAN' ? 'PR' : '');

                $pendidikan = isset($headers['Pendidikan']) && isset($cells[$headers['Pendidikan']]) ? trim((string)$cells[$headers['Pendidikan']]) : null;

                $tempatLahir = null;
                $tglLahir = null;
                $ttlRaw = isset($headers['Tempat, Tanggal Lahir (Umur)*']) && isset($cells[$headers['Tempat, Tanggal Lahir (Umur)*']]) ? trim((string)$cells[$headers['Tempat, Tanggal Lahir (Umur)*']]) : '';
                
                // Parse "Tempat, dd-mm-yyyy (Umur)" Format
                if (!empty($ttlRaw)) {
                    $parts = explode(',', $ttlRaw);
                    if (count($parts) > 1) {
                        $tempatLahir = trim($parts[0]);
                        $datePart = explode('(', trim($parts[1])); // Remove Umur
                        $dmy = trim($datePart[0]);
                        
                        try {
                            $tglLahir = Carbon::parse($dmy)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $tglLahir = null;
                        }
                    } else {
                        $tempatLahir = $ttlRaw;
                    }
                }

                if (empty($email)) {
                    $email = 'mitra_' . $sobatId . '@bps.go.id';
                }

                User::updateOrCreate(
                    ['sobat_id' => $sobatId],
                    [
                        'name' => $nama,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => bcrypt('password123'), // Default mitra password
                        'identity_type' => IdentityType::Mitra,
                        'kd_satker' => $kdSatker,
                        'jenis_kelamin' => $jenisKelamin,
                        'tempat_lahir' => $tempatLahir,
                        'tanggal_lahir' => $tglLahir,
                        'pendidikan' => $pendidikan,
                        'is_active' => true,
                    ]
                );

                $count++;
            }
        }

        $reader->close();

        $this->info("Berhasil meng-import/update $count data Mitra Statistik!");
        return Command::SUCCESS;
    }
}
