## Informasi Mata Kuliah

- **Mata Kuliah:** Sistem Multimedia
- **Kelompok:** 3
- **Aplikasi:** Aplikasi Perbankan Berbasis Web

## Anggota Kelompok

1. **L200230002** - Lutfi Bagas Wijayanto
2. **L200230008** - Muhammad Dzaky Haidar Abiyyu
3. **L200230015** - Nanang Marvin Kurniawan
4. **L200230020** - Irfan Hanif Saputra
5. **L200230023** - Andika Risky Septiawan
6. **L200230045** - Afkar Fakhru Ryanto
7. **L200230051** - Haydar Aulia Rahman
8. **L200230175** - Muhammad Zidni Khoirul Rizqi
9. **L200230209** - Narendra Satya Adikurniawan

# Aplikasi Perbankan Berbasis Web

## Deskripsi Proyek

Aplikasi Perbankan Berbasis Web merupakan proyek tugas akhir mata kuliah Sistem Multimedia yang dikembangkan menggunakan PHP Native dan MySQL.

Sistem ini menyediakan layanan simulasi perbankan yang meliputi:

* Registrasi dan Login Nasabah
* Manajemen Profil Nasabah
* Manajemen Rekening
* Setor Dana
* Tarik Dana
* Transfer Antar Rekening
* Top Up e-Wallet
* Dashboard Admin
* Audit Log Aktivitas Sistem

Selain fungsi perbankan, proyek ini juga mengimplementasikan materi Sistem Multimedia dan Keamanan Data berupa:

* Upload dan Kompresi Gambar Profil menggunakan PHP GD Library
* Enkripsi dan Dekripsi Data Sensitif menggunakan AES-256 OpenSSL
* Pembuatan Resi Transaksi
* Sistem Notifikasi Aktivitas Pengguna

---

# Teknologi yang Digunakan

| Teknologi             | Versi   |
| --------------------- | ------- |
| PHP                   | 8.x     |
| MySQL                 | 8.x     |
| Bootstrap             | 5.x CDN |
| JavaScript            | ES6     |
| HTML5                 | Latest  |
| CSS3                  | Latest  |
| OpenSSL PHP Extension | Enabled |
| GD Library            | Enabled |

---

# Panduan Instalasi

## 1. Persiapan Database
1. Buka MySQL Client Anda (phpMyAdmin, HeidiSQL, DBeaver, dll).
2. Buat database baru dengan nama `ta_perbankan`.
3. Import file `database.sql` yang berada di root folder proyek ini ke dalam database `ta_perbankan`.

## 2. Konfigurasi Aplikasi
1. Salin file `config/example.database.php` dan beri nama baru `config/database.php`.
2. Buka `config/database.php` dan sesuaikan kredensial database Anda:
   ```php
   $host = 'localhost';
   $username = 'root'; // sesuaikan dengan username database Anda
   $password = '';     // sesuaikan dengan password database Anda
   $database = 'ta_perbankan';
   ```

---

## 3. Cara Menjalankan Aplikasi

### A. Menggunakan XAMPP
1. Pindahkan atau clone folder proyek ini ke dalam direktori `C:\xampp\htdocs\`.
2. Pastikan nama folder adalah `php-perbankan-sismul`.
3. Jalankan **Apache** dan **MySQL** melalui XAMPP Control Panel.
4. Akses di browser: `http://localhost/php-perbankan-sismul`

### B. Menggunakan Laragon
1. Pindahkan folder proyek ke dalam direktori `C:\laragon\www\`.
2. Klik tombol **Start All** pada Laragon.
3. Laragon akan otomatis membuat virtual host. Akses melalui: `http://php-perbankan-sismul.test` atau `http://localhost/php-perbankan-sismul`

### C. Menggunakan Linux (/var/www/html)
1. Pindahkan folder proyek ke `/var/www/html/`.
2. Berikan izin akses folder:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/php-perbankan-sismul
   sudo chmod -R 755 /var/www/html/php-perbankan-sismul
   ```
3. Pastikan ekstensi PHP yang dibutuhkan sudah terpasang (mysqli, gd, openssl).
4. Akses di browser: `http://localhost/php-perbankan-sismul`

### D. Menggunakan PHP Built-in Server
1. Buka Terminal atau Command Prompt.
2. Masuk ke direktori root proyek.
3. Jalankan perintah:
   ```bash
   php -S localhost:8080
   ```
4. Akses di browser: `http://localhost:8080`

---

# Standar Pengembangan Tim

Seluruh anggota wajib mengikuti standar berikut.

