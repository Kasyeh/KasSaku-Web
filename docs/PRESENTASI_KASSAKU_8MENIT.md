# Materi Presentasi KasSaku (7-10 Menit)

## Ringkasan Tujuan
Tujuan presentasi ini adalah menunjukkan bahwa **KasSaku** bukan sekadar CRUD, tetapi sistem manajemen keuangan personal yang memiliki:
- Alur user dan admin yang jelas
- Kontrol keamanan role dan kepemilikan data user
- Integrasi real-time (Firebase RTDB + FCM)
- Fitur analitik serta pelaporan yang relevan untuk penggunaan nyata

Durasi target: **8 menit** (aman di rentang 7-10 menit).

## Rundown Presentasi + Script

### 1) Pembuka (0:00-0:45)
Script:
- "Proyek saya bernama **KasSaku**, aplikasi web manajemen keuangan pribadi."
- "Masalah yang diselesaikan adalah pengguna sering tidak tahu arus kas, overbudget, dan progres tabungan untuk tujuan tertentu."
- "Solusi yang saya bangun mencakup pencatatan transaksi, monitoring statistik, budget kategori, target impian, dan dashboard admin untuk moderasi akun."

### 2) Value dan Fitur Inti (0:45-2:15)
Jelaskan dua peran utama:
- `User`: input pemasukan/pengeluaran, lihat statistik, atur budget kategori, kelola impian, export PDF
- `Admin`: monitor user, block/unblock, proses permintaan unblock, kelola motivasi

Script:
- "Di sisi user, fokusnya pada tracking finansial harian dan progres target."
- "Di sisi admin, fokusnya pada keamanan dan pengawasan akun."

### 3) Arsitektur Teknis Singkat (2:15-3:45)
Stack utama:
- Backend: Laravel + Eloquent + Sanctum
- View: Blade
- Integrasi: Firebase RTDB + FCM

Narasi arsitektur:
- Controller menerima request dan validasi
- Service (`TransactionService`) menangani transaksi + update saldo secara atomik
- Saldo disinkronkan ke RTDB setelah DB commit
- Notifikasi transaksi/alert dikirim via FCM

Script:
- "Saya memisahkan business logic ke service agar controller tetap ringan dan konsisten dipakai oleh web maupun API."

### 4) Keamanan dan Konsistensi Data (3:45-5:00)
Poin yang harus disebut:
- Register publik selalu set `role = user` untuk mencegah privilege escalation
- API memakai Sanctum (`auth:sanctum`)
- Guard `id_user` di path/body untuk mencegah akses data user lain
- Login admin dibatasi untuk jalur web; API Android menolak admin

Script:
- "Fokus saya bukan hanya fitur, tapi juga pengamanan akses dan isolasi data antar user."

### 5) Demo Alur Utama (5:00-7:00)
Urutan demo yang direkomendasikan:
1. Login sebagai user
2. Tambah pemasukan
3. Tambah pengeluaran
4. Tunjukkan perubahan statistik/saldo
5. Set target pengeluaran + budget kategori
6. Tambah impian + setoran impian
7. Export PDF riwayat
8. Login admin: lihat list user + contoh proses unblock

Script penutup demo:
- "Dari demo ini terlihat alur user-end hingga admin moderation berjalan secara end-to-end."

### 6) Penutup (7:00-8:00)
Script:
- "Kesimpulannya, KasSaku membantu pengguna mengambil keputusan finansial harian berbasis data yang terstruktur."
- "Pengembangan lanjut yang siap dilakukan adalah reminder otomatis, analitik prediktif sederhana, dan peningkatan observability."

## Public API/Interface yang Perlu Disebut

### Web routes utama
- Auth + dashboard user/admin
- Transaksi, statistik, impian, budget, export PDF

### API routes utama
- Publik: `/api/login`, `/api/register`, `/api/unblock-request`
- Proteksi token (`auth:sanctum`): saldo, riwayat, statistik, impian, budget, notifikasi

### Kebijakan interface
- Registrasi publik tidak menerima role admin
- Endpoint berbasis user-id memverifikasi actor vs target

## Skenario Uji Saat Ditanya Penguji
1. Skenario normal: tambah pemasukan/pengeluaran dan saldo berubah benar
2. Skenario keamanan: token user A akses data user B -> ditolak `403`
3. Skenario moderation: akun diblokir -> user tidak bisa login normal
4. Skenario unblock: kirim request unblock -> diproses admin -> status akun berubah
5. Skenario pelaporan: filter riwayat -> export PDF berhasil
6. Skenario budget: pengeluaran melewati target -> status over-budget aktif

## Checklist Sebelum Presentasi
- Pastikan kredensial Firebase tersedia (`storage/app/firebase/service-account.json`)
- Siapkan 1 akun user aktif dan 1 akun admin
- Siapkan data transaksi minimal 1 bulan agar grafik/statistik terlihat
- Uji sekali endpoint API dengan token user agar siap saat sesi tanya jawab
- Siapkan contoh user terblokir untuk demo alur unblock

## Asumsi Presentasi
- Audiens: penguji teknis (dosen/pembimbing)
- Fokus: kualitas implementasi dan alasan desain, bukan styling UI
- Durasi tetap 7-10 menit sehingga detail query-level dibahas hanya saat ditanya
