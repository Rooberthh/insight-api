<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insight_api_requests', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('route_pattern');
            $table->string('uri');
            $table->string('ip_address', 45);
            $table->unsignedSmallInteger('status_code');
            $table->float('response_time_ms');
            $table->timestamp('captured_at');

            $table->index(['method', 'route_pattern']);
            $table->index('captured_at');
            $table->index('status_code');
        });

        Schema::create('insight_api_payloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('insight_api_requests')->cascadeOnDelete();

            // Request payload
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();

            // Response payload
            $table->json('response_headers')->nullable();
            $table->json('response_body')->nullable();

            $table->index('request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_api_payloads');
        Schema::dropIfExists('insight_api_requests');
    }
};
