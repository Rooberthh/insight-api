<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Rooberthh\InsightApi\Http\Middleware\InsightApiMiddleware;
use Rooberthh\InsightApi\Models\InsightApiPayload;
use Rooberthh\InsightApi\Models\InsightApiRequest;

beforeEach(function () {
    Route::middleware(InsightApiMiddleware::class)->group(function () {
        $getRoute = '/api/users/{user}/posts/{post}';
        $postRoute = '/api/users/{user}/posts/';

        Route::get($getRoute, fn() => response()->json(['users' => []]));
        Route::post($postRoute, fn() => response()->json(['created' => true], 201));

        $this->getRoute = $getRoute;
        $this->postRoute = $postRoute;
    });
});

it('captures a GET request', function () {
    expect(InsightApiRequest::all())->toBeEmpty();

    $uri = '/api/users/1/posts/1';

    $this->getJson($uri);

    $requests = InsightApiRequest::all();

    expect($requests)->toHaveCount(1)
        ->and($requests->first()->method)->toBe('GET')
        ->and($requests->first()->route_pattern)->toBe($this->getRoute)
        ->and($requests->first()->status_code)->toBe(200);
});

it('does not capture a get request is sampling rate is set to 0', function () {
    config()->set('insight-api.sampling.rate', 0.0);

    expect(InsightApiRequest::all())->toBeEmpty();

    $uri = '/api/users/1/posts/1';

    $this->getJson($uri);

    $requests = InsightApiRequest::all();

    expect($requests)->toHaveCount(0);
});

it('captures a POST request with body', function () {
    $this->postJson('/api/users/1/posts', [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);

    $requests = InsightApiRequest::with('payload')->get();

    expect($requests)->toHaveCount(1)
        ->and($requests->first()->method)->toBe('POST')
        ->and($requests->first()->status_code)->toBe(201)
        ->and($requests->first()->payload->request_body)->toBe([
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
});

it('captures response body in payload', function () {
    $this->getJson('/api/users/1/posts/1');

    $requests = InsightApiRequest::with('payload')->get();

    expect($requests)->toHaveCount(1)
        ->and($requests->first()->payload)->toBeInstanceOf(InsightApiPayload::class)
        ->and($requests->first()->payload->response_body)->toBe(['users' => []]);
});

it('creates payload with request and response headers', function () {
    $this->getJson('/api/users/1/posts/1', ['X-Custom-Header' => 'test-value']);

    $request = InsightApiRequest::with('payload')->first();

    expect($request->payload->request_headers)->toHaveKey('x-custom-header')
        ->and($request->payload->response_headers)->toHaveKey('content-type');
});
