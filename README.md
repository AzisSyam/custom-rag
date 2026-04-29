# Custom RAG (Retrieval-Augmented Generation) API

Backend API Laravel yang dirancang untuk mengelola dokumen, melakukan ekstraksi teks, dan melakukan tanya jawab cerdas menggunakan LLM (OpenAI) dengan dukungan data vektor PostgreSQL `pgvector`. Proyek ini adalah sistem **API-only** yang menyediakan infrastruktur lengkap untuk sistem RAG.

## Fitur Utama
- **RAG Chat (Q&A)**: Bertanya kepada AI (GPT-4o-mini) yang akan menjawab berdasarkan konteks dokumen yang Anda unggah.
- **Privacy Filtering**: Keamanan data terjamin. Hasil pencarian dan chat hanya terbatas pada dokumen yang diunggah oleh user itu sendiri (Multi-tenant).
- **Vector Storage**: Integrasi PostgreSQL dengan ekstensi `pgvector` untuk penyimpanan embedding secara efisien.
- **Semantic Search**: Mencari potongan teks berdasarkan makna (semantik), bukan sekadar kata kunci.
- **Document Extraction**: Ekstraksi teks otomatis dari file `.txt` dan `.pdf`.
- **Auto-Documentation**: Dokumentasi API selalu terbaru dan interaktif menggunakan Scramble.
- **API Authentication**: Menggunakan Laravel Sanctum untuk autentikasi berbasis token.

## Teknologi Utama
- **Framework**: Laravel 13
- **Database**: PostgreSQL + `pgvector`
- **AI Engine**: OpenAI (Models: `gpt-4o-mini`, `text-embedding-3-small`)
- **Documentation**: Dedoc Scramble
- **Extraction**: Spatie PDF-to-Text (Poppler Utils)

## Persyaratan Sistem
- **PHP**: 8.3+
- **PostgreSQL**: Versi 15+ dengan ekstensi `pgvector`.
- **Poppler Utils**: Diperlukan untuk ekstraksi PDF (`pdftotext`).

### Panduan Instalasi Poppler
- **Mac (Homebrew)**:
  ```bash
  brew install poppler
  ```
- **Linux/Ubuntu**:
  ```bash
  sudo apt-get update
  sudo apt-get install poppler-utils
  ```
- **Windows**:
  1. Download [Poppler for Windows](https://github.com/oschwartz10612/poppler-windows/releases/).
  2. Ekstrak file zip.
  3. Tambahkan path folder `bin` ke variabel environment `PATH` sistem Anda, ATAU atur `PDFTOTEXT_PATH` di file `.env`.

---

## Setup Proyek

### 1. Instalasi Dependensi
```bash
composer install
```

### 2. Konfigurasi Environment
Salin `.env.example` ke `.env` dan sesuaikan kredensial:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=custom_rag
DB_USERNAME=postgres
DB_PASSWORD=your_password

OPENAI_API_KEY=sk-your-api-key
PDFTOTEXT_PATH="C:\path\to\pdftotext.exe" # Hanya jika di Windows dan tidak masuk PATH
```

### 3. Migrasi Database
```bash
php artisan migrate
```

---

## Dokumentasi API (Interaktif)
Proyek ini menggunakan **Scramble** untuk menghasilkan dokumentasi API secara otomatis. Anda dapat mengaksesnya langsung melalui browser:

**URL**: `http://localhost/docs/api`

Di halaman tersebut, Anda bisa:
- Melihat daftar endpoint lengkap.
- Melihat skema Request/Response.
- Melakukan "Try It Out" untuk mencoba API langsung (memerlukan Authorize dengan Bearer Token).

---

## Arsitektur & Alur Kerja (RAG)

1. **Indexing**: Saat file diupload, teks diekstrak, dipecah menjadi potongan (chunks), diubah menjadi vektor (embedding), dan disimpan ke database.
2. **Retrieval**: Saat user bertanya, sistem mencari 5 potongan teks yang paling relevan secara semantik dengan pertanyaan user (dibatasi milik user tersebut).
3. **Generation**: Konteks teks tersebut dikirim ke OpenAI bersama pertanyaan user untuk menghasilkan jawaban yang akurat.

## Arsitektur Kodingan

Proyek ini menerapkan beberapa design pattern untuk memastikan kode tetap bersih, modular, dan mudah dipelihara:

1. **Service-Repository Pattern**:
   - **Controllers**: Hanya menangani request/response dan validasi dasar.
   - **Services**: Berisi logika bisnis inti (seperti alur RAG, ekstraksi dokumen, dll).
   - **Repositories**: Menangani semua interaksi dengan database (Eloquent).
2. **Contract-Based Development (Interfaces)**:
   - Penggunaan *Interface* (Contracts) pada Service dan Repository memudahkan kita untuk mengganti implementasi di masa depan (misal: berpindah dari OpenAI ke Gemini atau dari PostgreSQL ke Pinecone) tanpa merusak logika bisnis utama.
3. **Sanctum Authentication**: Sistem keamanan berbasis token yang ringan untuk API.

## Lisensi
Proyek ini bersifat open-source di bawah lisensi MIT.

---
*Terakhir diperbarui: 2026-04-29 (Uji coba push)*

*Update: Test push direct to main (2026-04-29) - Round 2*
