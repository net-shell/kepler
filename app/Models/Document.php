<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'path',
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

    /**
     * Get the folder path (without the filename)
     */
    public function getFolderPath(): string
    {
        $path = $this->path ?? '/';
        $lastSlash = strrpos($path, '/');

        if ($lastSlash === false || $lastSlash === 0) {
            return '/';
        }

        return substr($path, 0, $lastSlash);
    }

    /**
     * Get the filename from the path
     */
    public function getFileName(): string
    {
        $path = $this->path ?? $this->title;
        $lastSlash = strrpos($path, '/');

        if ($lastSlash === false) {
            return $path;
        }

        return substr($path, $lastSlash + 1);
    }

    /**
     * Scope to filter by folder path
     */
    public function scopeInFolder($query, string $folder)
    {
        $folder = rtrim($folder, '/');
        if (empty($folder)) {
            $folder = '/';
        }

        return $query->where('path', 'like', $folder . '%');
    }

    /**
     * Scope to get only direct children of a folder (not recursive)
     */
    public function scopeDirectChildren($query, string $folder)
    {
        $folder = rtrim($folder, '/');
        if ($folder === '') {
            $folder = '/';
        }

        // Match paths like /folder/file but not /folder/subfolder/file
        $pattern = $folder === '/'
            ? '/^\/[^\/]+$/'
            : '/^' . preg_quote($folder, '/') . '\/[^\/]+$/';

        return $query->where('path', 'like', $folder === '/' ? '/%' : $folder . '/%')
            ->whereRaw('path REGEXP ?', [$pattern]);
    }
}
