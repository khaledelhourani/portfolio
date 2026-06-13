<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contact form submissions (Part 1 + Part 7 Messages Manager).
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->enum('status', ['unread', 'read', 'replied', 'archived'])->default('unread');
            $table->string('ip', 45)->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
