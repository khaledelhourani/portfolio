<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blog comments by logged-in public members (Part 5).
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('approved')->default(false);
            $table->timestamps();

            $table->index(['blog_post_id', 'approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
