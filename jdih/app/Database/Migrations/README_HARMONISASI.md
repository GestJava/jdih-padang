# Migrasi Database untuk Modul Harmonisasi

Dokumen ini menjelaskan struktur migrasi database untuk modul Harmonisasi pada aplikasi JDIH.

## Daftar Tabel

Migrasi ini membuat beberapa tabel yang saling terkait:

1. `harmonisasi_jenis_peraturan` - Menyimpan jenis-jenis peraturan yang dapat diharmonisasi
2. `harmonisasi_status` - Menyimpan status-status dalam proses harmonisasi
3. `harmonisasi_ajuan` - Tabel utama yang menyimpan data pengajuan harmonisasi
4. `harmonisasi_dokumen` - Menyimpan dokumen-dokumen terkait pengajuan harmonisasi
5. `harmonisasi_histori` - Mencatat riwayat perubahan status dan aktivitas pada pengajuan
6. `harmonisasi_tte_log` - Mencatat aktivitas tanda tangan elektronik pada dokumen harmonisasi

## Cara Menjalankan Migrasi

Untuk menjalankan migrasi, gunakan perintah berikut di terminal:

```bash
php spark migrate
```

Jika ingin menjalankan migrasi spesifik untuk modul harmonisasi saja:

```bash
php spark migrate --path=app/Database/Migrations/2025-01-27-000000_CreateHarmonisasiTables.php
php spark migrate --path=app/Database/Migrations/2025-01-27-000001_CreateHarmonisasiTteLogTable.php
```

## Menjalankan Seeder

Untuk mengisi data awal yang diperlukan, jalankan seeder dengan perintah:

```bash
php spark db:seed HarmonisasiSeeder
```

## Struktur Relasi

Berikut adalah struktur relasi antar tabel:

- `harmonisasi_ajuan` memiliki relasi ke:
  - `harmonisasi_jenis_peraturan` (id_jenis_peraturan)
  - `instansi` (id_instansi_pemohon -> id)
  - `user` (id_user_pemohon, id_petugas_verifikasi)
  - `harmonisasi_status` (id_status_ajuan)

- `harmonisasi_dokumen` memiliki relasi ke:
  - `harmonisasi_ajuan` (id_ajuan)
  - `user` (id_user_uploader)

- `harmonisasi_histori` memiliki relasi ke:
  - `harmonisasi_ajuan` (id_ajuan)
  - `user` (id_user_aksi)
  - `harmonisasi_status` (id_status_sebelumnya, id_status_sekarang)
  - `harmonisasi_dokumen` (id_dokumen_terkait)

- `harmonisasi_tte_log` memiliki relasi ke:
  - `harmonisasi_ajuan` (id_ajuan)
  - `harmonisasi_dokumen` (id_dokumen)
  - `user` (id_user_penandatangan)

## Rollback Migrasi

Jika perlu mengembalikan (rollback) migrasi, gunakan perintah:

```bash
php spark migrate:rollback
```

Untuk rollback satu langkah saja:

```bash
php spark migrate:rollback --batch=1
```

## Catatan Penting

- Pastikan tabel `user` dan `instansi` sudah ada sebelum menjalankan migrasi ini
- Foreign key constraints digunakan untuk menjaga integritas data
- Beberapa field menggunakan soft delete untuk mempertahankan riwayat data