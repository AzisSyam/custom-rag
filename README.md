# Custom RAG (Retrieval-Augmented Generation) API

Backend API Laravel yang dirancang untuk mengelola dokumen, melakukan ekstraksi teks, dan menyimpan data vektor menggunakan PostgreSQL `pgvector`. Proyek ini adalah sistem **API-only** untuk penyediaan infrastruktur dasar pencarian berbasis AI (Semantic Search).

## Fitur Utama
- **Vector Storage**: Integrasi PostgreSQL dengan ekstensi `pgvector` untuk penyimpanan embedding.
- **Document Extraction**: Ekstraksi teks otomatis dari file `.txt` dan `.pdf`.
- **Text Chunking**: Pemecahan teks otomatis menjadi potongan-potongan (chunks) yang optimal untuk pemrosesan LLM.
- **API Authentication**: Menggunakan Laravel Sanctum untuk autentikasi berbasis token.
- **Service-Repository Architecture**: Implementasi design pattern yang modular dan mudah diuji.

## Persyaratan Sistem
- **PHP**: 8.3+
- **PostgreSQL**: Versi 15+ dengan ekstensi `pgvector`.
- **Poppler Utils**: Diperlukan untuk ekstraksi PDF (`pdftotext`).
  - **Windows**: [Download poppler-windows](https://blog.alivate.com.au/poppler-windows/) dan tambahkan folder `bin` ke PATH sistem.
  - **Linux/Ubuntu**: `sudo apt-get install poppler-utils`.

## Setup Proyek

### 1. Instalasi Dependensi
```bash
composer install
```

### 2. Konfigurasi Environment
Salin `.env.example` ke `.env` dan sesuaikan kredensial database PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=custom_rag
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Migrasi Database
```bash
php artisan migrate
```

## Dokumentasi API (Endpoints)

### Autentikasi
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/api/register` | Registrasi user baru |
| `POST` | `/api/login` | Login & dapatkan Bearer Token |
| `POST` | `/api/logout` | Logout (revoke token) |
| `GET` | `/api/user` | Dapatkan data user yang login |

### Manajemen Dokumen
*Semua endpoint di bawah memerlukan header `Authorization: Bearer <token>`*

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/api/documents` | List semua dokumen user |
| `POST` | `/api/documents` | Upload dokumen baru |
| `GET` | `/api/documents/{id}` | Detail dokumen & chunks |
| `PATCH` | `/api/documents/{id}` | Update metadata dokumen |
| `DELETE` | `/api/documents/{id}` | Hapus dokumen & chunks |

## Arsitektur & Alur Kerja

1. **Upload**: Dokumen dikirim melalui endpoint `POST /api/documents`.
2. **Extraction**: `DocumentExtractionService` mengambil teks mentah dari file.
3. **Chunking**: Teks dipecah menjadi bagian kecil (chunks) secara otomatis.
4. **Storage**: Data disimpan di PostgreSQL, kolom `embedding` di tabel `document_chunks` siap menampung vektor.

## Lisensi
Proyek ini bersifat open-source di bawah lisensi MIT.
