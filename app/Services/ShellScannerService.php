<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Security scanner for uploaded project archives.
 *
 * Tiered on purpose: WordPress / Laravel / CodeIgniter legitimately call
 * base64_decode(), exec(), system() in their own core, so flagging those bare
 * would reject every real project. Instead we BLOCK only high-confidence
 * web-shell signatures (obfuscated eval/assert pipelines, superglobals piped
 * into shell calls) and known shell filenames, and merely RECORD softer
 * signals as warnings for the admin to review.
 *
 * A blocked archive is quarantined (kept, not deleted) for inspection.
 */
class ShellScannerService
{
    /** Obfuscation / injection pipelines — whitespace-stripped, lowercased. */
    private const MALICIOUS = [
        'eval(base64_decode', 'eval(gzinflate', 'eval(gzuncompress', 'eval(str_rot13',
        'eval(gzdecode', 'eval($_', 'eval(stripslashes($_', 'eval(html_entity_decode',
        'assert($_', 'assert(base64_decode', 'create_function(',
        'shell_exec($_', 'system($_', 'passthru($_', 'exec($_', 'popen($_', 'proc_open($_',
        'base64_decode($_', 'gzinflate(base64_decode', 'gzuncompress(base64_decode',
        'move_uploaded_file($_files', 'file_put_contents($_', 'fwrite($_',
        'preg_replace("/.*/e', "preg_replace('/.*/e", '/e",$_', "/e',\$_",
    ];

    /** Signature strings found inside well-known web shells. */
    private const SHELL_TOKENS = [
        'c99shell', 'r57shell', 'b374k', 'weevely', 'filesman', 'wsoshell', 'wso2.',
        'phpspy', 'indoxploit', 'egyspider', 'mister spy', 'priv8', 'antichat',
        'safe_mode bypass', 'symlink bypass', 'pwnshell', 'k2ll33d',
    ];

    /** Filenames that are almost always malicious. */
    private const SUSPICIOUS_FILENAMES = [
        '/^c99\.php$/i', '/^r57\.php$/i', '/^wso\.php$/i', '/^b374k.*\.php$/i',
        '/(^|[._-])shell\.php$/i', '/web[._-]?shell/i', '/backdoor/i',
        '/^cmd\.php$/i', '/^adminer\.php$/i', '/^upload(er)?\.php$/i', '/^bypass/i',
    ];

    /** Soft signals: recorded as warnings, never auto-reject. */
    private const SOFT = [
        'shell_exec(', 'system(', 'passthru(', 'exec(', 'popen(', 'proc_open(',
        'base64_decode(', 'eval(', 'assert(', 'fsockopen(', 'pcntl_exec(',
    ];

    private const PHP_EXT = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar', 'inc'];

    /**
     * Scan a ZIP without fully extracting it.
     *
     * @return array{clean:bool, threats:array<int,array{file:string,reason:string}>, warnings:array<int,array{file:string,reason:string}>, files_scanned:int}
     */
    public function scanZip(string $zipPath): array
    {
        $threats = [];
        $warnings = [];
        $scanned = 0;

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return $this->result([['file' => basename($zipPath), 'reason' => 'تعذّر فتح الأرشيف']], [], 0);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);

            // Path traversal / absolute paths → always a threat.
            if (str_contains($name, '..') || str_starts_with($name, '/') || preg_match('/^[a-z]:/i', $name)) {
                $threats[] = ['file' => $name, 'reason' => 'مسار غير آمن (path traversal)'];

                continue;
            }

            if ($this->filenameLooksMalicious($name)) {
                $threats[] = ['file' => $name, 'reason' => 'اسم ملف يطابق web-shell معروف'];
            }

            if (! $this->isPhpFile($name)) {
                continue;
            }

            $scanned++;
            $contents = $zip->getFromIndex($i);
            if ($contents === false) {
                continue;
            }

