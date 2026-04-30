<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class Document extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'file_path',
    ];

    /**
     * Get the user that owns the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chunks for the document.
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class)->orderBy('chunk_order');
    }
}
