<?php

namespace App\Services;

use App\Models\Document;
use App\Repositories\Contracts\DocumentChunkRepositoryInterface;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Services\Contracts\EmbeddingServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function __construct(
        private DocumentRepositoryInterface $documentRepo,
        private DocumentChunkRepositoryInterface $chunkRepo,
        private DocumentExtractionService $extractionService,
        private EmbeddingServiceInterface $embeddingService,
    ) {}

    /**
     * Get all documents belonging to a user.
     */
    public function getDocumentsByUser(string $userId): Collection
    {
        return $this->documentRepo->findByUser($userId);
    }

    /**
     * Get a single document with its chunks.
     */
    public function getDocumentWithChunks(string $id): ?Document
    {
        $document = $this->documentRepo->findById($id);

        if ($document) {
            $document->load('chunks');
        }

        return $document;
    }

    /**
     * Store a new document: save file, create DB record, and chunk the content.
     */
    public function storeDocument(string $userId, array $data, UploadedFile $file): Document
    {
        return DB::transaction(function () use ($userId, $data, $file) {
            // Simpan file ke storage dengan nama acak tapi tetap menggunakan ekstensi asli
            $extension = $file->getClientOriginalExtension();
            $filename = \Illuminate\Support\Str::random(40) . '.' . $extension;
            $path = $file->storeAs('documents', $filename, 'public');


            // Buat record dokumen
            $document = $this->documentRepo->create([
                'user_id' => $userId,
                'title' => $data['title'],
                'category' => $data['category'],
                'file_path' => $path,
            ]);

            // Baca konten file dan pecah menjadi chunks menggunakan Extraction Service
            $content = $this->extractionService->extractText($path, 'public');
            $chunks = $this->chunkText($content);

            // Ambil semua konten chunk untuk di-embedding secara batch
            $chunkTexts = array_column($chunks, 'content');
            $embeddings = $this->embeddingService->embedBatch($chunkTexts);

            // Gabungkan embedding ke dalam data chunks
            foreach ($chunks as $index => &$chunk) {
                $chunk['embedding'] = $embeddings[$index] ?? null;
            }

            // Simpan chunks ke database
            $this->chunkRepo->createMany($document->id, $chunks);

            return $document->load('chunks');
        });
    }

    /**
     * Update document metadata and optionally re-index content if a new file is uploaded.
     */
    public function updateDocument(string $id, array $data, ?UploadedFile $file = null): Document
    {
        return DB::transaction(function () use ($id, $data, $file) {
            $document = $this->documentRepo->findById($id);
            
            if (!$document) {
                throw new \Exception("Document not found");
            }

            if ($file) {
                // 1. Hapus file lama jika ada
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                // 2. Hapus chunks lama
                $this->chunkRepo->deleteByDocument($id);

                // 3. Simpan file baru
                $extension = $file->getClientOriginalExtension();
                $filename = \Illuminate\Support\Str::random(40) . '.' . $extension;
                $path = $file->storeAs('documents', $filename, 'public');

                // 4. Ekstrak dan embed teks baru
                $content = $this->extractionService->extractText($path, 'public');
                $chunks = $this->chunkText($content);

                $chunkTexts = array_column($chunks, 'content');
                $embeddings = $this->embeddingService->embedBatch($chunkTexts);

                foreach ($chunks as $index => &$chunk) {
                    $chunk['embedding'] = $embeddings[$index] ?? null;
                }

                // 5. Simpan chunks baru
                $this->chunkRepo->createMany($id, $chunks);

                // Update file path di data metadata
                $data['file_path'] = $path;
            }

            // Update metadata (dan file_path jika ada file baru)
            $this->documentRepo->update($id, $data);

            return $this->documentRepo->findById($id)->load('chunks');
        });
    }

    /**
     * Delete a document, its chunks, and the physical file.
     */
    public function deleteDocument(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $document = $this->documentRepo->findById($id);

            if (! $document) {
                return false;
            }

            // Hapus file fisik dari storage
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Hapus chunks (juga akan terhapus via cascade, tapi kita eksplisit)
            $this->chunkRepo->deleteByDocument($id);

            // Hapus dokumen
            return $this->documentRepo->delete($id);
        });
    }

    /**
     * Pecah teks menjadi potongan-potongan (chunks).
     *
     * @return array<int, array{content: string, chunk_order: int}>
     */
    protected function chunkText(string $content, int $chunkSize = 1000): array
    {
        $chunks = [];
        $text = trim($content);

        if (empty($text)) {
            return $chunks;
        }

        $segments = str_split($text, $chunkSize);

        foreach ($segments as $index => $segment) {
            $chunks[] = [
                'content' => $segment,
                'chunk_order' => $index + 1,
                'metadata' => [
                    'char_count' => mb_strlen($segment),
                ],
            ];
        }

        return $chunks;
    }
}
