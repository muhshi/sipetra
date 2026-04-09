# Arsitektur Ekosistem SSO BPS Demak (Sipetra + Portal)

Dokumen ini merangkum rumusan **Alur Emas (Best Practice)** dari implementasi autentikasi tunggal (*Single Sign-On*) yang dipadukan dengan konsep Aplikasi Portal (*Application Launcher*).

## Kedudukan Portal dalam Ekosistem
Aplikasi Portal pada dasarnya bertindak sebagai **Client App** biasa (sama kedudukannya seperti aplikasi satelit lain: AL FATH, MANGGA MUDA, SIPUTRI, dkk), bukan sebagai Server SSO. Portal semata-mata berguna sebagai ruang tamu utama (Etalase Dasbor) bagi pengguna dalam melihat keseluruhan pustaka aplikasi digital milik BPS.

Hak perwalian sandi dan Server SSO Tetaplah dikendalikan sepenuhnya oleh **Sipetra**.

---

## Alur Pengalaman Pegawai (Seamless Experience)

Berikut merupakan skenario aktivitas harian seorang pegawai BPS yang mencerminkan ekosistem mulus SSO:

1. **Titik Awal Rutinitas (Morning Routine):**
   Pegawai membuka *browser* menuju domain **Portal BPS Demak** dan menekan layar *login*.
   
2. **Lemparan Otentikasi (Redirect):**
   Sistem Portal secara siluman tidak memvalidasi data tersebut sendiri, melainkan segera melempar (*redirect*) halaman pegawai tersebut menuju Server Identitas Tunggal: **Sipetra**.
   
3. **Perekaman Sesi (Session Cookie):**
   Di layar otentikasi UI Sipetra, pegawai memasukkan kredensial *email* dan *password* BPS yang sah. Sipetra memvalidasinya dengan database terpusat, lalu secara spesifik merekam *Cookie SSO* (*Remember Me*) di peramban pengguna. Setelahnya, Sipetra "memantulkan" pegawai tersebut balik menuju titik awal di **Portal**.

4. **Etalase Hak Akses:**
   Setibanya kembali di Portal, layar Dasbor merespon identitas tersebut dengan merentangkan keseluruhan (8) Ikon Aplikasi Sektoral satelit BPS yang sah untuk dimasuki.

---

## Mekanika Keajaiban SSO (Single Sign-On)

Inilah titik di mana arsitektur SSO Sipetra unjuk gigi:

Kala pengguna di Portal memutuskan untuk mengklik salah satu aplikasi, sebut saja **AL FATH**, beginilah rangkaian transaksi kilat (*background processing*) yang sesungguhnya terjadi dalam hitungan milidetik:
1. Menuju Wilayah Baru: Peramban web merespon menuju domain milik (`alfath.bpsdemak.com`).
2. Proses Pencegatan: Layar utama AL FATH menangkap bahwa _session_ lokal penggunanya kosong. Otomatis, AL FATH ikut melempar pengguna terkait ke **Sipetra** untuk meminta surat izin masuk SSO.
3. Deteksi Sidik Jari: Alih-alih menyugesti form *input password* pada umumnya, Sipetra secara cerdas melacak bahwa *Cookie SSO* identitas bersangkutan warisan dari Portal tadi **Masih Sangat Aktif**. 
4. Persetujuan Langsung (*Bypass Auth*): Sipetra melewatkan form persetujuan (katup rahasia yang telah di-*bypass* untuk aplikasi internal) dan seketika mengecap stempel pada permintaan AL FATH lalu melemparnya balik.
5. Realita pada *End-User*: Segala negosiasi berbelit mili-detik antara Sipetra dan AL FATH tersebut tidak dirasakan oleh pengguna sama sekali. Di mata Pegawai, begitu menekan *link* AL FATH pada Portal, hal berikutnya yang dia amati adalah layar aplikasi AL FATH mempesona telah terbuka sempurna tanpa menuntut satupun huruf *password* diulang!

## Kesimpulan

Lumbung "Gerbang Utama" lalu lintas harus secara sistematis disangga oleh Aplikasi Portal sebagai titik tumpu awal rutinitas Pegawai. Begitu sang pengguna berhasil *"melapor"* pada Sipetra di pagi harinya (*check-in*), ia mengantongi paspor abadi untuk berpindah ria dari satu sistem sektoral BPS Demak ke aplikasi digital lainnya secara absolut transparan, efisien, dan secara total menghemat resiko kebocoran identitas data!
