<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-row owner profile powering the Hero/About sections (Part 1).
     * Editable from the Profile Manager (Part 7).
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->string('role_ar')->nullable();
            $table->string('role_en')->nullable();
            $table->text('bio_ar')->nullable();
            $table->text('bio_en')->nullable();
            $table->string('credential_badge_ar')->nullable();
            $table->string('credential_badge_en')->nullable();
            $table->string('photo')->nullable();
            $table->string('cv_pdf')->nullable();
            $table->string('city')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('social_links')->nullable();   // {github, instagram, facebook, linkedin, ...}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
