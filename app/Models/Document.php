<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'tags',
        'metadata',
        'tfidf_score'
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'tfidf_score' => 'float',
    ];

    /**
     * Get the searchable text for this document.
     */
    public function getSearchableText(): string
    {
        $parts = [
            $this->title,
            $this->body,
        ];

        if ($this->tags) {
            $parts[] = implode(' ', $this->tags);
        }

        if ($this->metadata) {
            $parts[] = json_encode($this->metadata);
        }

        return implode(' ', $parts);
    }
}
