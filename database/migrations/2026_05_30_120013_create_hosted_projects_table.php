<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Self-hosted projects engine registry (Part 6).
     */
    public function up(): void
    {
        Schema::create('hosted_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('php_version')->default('8.3');
            $table->enum('status', ['active', 'stopped', 'error', 'deploying'])->default('deploying');
            $table->unsignedBigInteger('disk_usage')->default(0);   // bytes
            $table->unsignedBigInteger('db_size')->default(0);      // bytes
            $table->unsignedInteger('file_count')->default(0);
            $table->string('db_name')->nullable();
            $table->string('db_user')->nullable();
            $table->string('custom_domain')->nullable();
            $table->timestamp('last_deployed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosted_projects');
    }
};
