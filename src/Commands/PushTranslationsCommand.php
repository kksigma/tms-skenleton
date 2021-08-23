<?php

namespace Kksigma\TMS\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class PushTranslationsCommand extends Command
{
    public $signature = 'tms:push
    {lang_dirs : example: en,ja,zh}
    {token}
    {code? : Language code}';

    public $description = 'push translations to Tms';

    public $file_system;

    public $translations;

    public function handle()
    {
        // 将3d项目翻译推送到tms
        $this->file_system = new Filesystem();
        $this->translations = [];

        $dir = resource_path('lang');
        $need_langs = explode(',', $this->argument('lang_dirs'));

        // support_lang_dirs => ['en', 'zh', ....];
        $support_lang_dirs = $this->file_system->directories($dir);
        $support_lang_dirs = array_map(function ($dir) {
            return basename($dir);
        }, $support_lang_dirs);

        foreach ($need_langs as $lang) {
            if (! in_array($lang, $support_lang_dirs)) {
                $this->error('not support lang: ' . $lang);

                continue;
            }

            $path = $dir . '/' . $lang;
            $this->loadDirectory($path, $lang);
        }

        $this->pushToTms();
        $this->comment('All done');
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
            $this->setTranslation($file, $lang, $domain);
        }
    }

    private function setTranslation($file, $lang, $domain): void
    {
        // 去掉最后一个/
        $domain = substr($domain, 0, strrpos($domain, '/'));

        // module == file_name
        $module = $file->getFilenameWithoutExtension();

        if (! $domain) {
            $domain = $module == '通用' ? '后端通用' : '框架';
        }

        $translations = Arr::dot($this->file_system->getRequire($file));

        foreach ($translations as $key => $value) {
            $this->translations[$lang][$domain][$module][$key] = $value;
        }
    }

    private function pushToTms()
    {
        // $this->translations = include __DIR__ . '/lang.php';
        // developer token EtGMNguKVSFf5TGZ9dFr3q7ZtG31TuI2OmGTTxLD
        // test token GPfqhmu8vZHY1zNJ64nHokaRYT4ZnZs3f3OOpcWG
        $url = config('tms.url');

        $data = [
            'translations' => $this->translations,
            'project_name' => config('tms.project_name'),
        ];

        if ($code = $this->argument('code')) {
            $data['code'] = $code;
        }

        $response = Http::retry(10, 300)
            ->withToken($this->argument('token'))
            ->asJson()
            ->acceptJson()
            ->post($url, $data);

        $response->throw();
    }
}
