<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-request visitor tracking log (Part 3).
     */
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->index();
            $table->text('user_agent')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('device')->nullable();          // desktop | mobile | tablet
            $table->string('country')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('page_url');
            $table->string('referrer')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->timestamp('read_at')->nullable();       // dashboard notification read state
            $table->timestamp('visited_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
