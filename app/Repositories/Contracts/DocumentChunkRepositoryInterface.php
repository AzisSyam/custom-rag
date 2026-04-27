<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DocumentChunkRepositoryInterface
{
    /**
     * Get all chunks for a document, ordered by chunk_order.
     */
    public function findByDocument(int $documentId): Collection;

    /**
     * Create multiple chunks for a document.
     */
    public function createMany(int $documentId, array $chunks): Collection;

    /**
     * Delete all chunks belonging to a document.
     */
    public function deleteByDocument(int $documentId): bool;

    /**
     * Perform vector similarity search to find relevant chunks.
     *
     * @param array<int, float> $queryEmbedding
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function similaritySearch(array $queryEmbedding, int $userId, int $limit = 5): Collection;
}
