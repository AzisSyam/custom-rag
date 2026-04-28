<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;

class DocumentExtractionService
{
    /**
     * Extract text from a given file path.
     *
     * @param string $filePath Absolute path or storage relative path
     * @param string|null $disk
     * @return string
     * @throws Exception
     */
    public function extractText(string $filePath, ?string $disk = 'public'): string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $absolutePath = Storage::disk($disk)->path($filePath);

        if (!file_exists($absolutePath)) {
            throw new Exception("File not found at: {$absolutePath}");
        }

        $text = match (strtolower($extension)) {
            'txt' => $this->extractFromTxt($absolutePath),
            'pdf' => $this->extractFromPdf($absolutePath),
            default => throw new Exception("Unsupported file extension: {$extension}"),
        };

        // Bersihkan karakter UTF-8 yang cacat (malformed) agar aman diproses oleh JSON / OpenAI
        // 1. mb_scrub menghapus byte sequence yang tidak valid dalam standar UTF-8
        $text = mb_scrub($text, 'UTF-8');
        
        // 2. Hapus control characters (karakter tak kasat mata seperti NUL, ESC) yang sangat dibenci oleh json_encode
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        return $text;
    }

    /**
     * Extract text from a .txt file.
     *
     * @param string $path
     * @return string
     */
    private function extractFromTxt(string $path): string
    {
        return file_get_contents($path);
    }

    /**
     * Extract text from a .pdf file.
     *
     * @param string $path
     * @return string
     */
    private function extractFromPdf(string $path): string
    {
        // Membaca path file eksekusi pdftotext.exe dari file .env (khususnya untuk Windows)
        $binPath = env('PDFTOTEXT_PATH');
        
        // Pass path langsung ke constructor agar tidak meledak saat inisialisasi
        $pdf = $binPath ? new Pdf($binPath) : new Pdf();

        return $pdf->setPdf($path)->text();
    }
}
