<?php

namespace App\Services;

use App\Services\Contracts\EmbeddingServiceInterface;
use OpenAI;

class OpenAIEmbeddingService implements EmbeddingServiceInterface
{
    private $client;
    private string $model;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
        $this->model = config('services.openai.embedding_model');
    }

    /**
     * Generate embeddings for a batch of texts.
     */
    public function embedBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $texts,
        ]);

        $embeddings = [];
        foreach ($response->embeddings as $embedding) {
            $embeddings[] = $embedding->embedding;
        }

        return $embeddings;
    }

    /**
     * Generate embedding for a single text.
     */
    public function embed(string $text): array
    {
        $response = $this->client->embeddings()->create([
            'model' => $this->model,
            'input' => $text,
        ]);

        return $response->embeddings[0]->embedding;
    }
}
