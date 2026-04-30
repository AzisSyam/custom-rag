<?php

namespace App\Repositories\Eloquent;

use App\Models\DocumentChunk;
use App\Repositories\Contracts\DocumentChunkRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentDocumentChunkRepository implements DocumentChunkRepositoryInterface
{
    public function __construct(
        private DocumentChunk $model,
    ) {}

    /**
     * Get all chunks for a document, ordered by chunk_order.
     */
    public function findByDocument(string $documentId): Collection
    {
        return $this->model->newQuery()
            ->where('document_id', $documentId)
            ->orderBy('chunk_order')
            ->get();
    }

    /**
     * Create multiple chunks for a document.
     */
    public function createMany(string $documentId, array $chunks): Collection
    {
        $created = new Collection();

        foreach ($chunks as $index => $chunk) {
            $created->push(
                $this->model->newQuery()->create([
                    'document_id' => $documentId,
                    'content' => $chunk['content'],
                    'chunk_order' => $chunk['chunk_order'] ?? $index + 1,
                    'metadata' => $chunk['metadata'] ?? null,
                    'embedding' => $chunk['embedding'] ?? null,
                ])
            );
        }

        return $created;
    }

    /**
     * Perform vector similarity search to find relevant chunks.
     *
     * @param array<int, float> $queryEmbedding
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function similaritySearch(array $queryEmbedding, string $userId, int $limit = 5): Collection
    {
        $vector = '[' . implode(',', $queryEmbedding) . ']';

        return $this->model->newQuery()
            ->whereHas('document', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->select(['*', \Illuminate\Support\Facades\DB::raw("embedding <=> '{$vector}' as distance")])
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete all chunks belonging to a document.
     */
    public function deleteByDocument(string $documentId): bool
    {
        return $this->model->newQuery()
            ->where('document_id', $documentId)
            ->delete() > 0;
    }
}
