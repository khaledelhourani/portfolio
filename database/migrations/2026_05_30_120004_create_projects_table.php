<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Portfolio projects (Part 1 grid + Part 7 manager).
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('slug')->unique();
            $table->string('type')->nullable();          // overlay badge label
            $table->string('duration')->nullable();       // duration badge
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->json('tech_stack')->nullable();       // ["Laravel", "Vue", ...]
            $table->string('github_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('thumbnail')->nullable();
            // Expandable detail blocks
            $table->text('core_focus')->nullable();
            $table->text('architecture')->nullable();
            $table->text('mitigation')->nullable();
            $table->boolean('featured')->default(false);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
