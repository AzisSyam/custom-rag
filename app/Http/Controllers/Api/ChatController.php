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

            return response()->json([
                'answer' => $answer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses pertanyaan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
