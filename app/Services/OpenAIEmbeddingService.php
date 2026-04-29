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
        $this->model = config('services.openai.embedding_model');
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
     * Generate embeddings for a batch of texts.
     */
    public function embedBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $response = $this->getClient()->embeddings()->create([
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
        $response = $this->getClient()->embeddings()->create([
            'model' => $this->model,
            'input' => $text,
        ]);

        return $response->embeddings[0]->embedding;
    }
}