            $hit = $this->inspect($contents);
            foreach ($hit['threats'] as $reason) {
                $threats[] = ['file' => $name, 'reason' => $reason];
            }
            foreach ($hit['warnings'] as $reason) {
                $warnings[] = ['file' => $name, 'reason' => $reason];
            }
        }
        $zip->close();

        return $this->result($threats, $warnings, $scanned);
    }

    /**
     * Defense-in-depth: re-scan files already extracted to disk.
     *
     * @return array{clean:bool, threats:array, warnings:array, files_scanned:int}
     */
    public function scanDirectory(string $dir): array
    {
        $threats = [];
        $warnings = [];
        $scanned = 0;

        if (! is_dir($dir)) {
            return $this->result([], [], 0);
        }

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $name = $file->getFilename();

            if ($this->filenameLooksMalicious($name)) {
                $threats[] = ['file' => $name, 'reason' => 'اسم ملف يطابق web-shell معروف'];
            }
            if (! $this->isPhpFile($name) || $file->getSize() > 4_000_000) {
                continue;
            }

            $scanned++;
            $contents = @file_get_contents($file->getPathname());
            if ($contents === false) {
                continue;
            }
            $hit = $this->inspect($contents);
            foreach ($hit['threats'] as $reason) {
                $threats[] = ['file' => $name, 'reason' => $reason];
            }
            foreach ($hit['warnings'] as $reason) {
                $warnings[] = ['file' => $name, 'reason' => $reason];
            }
        }

        return $this->result($threats, $warnings, $scanned);
    }

    /**
     * Move a rejected archive into quarantine for later review (never deleted).
     *
     * @return string the quarantine path
     */
    public function quarantine(string $sourcePath, string $slug): string
    {
        $dir = config('hosting.paths.quarantine', storage_path('app/quarantine'));
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $dest = $dir . DIRECTORY_SEPARATOR . Str::slug($slug) . '-' . now()->format('Ymd-His') . '-' . Str::random(6) . '.zip';
        @copy($sourcePath, $dest);

        return $dest;
    }

    /** Append a structured line to the hosting scan log. */
    public function logScan(string $slug, array $result, ?string $quarantinedAt = null): void
    {
        Log::channel(config('logging.default'))->info('[hosting-scan] ' . $slug, [
            'clean' => $result['clean'],
            'threats' => $result['threats'],
            'warnings' => count($result['warnings']),
            'files_scanned' => $result['files_scanned'],
            'quarantined' => $quarantinedAt,
        ]);
    }

    /* ------------------------------------------------------------- internals */

    /** @return array{threats:array<int,string>, warnings:array<int,string>} */
    private function inspect(string $contents): array
    {
        $haystack = strtolower(preg_replace('/\s+/', '', $contents) ?? '');
        $loose = strtolower($contents);
        $threats = [];
        $warnings = [];

        foreach (self::MALICIOUS as $needle) {
            if (str_contains($haystack, $needle)) {
                $threats[] = "نمط خبيث: {$needle}";
            }
        }
        foreach (self::SHELL_TOKENS as $token) {
            if (str_contains($loose, $token)) {
                $threats[] = "توقيع web-shell: {$token}";
            }
        }
        // Only record soft signals when nothing was already blocked (less noise).
        if (empty($threats)) {
            foreach (self::SOFT as $needle) {
                if (str_contains($haystack, $needle)) {
                    $warnings[] = "دالة حساسة: {$needle}";
                }
            }
        }

        return ['threats' => array_values(array_unique($threats)), 'warnings' => array_values(array_unique($warnings))];
    }

    private function isPhpFile(string $name): bool
    {
        return in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::PHP_EXT, true);
    }

    private function filenameLooksMalicious(string $name): bool
    {
        $base = basename($name);
        foreach (self::SUSPICIOUS_FILENAMES as $pattern) {
            if (preg_match($pattern, $base)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{clean:bool, threats:array, warnings:array, files_scanned:int} */
    private function result(array $threats, array $warnings, int $scanned): array
    {
        return [
            'clean' => count($threats) === 0,
            'threats' => $threats,
            'warnings' => $warnings,
            'files_scanned' => $scanned,
        ];
    }
}
