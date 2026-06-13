<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Local (email + password) accounts have no social provider.
            $table->string('password')->nullable()->after('email');
            $table->rememberToken(); // enables "remember me" for the member guard
            $table->string('provider')->nullable()->change();
            $table->string('provider_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token']);
            $table->string('provider')->nullable(false)->change();
            $table->string('provider_id')->nullable(false)->change();
        });
    }
};
