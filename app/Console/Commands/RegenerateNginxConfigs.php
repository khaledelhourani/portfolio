<?php

namespace App\Console\Commands;

use App\Services\NginxConfigService;
use Illuminate\Console\Command;

/**
 * Rebuild every hosted project's nginx snippet from the database.
 *
 * Run at container boot (before nginx starts — see supervisord/entrypoint), so
 * the ephemeral Railway filesystem is repopulated with one *.conf per live
 * project. Also handy locally to refresh all snippets after a config change.
 */
class RegenerateNginxConfigs extends Command
{
    protected $signature = 'hosting:regenerate-nginx {--reload : Attempt `nginx -s reload` afterwards}';

    protected $description = 'Regenerate all hosted-project nginx config snippets from the database';

    public function handle(NginxConfigService $nginx): int
    {
        $this->info('Regenerating nginx snippets for hosted projects…');

        $count = $nginx->regenerateAll();

        $this->info("✓ Wrote {$count} config file(s) to {$nginx->configDir()}");

        if ($this->option('reload')) {
            $this->line('Reloading nginx…');
            $ok = $nginx->reload();
            $ok ? $this->info('✓ nginx reloaded') : $this->warn('nginx reload not available (configs are applied at next start).');
        }

        return self::SUCCESS;
    }
}
