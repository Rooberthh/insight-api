<?php

use Illuminate\Http\JsonResponse;
use Rooberthh\InsightApi\Actions\RecordRequest;
use Rooberthh\InsightApi\Exceptions\CannotRecordRequestException;
use Rooberthh\InsightApi\Models\InsightApiRequest;

it('can capture a json request', function () {
    Route::get('/api/users/{user}', fn() => null)->name('test.route');

    $request = Request::create('/api/users/123', 'GET');
    $request->headers->set('Accept', 'application/json');
    $request->setRouteResolver(fn() => app('router')->getRoutes()->match($request));

    expect(InsightApiRequest::query()->get())->toBeEmpty();

    $response = new JsonResponse(['user' => []], 200);

    $action = new RecordRequest();
    $apiRequest = $action->handle($request, $response, 50.0);

    expect($apiRequest->method)->toBe('GET')
        ->and($apiRequest->route_pattern)->toBe('/api/users/{user}')
        ->and($apiRequest->status_code)->toBe(200)
        ->and($apiRequest->payload->request_headers)->toBe($request->headers->all())
        ->and($apiRequest->payload->request_body)->toBe($request->all())
        ->and($apiRequest->payload->response_headers)->toBe($response->headers->all())
        ->and($apiRequest->payload->response_body)->toBe(json_decode(json_encode(['user' => []]), true));
});

it('can throws an exception if the request is not expection json', function () {
    Route::get('/api/users/{user}', fn() => null)->name('test.route');

    $request = Request::create('/api/users/123', 'GET');
    $request->setRouteResolver(fn() => app('router')->getRoutes()->match($request));

    expect(InsightApiRequest::query()->get())->toBeEmpty();

    $response = new JsonResponse(['user' => []], 200);

    $action = new RecordRequest();
    $action->handle($request, $response, 50.0);
})->throws(CannotRecordRequestException::class);