## Database Access

Gunakan:

```php
mysqli_connect()
mysqli_prepare()
mysqli_stmt_bind_param()
mysqli_stmt_execute()
mysqli_stmt_get_result()
mysqli_fetch_assoc()
mysqli_num_rows()
```

Dilarang:

```php
mysql_query()
mysql_connect()
```

---

## Password

Seluruh password wajib menggunakan:

```php
password_hash()
password_verify()
```

Contoh:

```php
$hash = password_hash($password, PASSWORD_DEFAULT);

if(password_verify($password, $hash)){
    // login berhasil
}
```

---

## Session

Gunakan:

```php
session_start();
```

Pengecekan login:

```php
if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}
```

---

## Validasi Input

Gunakan:

```php
trim()
htmlspecialchars()
filter_input()
filter_var()
```

Contoh:

```php
$nama = htmlspecialchars(trim($_POST['nama']));
```

---

## Upload File

Gunakan:

```php
move_uploaded_file()
pathinfo()
getimagesize()
```

---

## Kompresi Gambar

Gunakan GD Library:

```php
imagecreatefromjpeg()
imagecreatefrompng()
imagejpeg()
imagepng()
imagedestroy()
```

---

## Enkripsi Data

Gunakan OpenSSL:

```php
openssl_encrypt()
openssl_decrypt()
openssl_random_pseudo_bytes()
```

Algoritma:

```text
AES-256-CBC
```

---

## Format Tanggal

Gunakan:

```php
date()
strtotime()
```

---

## Format Nominal Uang

Gunakan:

```php
number_format()
```

Contoh:

```php
echo number_format($saldo, 0, ',', '.');
```

---

## JSON Response (Jika Diperlukan)

Gunakan:

```php
json_encode()
json_decode()
```

---

# Konvensi Penamaan

## Nama File

Gunakan huruf kecil dan underscore.

Contoh:

```text
login.php
register.php
data_nasabah.php
proses_transfer.php
```

---

## Nama Variabel

Gunakan snake_case.

Contoh:

```php
$nama_nasabah
$nomor_rekening
$total_saldo
```

---

## Nama Function

Gunakan camelCase.

Contoh:

```php
generateNomorRekening()
cekSaldo()
catatAuditLog()
```

---

## Nama Tabel Database

Gunakan snake_case.

Contoh:

```text
users
rekening
transaksi
audit_log
```

---

# Pembagian Tugas

## Anggota 1

Authentikasi & Otorisasi

* Registrasi
* Login
* Logout
* Role Access
* Session Middleware

## Anggota 2

Profil Nasabah

* Profil
* Edit Profil
* Upload Foto
* Kompresi Gambar

## Anggota 3

Rekening & Keamanan Data

* Pembuatan Rekening
* Generate Nomor Rekening
* Enkripsi Rekening

## Anggota 4

Setor Dana

* Setor Dana
* Merchant
* Cetak Resi

## Anggota 5

Tarik Dana

* Tarik Dana
* Riwayat Tarik
* Cetak Resi

## Anggota 6

Transfer Antar Rekening

* Transfer
* Validasi Rekening
* Riwayat Transfer
* Cetak Resi

## Anggota 7

Top Up E-Wallet

* OVO
* DANA
* GoPay
* ShopeePay
* Fee Transaksi

## Anggota 8

Admin

* Dashboard
* Aktivasi Akun
* Verifikasi Akun
* Reset Password

## Anggota 9

Audit Log & Notifikasi

* Audit Aktivitas
* Notifikasi Sistem
* Ekspor Log
---

# Jenis Commit yang Digunakan
## Feature Baru
feat:

## Perbaikan Bug
fix:

## Dokumentasi
docs:

## Perubahan Tampilan
style:

## Refactoring Kode
refactor:

## Pengujian
test:

# Aturan Commit Repository

Gunakan format:
```text
<jenis_commit>: <deskripsi_perubahan>
```

Contoh:

```text
feat: menambahkan modul login
feat: menambahkan upload foto profil
fix: memperbaiki validasi saldo transfer
docs: update README
```

## Catatan

Jika dalam satu commit terdapat 2 perubahan atau lebih, tuliskan setiap perubahan di baris terpisah.

Contoh:

```text
feat: menambahkan fitur login
fix: memperbaiki validasi input
```
---

# Informasi Pengujian

## Akun Admin
- **Email:** admin@bank.com
- **Password:** admin123

## Akun Nasabah
- **Email:** budi@mail.com
- **Password:** nasabah123