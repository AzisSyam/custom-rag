<?php

namespace App\Services\Contracts;

interface EmbeddingServiceInterface
{
    /**
     * Generate embeddings for a batch of texts.
     *
     * @param array<int, string> $texts
     * @return array<int, array<int, float>>
     */
    public function embedBatch(array $texts): array;

    /**
     * Generate embedding for a single text.
     *
     * @param string $text
     * @return array<int, float>
     */
    public function embed(string $text): array;
}
