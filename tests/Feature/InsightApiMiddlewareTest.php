<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Rooberthh\InsightApi\Http\Middleware\InsightApiMiddleware;
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

test('captures GET request', function () {
    expect(InsightApiRequest::all())->toBeEmpty();

    $uri = '/api/users/1/posts/1';

    $this->getJson($uri);

    $requests = InsightApiRequest::all();

    expect($requests)->toHaveCount(1)
        ->and($requests->first()->method)->toBe('GET')
        ->and($requests->first()->route_pattern)->toBe($this->getRoute)
        ->and($requests->first()->status_code)->toBe(200);
});

test('captures POST request with body', function () {
    $this->postJson('/api/users/1/posts', [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);

    $requests = InsightApiRequest::all();

    expect($requests)->toHaveCount(1)
        ->and($requests->first()->method)->toBe('POST')
        ->and($requests->first()->status_code)->toBe(201)
        ->and($requests->first()->body)->toBe([
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
});
