<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DataSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'config',
        'cache_ttl',
        'last_cached_at',
        'cached_data',
        'enabled',
        'description',
    ];

    protected $casts = [
        'config' => 'array',
        'cached_data' => 'array',
        'last_cached_at' => 'datetime',
        'enabled' => 'boolean',
    ];

    /**
     * Available data source types
     */
    const TYPE_DATABASE = 'database';
    const TYPE_URL = 'url';
    const TYPE_API = 'api';

    /**
     * Available authentication types for API sources
     */
    const AUTH_NONE = 'none';
    const AUTH_BEARER = 'bearer';
    const AUTH_API_KEY = 'api_key';
    const AUTH_BASIC = 'basic';
    const AUTH_OAUTH2 = 'oauth2';

    /**
     * Check if cache is still valid
     */
    public function isCacheValid(): bool
    {
        if (!$this->last_cached_at || !$this->cached_data) {
            return false;
        }

        $expiresAt = $this->last_cached_at->addSeconds($this->cache_ttl);
        return now()->lt($expiresAt);
    }

    /**
     * Get cache expiry time
     */
    public function cacheExpiresAt(): ?\Carbon\Carbon
    {
        if (!$this->last_cached_at) {
            return null;
        }

        return $this->last_cached_at->addSeconds($this->cache_ttl);
    }

    /**
     * Scope to get only enabled sources
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope to get sources that need caching
     */
    public function scopeNeedsCaching($query)
    {
        return $query->enabled()
            ->where(function ($q) {
                $q->whereNull('last_cached_at')
                    ->orWhereRaw('last_cached_at + INTERVAL cache_ttl SECOND < NOW()');
            });
    }
}
