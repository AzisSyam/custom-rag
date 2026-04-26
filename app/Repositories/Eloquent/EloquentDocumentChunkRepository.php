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
    public function findByDocument(int $documentId): Collection
    {
        return $this->model->newQuery()
            ->where('document_id', $documentId)
            ->orderBy('chunk_order')
            ->get();
    }

    /**
     * Create multiple chunks for a document.
     */
    public function createMany(int $documentId, array $chunks): Collection
    {
        $created = new Collection();

        foreach ($chunks as $index => $chunk) {
            $created->push(
                $this->model->newQuery()->create([
                    'document_id' => $documentId,
                    'content' => $chunk['content'],
                    'chunk_order' => $chunk['chunk_order'] ?? $index + 1,
                    'metadata' => $chunk['metadata'] ?? null,
                ])
            );
        }

        return $created;
    }

    /**
     * Delete all chunks belonging to a document.
     */
    public function deleteByDocument(int $documentId): bool
    {
        return $this->model->newQuery()
            ->where('document_id', $documentId)
            ->delete() > 0;
    }
}
