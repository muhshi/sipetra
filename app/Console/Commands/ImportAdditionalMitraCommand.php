<?php

namespace App\Console\Commands;

use App\Enums\IdentityType;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ImportAdditionalMitraCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-additional-mitra {--password=Mitra3321 : Password default untuk user mitra baru}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dan verifikasi data mitra statistik ke dalam tabel users';

    /**
     * Daftar 73 mitra dari spreadsheet input.
     *
     * @return array<int, array{email: string, name: string}>
     */
    public static function getMitraData(): array
    {
        return [
            ['email' => 'elisaananda93@gmail.com', 'name' => 'Ananda Elisa Putri'],
            ['email' => 'dewis2655@gmail.com', 'name' => 'Dewi Sartika'],
            ['email' => 'dyahlaila1@gmail.com', 'name' => 'DYAH LAILA SAFRIANI'],
            ['email' => 'musaadah77@gmail.com', 'name' => "Musa'adah"],
            ['email' => 'rowaidah013@gmail.com', 'name' => 'Rowaidah'],
            ['email' => 'kbs.dasir@gmail.com', 'name' => 'Armin Irmasari'],
            ['email' => 'lailaa1234589@gmail.com', 'name' => 'Laelatul fitriya'],
            ['email' => 'nawanoor02@gmail.com', 'name' => 'Munawaroh'],
            ['email' => 'rcristine768@gmail.com', 'name' => 'Siti Wiwin Fauzizah'],
            ['email' => 'itawatisri@gmail.com', 'name' => 'SRI ITAWATI'],
            ['email' => 'elendiningriani@gmail.com', 'name' => 'Elen Dining Riani'],
            ['email' => 'luthfiyanaihda89@gmail.com', 'name' => 'Ihda Luthfiyana'],
            ['email' => 'maesurip@gmail.com', 'name' => 'Muhammad Maesuri'],
            ['email' => 'anissahnur.92@gmail.com', 'name' => 'NUR ANISSAH'],
            ['email' => 'Ikadafa7@gmail.com', 'name' => 'Ika Haryati'],
            ['email' => 'tarnocitraalam@gmail.com', 'name' => 'tarno'],
            ['email' => 'noor.physics86@gmail.com', 'name' => 'NURYANTO'],
            ['email' => 'trisuciariyani230392@gmail.com', 'name' => 'Tri Suci Ariyani'],
            ['email' => 'kuntunretno@gmail.com', 'name' => 'Kuntum Retno Murti'],
            ['email' => 'muhammadabdulmanaf011@gmail.com', 'name' => 'Muhammad Abdul Manaf'],
            ['email' => 'syaidati03@gmail.com', 'name' => 'Syaidati salsabila'],
            ['email' => 'fitrianamarinawati@gmail.com', 'name' => 'Fitriana marinawati'],
            ['email' => 'hanikfatkhiyah8@gmail.com', 'name' => 'Hanik fatkhiyah'],
            ['email' => 'zakiyyatulmunifah12061999@gmail.com', 'name' => 'Zakiyyatul Munifah'],
            ['email' => 'faiz.abdulrokhman@gmail.com', 'name' => 'FAIZ ABDUL ROKHMAN'],
            ['email' => 'himatunnafiah05@gmail.com', 'name' => 'Himatun Nafiah'],
            ['email' => 'lilikermawati198@gmail.com', 'name' => 'Lilik Ermawati Ratna'],
            ['email' => 'putriana.soebardi@gmail.com', 'name' => 'Putriana'],
            ['email' => 'dwipurmonisuciatI65@gmail.com', 'name' => 'DWI PURMONI SUCIATI'],
            ['email' => 'indriyaniserli@gmail.com', 'name' => 'Serli Indriyani'],
            ['email' => 'khoiriyahizza78@gmail.com', 'name' => 'KHOIRIYAH'],
            ['email' => 'Masnurfaizin@gmail.com', 'name' => 'Nur Faizin'],
            ['email' => 'anggicahya975@gmail.com', 'name' => 'SRI Hartatik'],
            ['email' => 'Anafao.ibrahim@gmail.com', 'name' => 'Ana fao azizah'],
            ['email' => 'yyuulliiaannii2@gmail.com', 'name' => 'Yuliani'],
            ['email' => 'mangkubuwono183@gmail.com', 'name' => 'hamdan'],
            ['email' => 'hadinurrohim3@gmail.com', 'name' => 'Hadi Nurrohim'],
            ['email' => 'sepiahartiningsih@gmail.com', 'name' => 'Sepia Hartiningsih'],
            ['email' => 'sitizumarohkuwu1@gmail.com', 'name' => 'siti zumaroh'],
            ['email' => 'lestarial10@gmail.com', 'name' => 'AYU LESTARI'],
            ['email' => 'khulaidah12@gmail.com', 'name' => 'Khulaidah'],
            ['email' => 'rizqikurniadewi@gmail.com', 'name' => 'Rizqi Kurnia Dewi'],
            ['email' => 'shevadvn@gmail.com', 'name' => 'Sheva Devina Artarani'],
            ['email' => '1984winartisri@gmail.com', 'name' => 'Sri Winarti'],
            ['email' => 'dinariasari4@gmail.com', 'name' => 'DINA RIA SARI'],
            ['email' => 'nurharyadi1201@gmail.com', 'name' => 'Nur Haryadi'],
            ['email' => 'syamsudluha88@gmail.com', 'name' => 'Syamsu Dhuka'],
            ['email' => 'kartikasaria94@gmail.com', 'name' => 'Ayu Kartika Sari'],
            ['email' => 'gitaindah561@gmail.com', 'name' => 'INDAH GITA CAHYANI'],
            ['email' => 'Muhammadsubkan683@gmail.com', 'name' => 'MUHAMMAD SUBKAN'],
            ['email' => 'mutiacustamer@gmail.com', 'name' => 'Muthia Nurul Chusna'],
            ['email' => 'ahmadkharis069@gmail.com', 'name' => 'AHMAD KHARIS'],
            ['email' => 'm.arif.rohman13@gmail.com', 'name' => 'Arif Rohman'],
            ['email' => 'Sliestyaningrum@gmail.com', 'name' => 'SHINTA LIESTYANINGRUM'],
            ['email' => 'shaqilawahyu7@gmail.com', 'name' => 'Ali ridho'],
            ['email' => 'kucingsulistiyo@gmail.com', 'name' => 'HAR SULISTIYO'],
            ['email' => 'siswantodemak09@gmail.com', 'name' => 'Nur Laili wakhidah'],
            ['email' => 'Cantikaap86@gmail.com', 'name' => 'Apriyani kurnia'],
            ['email' => 'maslahahfatihul@gmail.com', 'name' => 'Fatihul maslahah'],
            ['email' => 'indahkl252@gmail.com', 'name' => 'Indah Khomsati Lestari'],
            ['email' => 'krisnabay.01@gmail.com', 'name' => "Lu'luil Maknun"],
            ['email' => 'nailulmuna48@gmail.com', 'name' => 'NAILUL MUNA'],
            ['email' => 'naurahnau.6@gmail.com', 'name' => 'Naurah Nadlifah'],
            ['email' => 'khafidhohaliya@gmail.com', 'name' => 'nurul kafidhoh'],
            ['email' => 'riskaristiyanti@gmail.com', 'name' => 'Riska Ristiyanti'],
            ['email' => 'dewisintya.s@gmail.com', 'name' => 'Ita fatmawati'],
            ['email' => 'sqoniatu@gmail.com', 'name' => 'QONIATU SAADAH'],
            ['email' => 'srikandi9631@gmail.com', 'name' => 'SYAHADATUL INAYAH'],
            ['email' => 'talitaastutiningtyas@gmail.com', 'name' => 'Abdul Ghofur'],
            ['email' => 'LaviniaPhilliandita83@gmail.com', 'name' => 'LAVINIA PHILLIANDITA'],
            ['email' => 'nissodani87@gmail.com', 'name' => 'ACHMAD HAMDANI'],
            ['email' => 'anytakusindriasussanty@gmail.com', 'name' => 'Anyta kus indria sussanty'],
            ['email' => 'Ismarokhim@gmail.com', 'name' => 'WASIATUN NIKMAH'],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $defaultPassword = (string) $this->option('password');
        $hashedPassword = Hash::make($defaultPassword);
        $mitraList = self::getMitraData();

        $this->info('Memulai pengecekan dan import '.count($mitraList).' data mitra...');

        $created = 0;
        $skipped = 0;

        foreach ($mitraList as $item) {
            $normalizedEmail = strtolower(trim($item['email']));
            $name = trim($item['name']);

            $existingUser = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

            if ($existingUser) {
                $skipped++;
                $this->line(" - Sudah ada: {$name} ({$existingUser->email}) [ID: {$existingUser->id}]");

                continue;
            }

            User::create([
                'name' => $name,
                'email' => $normalizedEmail,
                'password' => $hashedPassword,
                'identity_type' => IdentityType::Mitra,
                'kd_satker' => '3321',
                'is_active' => true,
            ]);

            $created++;
            $this->info(" + Berhasil dibuat: {$name} ({$normalizedEmail})");
        }

        $this->newLine();
        $this->info("Selesai. Dibuat: {$created}, Sudah Ada: {$skipped}, Total: ".count($mitraList));

        return Command::SUCCESS;
    }
}
