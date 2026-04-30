<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {}

    /**
     * Menerima pertanyaan dari user dan mengembalikan jawaban dari AI.
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'question' => ['required', 'string', 'min:3'],
        ]);

        try {
            $answer = $this->chatService->askQuestion(
                $request->question,
                $request->user()->id
            );

            return $this->sendResponse([
                'answer' => $answer,
            ], 'AI answer retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Terjadi kesalahan saat memproses pertanyaan.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
