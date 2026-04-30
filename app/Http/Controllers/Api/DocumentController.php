<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService,
    ) {}

    /**
     * Display a listing of the user's documents.
     */
    public function index(Request $request): JsonResponse
    {
        $documents = $this->documentService->getDocumentsByUser(
            $request->user()->id
        );

        return $this->sendResponse([
            'documents' => $documents,
        ], 'Documents retrieved successfully.');
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $document = $this->documentService->storeDocument(
            $request->user()->id,
            $request->validated(),
            $request->file('file'),
        );

        return $this->sendResponse([
            'document' => $document,
        ], 'Document uploaded successfully.', 201);
    }

    /**
     * Display the specified document with its chunks.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $document = $this->documentService->getDocumentWithChunks($id);

        if (! $document || $document->user_id !== $request->user()->id) {
            return $this->sendError('Document not found.', 404);
        }

        return $this->sendResponse([
            'document' => $document,
        ], 'Document details retrieved.');
    }

    /**
     * View the specified document file natively in the browser.
     */
    public function viewFile(Request $request, string $id)
    {
        $document = $this->documentService->getDocumentWithChunks($id);

        if (! $document || $document->user_id !== $request->user()->id) {
            return $this->sendError('Document not found.', 404);
        }

        if (! $document->file_path || ! Storage::disk('public')->exists($document->file_path)) {
            return $this->sendError('File not found on server.', 404);
        }

        return Storage::disk('public')->response($document->file_path);
    }

    /**
     * Update the specified document's metadata.
     */
    public function update(UpdateDocumentRequest $request, string $id): JsonResponse
    {
        $document = $this->documentService->getDocumentWithChunks($id);

        if (! $document || $document->user_id !== $request->user()->id) {
            return $this->sendError('Document not found.', 404);
        }

        $updated = $this->documentService->updateDocument(
            $id, 
            $request->validated(),
            $request->file('file')
        );

        return $this->sendResponse([
            'document' => $updated,
        ], 'Document updated successfully.');
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $document = $this->documentService->getDocumentWithChunks($id);

        if (! $document || $document->user_id !== $request->user()->id) {
            return $this->sendError('Document not found.', 404);
        }

        $this->documentService->deleteDocument($id);

        return $this->sendResponse(null, 'Document deleted successfully.');
    }
}
