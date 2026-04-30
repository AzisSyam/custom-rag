<?php

namespace App\Repositories\Contracts;

use App\Models\Document;
use Illuminate\Database\Eloquent\Collection;

interface DocumentRepositoryInterface
{
    /**
     * Get all documents.
     */
    public function all(): Collection;

    /**
     * Find a document by its ID.
     */
    public function findById(string $id): ?Document;

    /**
     * Get all documents belonging to a user.
     */
    public function findByUser(string $userId): Collection;

    /**
     * Get all documents by category.
     */
    public function findByCategory(string $category): Collection;

    /**
     * Create a new document.
     */
    public function create(array $data): Document;

    /**
     * Update an existing document.
     */
    public function update(string $id, array $data): Document;

    /**
     * Delete a document by its ID.
     */
    public function delete(string $id): bool;
}
