<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Evolve the Part-6 hosted_projects registry into the full Multi-Project
 * Hosting Engine schema. Purely additive + backfill — no data is dropped, so
 * existing rows (and the rest of the CMS) survive. The legacy `name`,
 * `disk_usage`, `db_size`, `status('active'…)` columns are kept and mirrored
 * into the new bilingual / *_mb / processing fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hosted_projects', function (Blueprint $table) {
            // Bilingual identity (legacy `name`/`description` stay as the source).
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
            $table->text('description_ar')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_ar');

            // Engine fields.
            $table->string('type')->default('static')->after('slug');        // php|laravel|wordpress|static|nodejs
            $table->string('entry_point')->default('index.php')->after('type');
            $table->boolean('has_database')->default(false)->after('entry_point');
            $table->text('db_password')->nullable()->after('db_user');        // encrypted cast
            $table->longText('env_vars')->nullable()->after('db_password');   // encrypted JSON cast
            $table->unsignedBigInteger('disk_usage_mb')->default(0)->after('env_vars');
            $table->unsignedBigInteger('db_size_mb')->default(0)->after('disk_usage_mb');
            $table->string('zip_path')->nullable()->after('db_size_mb');
            $table->string('webroot_path')->nullable()->after('zip_path');
            $table->string('nginx_config_path')->nullable()->after('webroot_path');
            $table->string('thumbnail')->nullable()->after('nginx_config_path');

            // Async processing lifecycle (separate from the public `status`).
            $table->string('processing_status')->default('pending')->after('thumbnail'); // pending|processing|completed|failed
            $table->unsignedTinyInteger('processing_step')->default(0)->after('processing_status'); // 0..7
            $table->longText('processing_log')->nullable()->after('processing_step');
        });

        // Backfill new columns from the legacy ones so existing rows stay valid.
        foreach (DB::table('hosted_projects')->get() as $row) {
            DB::table('hosted_projects')->where('id', $row->id)->update([
                'name_ar' => $row->name_ar ?? $row->name,
                'name_en' => $row->name_en ?? $row->name,
                'description_ar' => $row->description_ar ?? $row->description,
                'has_database' => ! empty($row->db_name),
                'disk_usage_mb' => (int) round(($row->disk_usage ?? 0) / 1048576),
                'db_size_mb' => (int) round(($row->db_size ?? 0) / 1048576),
                'webroot_path' => 'storage/app/hosted/' . $row->slug,
                'processing_status' => in_array($row->status, ['active', 'stopped', 'error'], true)
                    ? ($row->status === 'error' ? 'failed' : 'completed')
                    : 'pending',
            ]);
        }

        // Re-map the public status vocabulary to active|maintenance|disabled.
        // (Stored as a plain string to avoid brittle enum ALTERs across MySQL/MariaDB.)
        Schema::table('hosted_projects', function (Blueprint $table) {
            $table->string('status', 32)->default('active')->change();
        });
        DB::table('hosted_projects')->where('status', 'stopped')->update(['status' => 'disabled']);
        DB::table('hosted_projects')->where('status', 'error')->update(['status' => 'disabled']);
        DB::table('hosted_projects')->where('status', 'deploying')->update(['status' => 'maintenance']);
    }

    public function down(): void
    {
        Schema::table('hosted_projects', function (Blueprint $table) {
            $table->dropColumn([
                'name_ar', 'name_en', 'description_ar', 'description_en',
                'type', 'entry_point', 'has_database', 'db_password', 'env_vars',
                'disk_usage_mb', 'db_size_mb', 'zip_path', 'webroot_path',
                'nginx_config_path', 'thumbnail',
                'processing_status', 'processing_step', 'processing_log',
            ]);
        });
    }
};
