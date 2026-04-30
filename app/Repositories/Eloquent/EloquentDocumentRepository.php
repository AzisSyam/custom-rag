<?php

namespace App\Repositories\Eloquent;

use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentDocumentRepository implements DocumentRepositoryInterface
{
    public function __construct(
        private Document $model,
    ) {}

    /**
     * Get all documents.
     */
    public function all(): Collection
    {
        return $this->model->newQuery()->latest()->get();
    }

    /**
     * Find a document by its ID.
     */
    public function findById(string $id): ?Document
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * Get all documents belonging to a user.
     */
    public function findByUser(string $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Get all documents by category.
     */
    public function findByCategory(string $category): Collection
    {
        return $this->model->newQuery()
            ->where('category', $category)
            ->latest()
            ->get();
    }

    /**
     * Create a new document.
     */
    public function create(array $data): Document
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * Update an existing document.
     */
    public function update(string $id, array $data): Document
    {
        $document = $this->model->newQuery()->findOrFail($id);
        $document->update($data);

        return $document->fresh();
    }

    /**
     * Delete a document by its ID.
     */
    public function delete(string $id): bool
    {
        $document = $this->model->newQuery()->findOrFail($id);

        return $document->delete();
    }
}
