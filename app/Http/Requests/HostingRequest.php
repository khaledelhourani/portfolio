<?php

namespace App\Http\Requests;

use App\Models\HostedProject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $phpVersions = implode(',', config('hosting.php_versions', ['8.1', '8.2', '8.3']));

        return [
            'name' => ['required', 'string', 'max:120'],
            'name_ar' => ['nullable', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9-]+$/', 'unique:hosted_projects,slug'],
            'description' => ['nullable', 'string', 'max:1000'],

            // 'auto' lets the engine detect the runtime from the archive.
            'type' => ['required', Rule::in(array_merge(['auto'], HostedProject::TYPES))],
            'entry_point' => ['nullable', 'string', 'max:120'],
            'php_version' => ['nullable', 'in:' . $phpVersions],
            'custom_domain' => ['nullable', 'string', 'max:160'],

            // Database is opt-in; provisioning happens in the deploy job.
            'has_database' => ['sometimes', 'boolean'],
            'env_vars' => ['nullable', 'string', 'max:20000'],   // KEY=VALUE per line

            'zip' => ['required', 'file', 'mimes:zip', 'max:' . config('hosting.max_upload_mb', 2048) * 1024],
            'sql' => ['nullable', 'file', 'mimes:sql,txt', 'max:' . config('hosting.max_sql_mb', 2048) * 1024],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'المعرّف يجب أن يحتوي حروفاً صغيرة وأرقاماً وشرطات فقط.',
            'zip.mimes' => 'يجب رفع ملف ZIP صالح.',
            'zip.max' => 'حجم الأرشيف يتجاوز الحد المسموح (' . config('hosting.max_upload_mb', 500) . ' م.ب).',
        ];
    }

    /**
     * Parse the env_vars textarea (KEY=VALUE lines) into an associative array.
     *
     * @return array<string,string>
     */
    public function envVars(): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $this->input('env_vars')) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            if ($k !== '') {
                $out[$k] = trim($v);
            }
        }

        return $out;
    }
}
