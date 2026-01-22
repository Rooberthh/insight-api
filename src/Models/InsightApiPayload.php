<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array<mixed, mixed>            $request_headers
 * @property array<mixed, mixed>             $request_body
 * @property array<mixed, mixed>             $response_headers
 * @property array<mixed, mixed>             $response_body
 * @property InsightApiRequest $request
 */
class InsightApiPayload extends Model
{
    protected $table = 'insight_api_payloads';

    protected $fillable = [
        'request_headers',
        'request_body',
        'response_headers',
        'response_body',
    ];

    protected function casts(): array
    {
        return [
            'request_headers' => 'array',
            'request_body' => 'array',
            'response_headers' => 'array',
            'response_body' => 'array',
        ];
    }

    /** @return BelongsTo<InsightApiRequest, $this> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(InsightApiRequest::class, 'request_id');
    }
}
