<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return response()->json([
            'documents' => $documents,
        ]);
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

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'document' => $document,
        ], 201);
    }

    /**
     * Display the specified document with its chunks.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $document = $this->documentService->getDocumentWithChunks($id);

        if (! $document || $document->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        return response()->json([
            'document' => $document,
        ]);
    }

    /**
     * Update the specified document's metadata.
     */
    public function update(UpdateDocumentRequest $request, int $id): JsonResponse
    {
        $document = $this->documentService->getDocumentWithChunks($id);

        if (! $document || $document->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $updated = $this->documentService->updateDocument($id, $request->validated());

        return response()->json([
            'message' => 'Document updated successfully.',
            'document' => $updated,
        ]);
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $document = $this->documentService->getDocumentWithChunks($id);

        if (! $document || $document->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $this->documentService->deleteDocument($id);

        return response()->json([
            'message' => 'Document deleted successfully.',
        ]);
    }
}
