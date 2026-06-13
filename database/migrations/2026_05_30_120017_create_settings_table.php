<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Key-value settings store (Part 7 Settings page).
     * Sensitive values (API keys, OAuth secrets) are encrypted at the model layer
     * when `is_encrypted` is true.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('string');   // string|bool|json|text
            $table->string('group')->default('general');  // general|seo|social|mail|ai|oauth
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
