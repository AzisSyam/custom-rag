# Custom RAG (Retrieval-Augmented Generation) Infrastructure

Sistem backend Laravel yang dirancang untuk mengelola dokumen, melakukan ekstraksi teks, dan menyimpan data vektor menggunakan PostgreSQL `pgvector`. Proyek ini difokuskan pada penyediaan infrastruktur dasar untuk pencarian berbasis AI (Semantic Search).

## Fitur Utama
- **Vector Storage**: Integrasi PostgreSQL dengan ekstensi `pgvector` untuk penyimpanan embedding.
- **Document Extraction**: Ekstraksi teks otomatis dari file `.txt` dan `.pdf`.
- **Text Chunking**: Pemecahan teks otomatis menjadi potongan-potongan (chunks) yang optimal untuk pemrosesan LLM.
- **Service-Repository Architecture**: Implementasi design pattern yang modular untuk memastikan kode mudah dipelihara dan diuji.

## Persyaratan Sistem
- **PHP**: 8.2 atau lebih baru.
- **PostgreSQL**: Versi 15+ dengan ekstensi `pgvector` yang sudah terinstall.
- **Poppler Utils**: Diperlukan untuk fungsionalitas ekstraksi PDF (`pdftotext`).
  - **Windows**: [Download poppler-windows](https://blog.alivate.com.au/poppler-windows/) dan tambahkan folder `bin` ke PATH sistem.
  - **Linux/Ubuntu**: Jalankan `sudo apt-get install poppler-utils`.

## Setup Proyek

### 1. Instalasi Dependensi
Jalankan perintah berikut untuk menginstall library PHP yang diperlukan:
```bash
composer install
```

### 2. Konfigurasi Environment
Buat file `.env` dari template yang ada dan sesuaikan kredensial database PostgreSQL Anda:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=custom_rag
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Migrasi Database
Jalankan migrasi untuk membuat tabel dokumen dan mengaktifkan ekstensi vektor:
```bash
php artisan migrate
```

### 4. Storage Setup
Pastikan folder storage memiliki permission yang tepat dan buat link ke public:
```bash
php artisan storage:link
```

## Arsitektur & Alur Kerja

### Pemrosesan Dokumen
Alur saat dokumen diunggah melalui `DocumentService`:
1. **Storage**: File disimpan ke disk storage lokal/public.
2. **Extraction**: `DocumentExtractionService` mendeteksi format file dan mengekstrak teks mentah.
3. **Chunking**: Teks dipecah menjadi beberapa bagian (default 1000 karakter) untuk efisiensi konteks AI.
4. **Database**: Metadata dokumen disimpan di tabel `documents`, dan setiap potongan teks disimpan di tabel `document_chunks` lengkap dengan kolom `embedding` (tipe data `vector`).

### Struktur Layer
- **Services**: Berisi logika bisnis inti (`DocumentService`, `DocumentExtractionService`).
- **Repositories**: Menangani abstraksi akses database menggunakan Eloquent.
- **Models**: `Document` dan `DocumentChunk` yang sudah mendukung casting data vektor.

## Lisensi
Proyek ini bersifat open-source di bawah lisensi MIT.
