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
            $table->string('request_id')->unique();
            $table->unsignedInteger('status');
            $table->string('method');
            $table->string('route_pattern');
            $table->string('uri');
            $table->string('ip_address');
            $table->text('token')->nullable();
            $table->float('response_time_ms')->default(0);
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['method', 'route_pattern']);
        });

        Schema::create('insight_api_payloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('insight_api_requests')->cascadeOnDelete();
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->json('response_headers')->nullable();
            $table->json('response_body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_api_payloads');
        Schema::dropIfExists('insight_api_requests');
    }
};
