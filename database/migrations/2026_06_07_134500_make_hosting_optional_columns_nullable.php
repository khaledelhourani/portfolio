<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * php_version and entry_point are optional: static / Node projects have no PHP
 * version, and the deploy job derives entry_point during type detection. They
 * were created NOT NULL, so Laravel's ConvertEmptyStringsToNull middleware made
 * empty create-form fields blow up with a 1048 constraint violation. Relax both
 * to nullable (keeping their defaults for rows that do supply a value).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hosted_projects', function (Blueprint $table) {
            $table->string('php_version')->nullable()->default('8.3')->change();
            $table->string('entry_point')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('hosted_projects', function (Blueprint $table) {
            $table->string('php_version')->default('8.3')->nullable(false)->change();
            $table->string('entry_point')->default('index.php')->nullable(false)->change();
        });
    }
};
