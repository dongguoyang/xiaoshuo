<?php

namespace App\Console\Commands;

use App\Logics\Repositories\src\AuthorRepository;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NovelCheckContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:novel-check-content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检测章节内容异常的执行同步';

    private $authors;
    private $novels;
    private $novelSections;
    private $commonSets;

    private $nowAt; // 当前时间戳
    private $passwd = '890432jfkds8jh32hjkfdsjkjjkl21iuy8idhsajknfy241nmfdjksa32212u7e28winj';
    private $syncHost; // 小说同步接口域名
    private $disk;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->novels        = new NovelRepository();
        $this->novelSections = new NovelSectionRepository();
        $this->commonSets    = new CommonSetRepository();
        $this->nowAt         = time();
        $this->disk     = config('filesystems.default');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->syncHost = $this->commonSets->values('sync_novel_api', 'domain');
        $this->info($this->signature . ' start do!' . date('Y-m-d H:i:s'));
        $rel = $this->checkSectionContent();
        dump($rel);
        dump($this->signature . ' end at '. date('Y-m-d H:i:s'), '----------------------------- 更新结果如下 -----------------------------', $rel);
    }
    protected function checkSectionContent() {
        $start = $this->nowAt - 86400;
        $rel = ['sections'=>0, 'update'=>0, 'fail'=>0, 'normal' => 0];

        $selects = ['id', 'novel_id', 'num', 'title', 'content'];
        $first = $this->novelSections->model->where('created_at', '>', $start)->orderBy('id')->select($selects)->first();
        if (!$first) return $rel;
        $min_id = $first['id'];
        while (true) {
            $list = $this->novelSections->model->where('id', '>', $min_id)->limit(500)->orderBy('id')->select($selects)->get();
            if (!$list) break;

            foreach ($list as $section) {
                $rel['sections']++;
                if (!$content = $this->getContent($section['content'])) {
                    if ($this->syncSectionContent($section)) {
                        $rel['update']++;
                    }else {
                        $rel['fail']++;
                    }
                } else {
                    $rel['normal']++;
                }
            }
            $min_id = $section['id'];
        }
        return $rel;
    }
    // 获取章节内容
    private function getContent($url, $re = 0) {
        if (empty($url)) return false;

        $url = (substr($url, 0, 4) != 'http') ? config('app.url') . $url : $url;
        $content = @file_get_contents($url);
        if (!$content && $re < 3) {
            sleep(1);
            return $this->getContent($url, ++$re);
        }
        return $content;
    }
    // 同步章节内容
    private function syncSectionContent($section) {
        $novel = $this->novels->find($section['novel_id'], ['title', 'tags', 'desc', 'origin_web']);
        $params = $this->novels->toArr($novel);
        $params['num'] = $section['num'];
        $params['passwd'] = $this->passwd;

        $curl_data = CallCurl($this->syncHost . '/api/novel/section_content', $params, 1);
        $data = json_decode($curl_data, 1);

        if (empty($data) && strpos($curl_data, '<title>Too Many Requests</title>')) {
            // 接口请求过多就暂停一下再来
            sleep(10);
            return $this->syncSectionContent($section);
        }

        if ($data['err_code'] > 0 || empty($data['data'])) {
            return false;
        }

        return $this->uploadCloud($section['content'], $data['data']);
    }

    /**
     * 上传图片或者文件到云对象存储
     * @param string $path
     * @param string $str 文件流
     */
    protected function uploadCloud($path, $str, $re = 0) {
        $dir = explode('.com/', $path)[1];

        try {
            if (!Storage::disk($this->disk)->put($dir, $str))
            {
                throw new \Exception('文件上传失败！', 2001);
            }

            $url = Storage::disk($this->disk)->url($dir);
            return $url;
        } catch (\Exception $e) {
            dump(
                date('Y-m-d H:i:s'),
                $e->getMessage(),
                'error line : ' . $e->getLine(),
                '--------------------------------------------------'
            );
            if ($re < 3) {
                $re++;
                sleep($re * 3);
                return $this->uploadCloud($path, $str, $re);
            }
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}
