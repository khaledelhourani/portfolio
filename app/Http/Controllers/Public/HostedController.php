<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HostedProject;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public serving layer for hosted projects at /hosted/{slug}/{path}.
 *
 * Static assets are served directly here, so static/Node projects work
 * identically on local Apache, `artisan serve`, and the nginx container.
 *
 * PHP projects (php/laravel/wordpress) are executed through php-cgi as a small
 * FastCGI gateway — this is the dev/Apache path. On the Railway image nginx +
 * php-fpm intercept `location /hosted/{slug}/…\.php` (see NginxConfigService)
 * before the request ever reaches Laravel, so this controller only handles the
 * static fallback there.
 */
class HostedController extends Controller
{
    /** Extensions whose MIME finfo guesses wrongly (breaks browser rendering). */
    private const MIME_MAP = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'mjs' => 'application/javascript',
        'json' => 'application/json',
        'svg' => 'image/svg+xml',
        'html' => 'text/html',
        'htm' => 'text/html',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'map' => 'application/json',
        'wasm' => 'application/wasm',
    ];

    public function serve(Request $request, string $slug, string $path = ''): Response
    {
        $project = HostedProject::where('slug', $slug)->first();

        if (! $project) {
            abort(404);
        }
        if ($project->processing_status !== 'completed' || $project->isProcessing()) {
            return response()->view('hosted.processing', ['project' => $project], 202);
        }
        if ($project->status === 'disabled') {
            abort(404);
        }
        if ($project->status === 'maintenance') {
            return response()->view('hosted.maintenance', ['project' => $project], 503);
        }

        $docRoot = realpath($project->docRoot());
        if ($docRoot === false) {
            abort(404, 'Document root missing.');
        }

        // Resolve + jail the requested path inside the document root.
        $relative = $this->sanitize($path);
        $candidate = $relative === '' ? $docRoot : $docRoot . DIRECTORY_SEPARATOR . $relative;
        $real = realpath($candidate);
        if ($real !== false && ! $this->within($docRoot, $real)) {
            abort(403);
        }

        // Existing real file → serve or execute it directly.
        if ($real !== false && is_file($real)) {
            return $this->isExecutablePhp($project, $real)
                ? $this->executePhp($request, $project, $docRoot, $real, '')
                : $this->serveStatic($request, $real);
        }

        // Existing directory (or root) → look for the index/entry file.
        if ($real !== false && is_dir($real)) {
            $index = $real . DIRECTORY_SEPARATOR . ($project->entry_point ?: ($project->isPhpRuntime() ? 'index.php' : 'index.html'));
            if (is_file($index)) {
                return $this->isExecutablePhp($project, $index)
                    ? $this->executePhp($request, $project, $docRoot, $index, '')
                    : $this->serveStatic($request, $index);
            }
        }

        // Nothing on disk. PHP runtimes use a single front controller with
        // pretty URLs (Laravel/WordPress) — route to it carrying PATH_INFO.
        if ($project->isPhpRuntime()) {
            $front = $docRoot . DIRECTORY_SEPARATOR . ($project->entry_point ?: 'index.php');
            if (is_file($front)) {
                $pathInfo = '/' . ltrim(str_replace(DIRECTORY_SEPARATOR, '/', $relative), '/');

                return $this->executePhp($request, $project, $docRoot, $front, $pathInfo);
            }
        }

        // Static SPA fallback: serve the entry HTML so client-side routes work.
        if ($project->isStaticRuntime()) {
            $entry = $docRoot . DIRECTORY_SEPARATOR . ($project->entry_point ?: 'index.html');
            if (is_file($entry)) {
                return $this->serveStatic($request, $entry);
            }
        }

        abort(404);
    }

    /* ----------------------------------------------------------- static */

    private function serveStatic(Request $request, string $file): Response
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = self::MIME_MAP[$ext] ?? (mime_content_type($file) ?: 'application/octet-stream');

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', $mime);
        // Cache hashed/static assets; keep HTML revalidated so redeploys show up.
        if (in_array($ext, ['html', 'htm'], true)) {
            $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        } else {
            $response->setMaxAge(3600)->setPublic();
        }
        $response->isNotModified($request);

        return $response;
    }

    /* ---------------------------------------------------------- php-cgi */

    private function isExecutablePhp(HostedProject $project, string $file): bool
    {
        return $project->isPhpRuntime()
            && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'php';
    }

    /**
     * Execute a PHP script via php-cgi and translate the CGI response into an
     * HTTP response. open_basedir jails the script to its own document root.
     */
    private function executePhp(Request $request, HostedProject $project, string $docRoot, string $script, string $pathInfo): Response
    {
        $cgi = config('hosting.php_cgi_path', 'php-cgi');
        if (! is_file($cgi) && ! $this->onPath($cgi)) {
            return response(
                "PHP runtime (php-cgi) not available in this environment.\n"
                . 'On the production server nginx + php-fpm serve PHP directly.',
                501
            )->header('Content-Type', 'text/plain; charset=utf-8');
        }

        $body = $request->getContent();
        $env = $this->cgiEnvironment($request, $project, $docRoot, $script, $pathInfo, strlen($body));

        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open([$cgi], $descriptors, $pipes, $docRoot, $env);
        if (! is_resource($process)) {
            return response('Failed to start PHP runtime.', 500);
        }

        fwrite($pipes[0], $body);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        if ($exit !== 0 && $stdout === '') {
            return response(
                'PHP execution error.' . (config('app.debug') ? "\n\n" . $stderr : ''),
                500
            )->header('Content-Type', 'text/plain; charset=utf-8');
        }

        return $this->parseCgiResponse($stdout);
    }

    /** @return array<string,string> */
    private function cgiEnvironment(Request $request, HostedProject $project, string $docRoot, string $script, string $pathInfo, int $contentLength): array
    {
        $scriptName = '/hosted/' . $project->slug . '/' . ltrim(str_replace('\\', '/', substr($script, strlen($docRoot))), '/');

        $env = [
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'REDIRECT_STATUS' => '200',                  // required by php-cgi (cgi.force_redirect)
            'SERVER_SOFTWARE' => 'PortfolioHostingEngine',
            'SERVER_PROTOCOL' => $request->server('SERVER_PROTOCOL', 'HTTP/1.1'),
            'SERVER_NAME' => $request->getHost(),
            'SERVER_PORT' => (string) $request->getPort(),
            'REQUEST_METHOD' => $request->getMethod(),
            'REQUEST_URI' => $request->getRequestUri(),
            'QUERY_STRING' => $request->getQueryString() ?? '',
            'SCRIPT_FILENAME' => $script,
            'SCRIPT_NAME' => $scriptName,
            'DOCUMENT_ROOT' => $docRoot,
            'PATH_INFO' => $pathInfo,
            'REMOTE_ADDR' => $request->ip() ?? '127.0.0.1',
            'CONTENT_TYPE' => $request->header('Content-Type', ''),
            'CONTENT_LENGTH' => (string) $contentLength,
            'HTTPS' => $request->isSecure() ? 'on' : '',
            // Per-project filesystem jail + a writable temp dir.
            'PHP_VALUE' => 'open_basedir=' . $docRoot . DIRECTORY_SEPARATOR . PATH_SEPARATOR . sys_get_temp_dir(),
        ];

        // Forward client request headers as HTTP_* CGI vars.
        foreach ($request->headers->all() as $name => $values) {
            $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
            $env[$key] = is_array($values) ? implode(', ', $values) : (string) $values;
        }

        return $env;
    }

    /** Split the php-cgi "headers\r\n\r\nbody" output into an HTTP response. */
    private function parseCgiResponse(string $raw): Response
    {
        $split = preg_split("/\r\n\r\n|\n\n/", $raw, 2);
        $headerBlock = $split[0] ?? '';
        $bodyContent = $split[1] ?? '';

        $status = 200;
        $headers = [];
        foreach (preg_split("/\r\n|\n/", $headerBlock) as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode(':', $line, 2));
            if (strcasecmp($key, 'Status') === 0) {
                $status = (int) $value ?: 200;

                continue;
            }
            $headers[$key] = $value;
        }

        return response($bodyContent, $status, $headers);
    }

    /* ----------------------------------------------------------- helpers */

    /** Strip traversal sequences and normalise separators. */
    private function sanitize(string $path): string
    {
        $path = str_replace(['\\', "\0"], ['/', ''], $path);
        $parts = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);

                continue;
            }
            $parts[] = $segment;
        }

        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    private function within(string $base, string $target): bool
    {
        $base = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return $target === rtrim($base, DIRECTORY_SEPARATOR) || str_starts_with($target . DIRECTORY_SEPARATOR, $base);
    }

    private function onPath(string $bin): bool
    {
        // Bare command name (no separators) → assume resolvable via PATH.
        return ! str_contains($bin, '/') && ! str_contains($bin, '\\');
    }
}
