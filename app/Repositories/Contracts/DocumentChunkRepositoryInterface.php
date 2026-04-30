<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface DocumentChunkRepositoryInterface
{
    /**
     * Get all chunks for a document, ordered by chunk_order.
     */
    public function findByDocument(string $documentId): Collection;

    /**
     * Create multiple chunks for a document.
     */
    public function createMany(string $documentId, array $chunks): Collection;

    /**
     * Delete all chunks belonging to a document.
     */
    public function deleteByDocument(string $documentId): bool;

    /**
     * Perform vector similarity search to find relevant chunks.
     *
     * @param array<int, float> $queryEmbedding
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function similaritySearch(array $queryEmbedding, string $userId, int $limit = 5): Collection;
}
