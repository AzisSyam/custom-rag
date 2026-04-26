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
}
