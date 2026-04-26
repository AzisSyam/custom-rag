<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService,
    ) {}

    /**
     * Display a listing of the user's documents.
     */
    public function index(Request $request): Response
    {
        $documents = $this->documentService->getDocumentsByUser(
            $request->user()->id
        );

        return Inertia::render('documents/index', [
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
            'message' => __('Document uploaded successfully.'),
            'document' => $document,
        ], 201);
    }

    /**
     * Display the specified document with its chunks.
     */
    public function show(Request $request, int $id): Response|JsonResponse
    {
        $document = $this->documentService->getDocumentWithChunks($id);

        if (! $document || $document->user_id !== $request->user()->id) {
            abort(404);
        }

        return Inertia::render('documents/show', [
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
            abort(404);
        }

        $updated = $this->documentService->updateDocument($id, $request->validated());

        return response()->json([
            'message' => __('Document updated successfully.'),
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
            abort(404);
        }

        $this->documentService->deleteDocument($id);

        return response()->json([
            'message' => __('Document deleted successfully.'),
        ]);
    }
}
