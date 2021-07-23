<?php

namespace Kksigma\TMS\Commands;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;

class PullTranslationsCommand extends Command
{
    public $signature = 'tms:pull {token} {code?}';

    public $description = 'pull translations from Tms';

    public $file_system;

    public $translations;

    public function handle()
    {
        $this->pullTranslationFromTms();

        if (! $this->translations) {
            $this->error('not data');
            return;
        }

        $this->file_system = new Filesystem();

        $dir = resource_path('lang');
        $langs = array_keys($this->translations);

        $support_langs = $this->file_system->directories($dir);
        $support_langs = array_map(function ($dir) {
            return basename($dir);
        }, $support_langs);

        foreach ($langs as $lang) {
            if (!in_array($lang, $support_langs)) {
                continue;
            }

            $path = $dir . '/' . $lang;
            $this->loadDirectory($path, $lang);
        }

        $this->comment('All done');
    }

    private function pullTranslationFromTms()
    {
        $url = config('tms.url');
        $data = [
            'project_name' => config('tms.project_name'),
        ];
        if ($code = $this->argument('code')) {
            $data['code'] = $code;
        }

        $response = Http::retry(10, 300)
            ->withToken($this->argument('token'))
            ->asJson()
            ->acceptJson()
            ->get($url, $data)
            ->throw();

        $this->translations = Arr::get(json_decode($response->body(), true), 'data', []);
    }

    private function loadDirectory(string $path, string $lang, $domain = '')
    {

        $directories = $this->file_system->directories($path);
        // 只要还含有文件夹，循环此方法
        foreach ($directories as $directory) {
            $dir_domain = $domain . basename($directory) . '/';
            $this->loadDirectory($directory, $lang, $dir_domain);
        }

        $files = $this->file_system->files($path);
        foreach ($files as $file) {
            $this->replaceFile($file, $lang, $domain);
        }
    }

    private function replaceFile($file, $lang, $domain): void
    {
        // 去掉最后一个/
        $domain = substr($domain, 0, strrpos($domain, '/'));
        $module = $file->getFilenameWithoutExtension();
        $file_translations = Arr::dot($this->file_system->getRequire($file));
        $tms_translations = Arr::get($this->translations, $lang . '.' . $domain . '.' . $module);

        $data = [];

        foreach ($file_translations as $key => $file_translation_value) {
            // 如果tms翻译含有该key，那值取tms的值，否则取自身
            $value = Arr::get($tms_translations, $key, $file_translation_value);
            $data = data_fill($data, $key, $value);
        }
        $content = '<?php' . PHP_EOL . 'return ' . var_export($data, true) . ';';

        $this->file_system->replace($file->getPathname(), $content);
    }
}
