<?php

use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\Factories\UserFactory;
use Rooberthh\InsightApi\Actions\RecordRequest;
use Rooberthh\InsightApi\DataObjects\CreateApiRequest;
use Rooberthh\InsightApi\Models\InsightApiRequest;

it('creates InsightApiRequest from CreateApiRequest DTO', function () {
    expect(InsightApiRequest::query()->get())->toBeEmpty();

    $requestData = new CreateApiRequest(
        status: 200,
        method: 'GET',
        routePattern: '/api/users/{user}',
        uri: '/api/users/123',
        ipAddress: '127.0.0.1',
        responseTimeMs: 50.0,
        requestId: 'test-fingerprint-123',
        requestHeaders: ['accept' => ['application/json']],
        requestBody: ['filter' => 'active'],
        responseHeaders: ['content-type' => ['application/json']],
        responseBody: ['user' => ['id' => 123]],
        requestable: null,
    );

    $action = new RecordRequest();
    $apiRequest = $action->handle($requestData);

    expect($apiRequest->method)->toBe('GET')
        ->and($apiRequest->route_pattern)->toBe('/api/users/{user}')
        ->and($apiRequest->uri)->toBe('/api/users/123')
        ->and($apiRequest->ip_address)->toBe('127.0.0.1')
        ->and($apiRequest->status)->toBe(200)
        ->and($apiRequest->response_time_ms)->toBe(50.0)
        ->and($apiRequest->request_id)->toBe('test-fingerprint-123');
});

it('creates payload with headers and bodies from DTO', function () {
    $requestData = new CreateApiRequest(
        status: 201,
        method: 'POST',
        routePattern: '/api/posts',
        uri: '/api/posts',
        ipAddress: '192.168.1.1',
        responseTimeMs: 75.5,
        requestId: 'post-fingerprint-456',
        requestHeaders: ['accept' => ['application/json'], 'x-custom' => ['value']],
        requestBody: ['title' => 'New Post', 'content' => 'Content here'],
        responseHeaders: ['content-type' => ['application/json']],
        responseBody: ['created' => true, 'id' => 456],
        requestable: null,
    );

    $action = new RecordRequest();
    $apiRequest = $action->handle($requestData);

    expect($apiRequest->payload->request_headers)->toBe($requestData->requestHeaders)
        ->and($apiRequest->payload->request_body)->toBe($requestData->requestBody)
        ->and($apiRequest->payload->response_headers)->toBe($requestData->responseHeaders)
        ->and($apiRequest->payload->response_body)->toBe($requestData->responseBody);
});

it('can associate the request to an authenticated user model', function () {
    $user = UserFactory::new()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $requestData = new CreateApiRequest(
        status: 200,
        method: 'GET',
        routePattern: '/api/profile',
        uri: '/api/profile',
        ipAddress: '192.168.1.1',
        responseTimeMs: 45.2,
        requestId: 'auth-fingerprint-789',
        requestHeaders: ['accept' => ['application/json']],
        requestBody: [],
        responseHeaders: ['content-type' => ['application/json']],
        responseBody: ['user' => ['id' => $user->id]],
        requestable: $user,
    );

    $action = new RecordRequest();
    $apiRequest = $action->handle($requestData);

    expect($apiRequest->requestable_type)->toBe(User::class)
        ->and($apiRequest->requestable_id)->toBe($user->id)
        ->and($apiRequest->requestable)->toBeInstanceOf(User::class)
        ->and($apiRequest->requestable->id)->toBe($user->id)
        ->and($apiRequest->requestable->email)->toBe('test@example.com');
});
