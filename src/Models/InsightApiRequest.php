<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Models;

use Illuminate\Database\Eloquent\Model;

class InsightApiRequest extends Model
{
    protected $table = 'insight_api_requests';

    protected $fillable = [
        'method',
        'route_pattern',
        'uri',
        'headers',
        'body',
        'ip_address',
        'status_code',
        'response_time_ms',
        'captured_at',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'body' => 'array',
            'response_time_ms' => 'float',
            'captured_at' => 'datetime',
        ];
    }

    public function endpoint(): string
    {
        return "{$this->method} {$this->route_pattern}";
    }

    public function scopeByEndpoint($query, string $method, string $routePattern)
    {
        return $query->where('method', $method)->where('route_pattern', $routePattern);
    }
}
