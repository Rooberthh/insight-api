<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsightApiPayload extends Model
{
    protected $table = 'insight_api_payloads';

    protected $fillable = [
        'request_id',
        'request_headers',
        'request_body',
        'response_headers',
        'response_body',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'request_headers' => 'array',
            'request_body' => 'array',
            'response_headers' => 'array',
            'response_body' => 'array',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(InsightApiRequest::class, 'request_id');
    }
}
