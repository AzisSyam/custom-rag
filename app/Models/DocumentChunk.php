<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'content',
        'chunk_order',
        'metadata',
        'embedding',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'embedding' => 'array',
        ];
    }

    /**
     * Get the document that owns the chunk.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
