<?php

namespace App\Services;

use App\Repositories\Contracts\DocumentChunkRepositoryInterface;
use App\Services\Contracts\EmbeddingServiceInterface;
use OpenAI;

class ChatService
{
    private $client;
    private string $chatModel;

    public function __construct(
        private EmbeddingServiceInterface $embeddingService,
        private DocumentChunkRepositoryInterface $chunkRepo,
    ) {
        $this->chatModel = config('services.openai.chat_model');
    }

    /**
     * Get the OpenAI client instance (Lazy initialization).
     */
    private function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $apiKey = config('services.openai.api_key');

        if (! $apiKey) {
            throw new \Exception('OpenAI API key is not configured. Please add OPENAI_API_KEY to your .env file.');
        }

        $this->client = OpenAI::client($apiKey);

        return $this->client;
    }

    /**
     * Bertanya kepada AI berdasarkan konteks dokumen yang ada di database.
     *
     * @param string $question Pertanyaan dari user.
     * @param int $userId ID User yang bertanya (untuk filter dokumen).
     * @return string Jawaban dari AI.
     */
    public function askQuestion(string $question, int $userId): string
    {
        // 1. Ubah pertanyaan user menjadi vektor angka
        $questionVector = $this->embeddingService->embed($question);

        // 2. Cari 5 potongan teks paling mirip di database, difilter berdasarkan milik user
        $relevantChunks = $this->chunkRepo->similaritySearch($questionVector, $userId, 5);

        // Jika tidak ada data dokumen sama sekali
        if ($relevantChunks->isEmpty()) {
            return "Maaf, saya tidak menemukan dokumen apapun di database untuk menjawab pertanyaan Anda.";
        }

        // 3. Gabungkan potongan-potongan teks menjadi satu string (Context)
        $context = $relevantChunks->map(function ($chunk) {
            return $chunk->content;
        })->implode("\n\n---\n\n");

        // 4. Buat prompt instruksi untuk dikirim ke ChatGPT
        $systemPrompt = "Anda adalah asisten AI yang pintar dan ramah. Tugas Anda adalah menjawab pertanyaan pengguna HANYA berdasarkan konteks yang diberikan. "
                      . "Jika jawabannya tidak ada di dalam konteks, katakan dengan jujur bahwa Anda tidak tahu berdasarkan dokumen yang ada.\n\n"
                      . "KONTEKS:\n" . $context;

        // 5. Kirim ke OpenAI
        $response = $this->getClient()->chat()->create([
            'model' => $this->chatModel,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $question],
            ],
            // 'temperature' => 0.3, // Opsional: atur agar jawaban lebih faktual (tidak terlalu halu)
        ]);

        return $response->choices[0]->message->content;
    }
}
