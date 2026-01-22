<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Rooberthh\InsightApi\DataObjects\CreateApiRequest;
use Rooberthh\InsightApi\Http\Middleware\InsightApiMiddleware;
use Rooberthh\InsightApi\Jobs\StoreApiRequestJob;

beforeEach(function () {
    Queue::fake();

    Route::middleware(InsightApiMiddleware::class)->group(function () {
        $getRoute = '/api/users/{user}/posts/{post}';
        $postRoute = '/api/users/{user}/posts/';

        Route::get($getRoute, fn() => response()->json(['users' => []]));
        Route::post($postRoute, fn() => response()->json(['created' => true], 201));

        $this->getRoute = $getRoute;
        $this->postRoute = $postRoute;
    });
});

it('does not dispatch job when sampling rate is set to 0', function () {
    config()->set('insight-api.sampling.rate', 0.0);

    $uri = '/api/users/1/posts/1';

    $this->getJson($uri);

    Queue::assertNothingPushed();
});

it('dispatches job for GET request with correct data', function () {
    $uri = '/api/users/1/posts/1';

    $this->getJson($uri);

    Queue::assertPushed(StoreApiRequestJob::class, function (StoreApiRequestJob $job) use ($uri) {
        $data = $job->request;

        return ($data instanceof CreateApiRequest)
            && ($data->method === 'GET')
            && $data->routePattern === $this->getRoute
            && $data->uri === $uri
            && $data->status === 200
            && $job->request->responseBody === ['users' => []];
    });
});

it('dispatches job for POST request with request body', function () {
    $requestBody = [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ];

    $this->postJson('/api/users/1/posts', $requestBody, ['X-Custom-Header' => 'test-value']);

    Queue::assertPushed(StoreApiRequestJob::class, function (StoreApiRequestJob $job) use ($requestBody) {
        $data = $job->request;

        return $data->method === 'POST'
            && $data->status === 201
            && $data->requestBody === $requestBody
            && isset($data->requestHeaders['x-custom-header']);
    });
});

it('does not dispatch job for non-JSON requests', function () {
    Route::get('/web/page', fn() => response('HTML content'))->middleware(InsightApiMiddleware::class);

    $this->get('/web/page');

    Queue::assertNothingPushed();
});
