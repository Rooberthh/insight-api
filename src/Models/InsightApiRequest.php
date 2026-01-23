<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $request_id
 * @property Model|null $requestable
 * @property string $method
 * @property string $route_pattern
 * @property string $uri
 * @property string $ip_address
 * @property int $status
 * @property float $response_time_ms
 * @property Carbon $captured_at
 * @property InsightApiPayload|null $payload
 */
class InsightApiRequest extends Model
{
    protected $table = 'insight_api_requests';

    protected $fillable = [
        'request_id',
        'method',
        'route_pattern',
        'uri',
        'ip_address',
        'status',
        'response_time_ms',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'response_time_ms' => 'float',
            'captured_at' => 'datetime',
        ];
    }

    /** @return null|MorphTo<Model, $this> */
    public function requestable(): ?MorphTo
    {
        return $this->morphTo('requestable');
    }

    /** @return HasOne<InsightApiPayload, $this> */
    public function payload(): HasOne
    {
        return $this->hasOne(InsightApiPayload::class, 'request_id');
    }
}
