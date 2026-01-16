<?php

declare(strict_types=1);

namespace Rooberthh\InsightApi\Services;

final class RedactionService
{
    private const REDACTED = '[REDACTED]';

    /**
     * @param  array<string>  $headerKeys
     * @param  array<string>  $bodyFields
     */
    public function __construct(
        private readonly array $headerKeys = [],
        private readonly array $bodyFields = [],
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            headerKeys: config('insight-api.redaction.headers', []),
            bodyFields: config('insight-api.redaction.body_fields', []),
        );
    }

    /**
     * @param  array<string, mixed>  $headers
     * @return array<string, mixed>
     */
    public function redactHeaders(array $headers): array
    {
        $redacted = [];
        $lowercaseKeys = array_map('strtolower', $this->headerKeys);

        foreach ($headers as $key => $value) {
            $normalizedKey = strtolower($key);

            if (in_array($normalizedKey, $lowercaseKeys, true)) {
                $redacted[$key] = self::REDACTED;
            } else {
                $redacted[$key] = $value;
            }
        }

        return $redacted;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function redactBody(array $body): array
    {
        return $this->redactBodyRecursive($body);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function redactBodyRecursive(array $data): array
    {
        $redacted = [];
        $lowercaseFields = array_map('strtolower', $this->bodyFields);

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, $lowercaseFields, true)) {
                $redacted[$key] = self::REDACTED;
            } elseif (is_array($value)) {
                $redacted[$key] = $this->redactBodyRecursive($value);
            } else {
                $redacted[$key] = $value;
            }
        }

        return $redacted;
    }
}
