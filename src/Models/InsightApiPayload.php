<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array $request_headers
 * @property array $request_body
 * @property array $response_headers
 * @property array $response_body
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

    /** @return BelongsTo<InsightApiRequest, InsightApiPayload> */
    public function request(): BelongsTo
    {
        return $this->belongsTo(InsightApiRequest::class, 'request_id');
    }
}
