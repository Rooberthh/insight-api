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
            $table->json('headers')->nullable();
            $table->json('body')->nullable();
            $table->string('ip_address', 45);
            $table->unsignedSmallInteger('status_code');
            $table->float('response_time_ms');
            $table->timestamp('captured_at');

            $table->index(['method', 'route_pattern']);
            $table->index('captured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_api_requests');
    }
};
