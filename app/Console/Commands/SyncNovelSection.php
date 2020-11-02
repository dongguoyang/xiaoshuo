<?php

namespace App\Console\Commands;

use App\Logics\Repositories\src\AuthorRepository;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncNovelSection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:sysc-all-data {--T|type= : 指定操作类型}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步小说最新数据';

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

        $this->authors       = new AuthorRepository();
        $this->novels        = new NovelRepository();
        $this->novelSections = new NovelSectionRepository();
        $this->commonSets    = new CommonSetRepository();
        $this->nowAt         = time();
        // $disk = config('filesystems.default');
        // $disk = 'local';
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
        if (!$this->syncHost) exit(0);
        $this->info($this->signature . ' start do!' . date('Y-m-d H:i:s'));

        if ($host = $this->option('type') == 'checknull') {
            $rel = $this->checkNoSection();
        } else {
            $rel = $this->addNovelData();
        }
        dd($this->signature . ' end at '. date('Y-m-d H:i:s'), '----------------------------- 更新结果如下 -----------------------------', $rel);
    }
    protected function addNovelData() {
        $page = 0;
        $params = [
            'start' => ($this->nowAt - 3600*4),
            'end'   => $this->nowAt,
            'passwd'=> $this->passwd,
        ];
        $rel = ['add_section'=>0, 'add_novel'=>0, 'up_novel'=>0];
        while (true) {
            $page++;
            $params['page'] = $page;
            $curl_data = CallCurl($this->syncHost . '/api/novel/sync', $params, 1);
            $data = json_decode($curl_data, 1);

            if (empty($data) && strpos($curl_data, '<title>Too Many Requests</title>')) {
                // 接口请求过多就暂停一下再来
                $page--;
                sleep(10);
                continue;
            }
            if ($data['err_code'] > 0 || empty($data['data'])) {
                break;
            }

            foreach ($data['data'] as $novel) {
                $novel = $this->getAuthor($novel);
                $had_novel = $this->novels->findByMap([
                    // ['author_id', $novel['author_id']], // author_name 重复太多，author_id 太多了，就不查他了
                    ['author_name', $novel['author_name']],
                    ['title', $novel['title']],
                ], ['id', 'sections', 'status', 'need_buy_section', 'subscribe_section']);
                $had_novel = $this->novels->toArr($had_novel);
                if (!$had_novel) {
                    // 没有小说就新建小说
                    $data = $novel;
                    unset($data['id']);
                    $data['img'] = $this->uploadCloud($data['img']);
                    $data['status'] = 0;
                    $data = $this->novels->create($data);
                    $had_novel['id'] = $data['id'];
                    $rel['add_novel']++;
                    // 添加章节数据
                    list($add_section, $num) = $this->addSectionData($had_novel['id'], $novel['id']);
                    if ($add_section > 20) {
                        $this->novels->update(['status' => $novel['status'], 'sections'=>$num], $had_novel['id']);
                    }
                    $rel['add_section'] += $add_section;
                } else {
                    if ($had_novel['sections'] == $novel['sections'] && // 章节数相同
                        $had_novel['status'] == $novel['status'] && // 状态相同
                        $had_novel['need_buy_section'] == $novel['need_buy_section'] && // 需付费章节数相同
                        $had_novel['subscribe_section'] == $novel['subscribe_section'] // 提示关注章节数相同
                    ) {
                        continue; // 章节数相同；就没有章节需要更新
                    }
                    // 更新小说章节总数和章节数据
                    $this->novels->update([
                        'sections'=>$novel['sections'],
                        'status'=>$novel['status'],
                        //'need_buy_section'=>$novel['need_buy_section'],
                        //'subscribe_section'=>$novel['subscribe_section'],
                    ], $had_novel['id']);
                    if ($had_novel['sections'] == $novel['sections']) {
                        continue; // 章节数相同；就没有章节需要更新
                    }
                    list($upsec, $num) = $this->addSectionData($had_novel['id'], $novel['id']);
                    if ($upsec) {
                        $rel['add_section'] += $upsec;
                        $rel['up_novel']++;
                        $this->novels->update(['status' => $novel['status'], 'sections'=>$num], $had_novel['id']);
                    }
                }
            }
        }
        return $rel;
    }
    /**
     * @param int $new_id 新增入库的小说ID
     * @param int $old_id 老的，从接口获取的那个库的小说ID
     */
    private function addSectionData($new_id, $old_id=0, $par = []) {
        $params = [
            //'novel_id' => $old_id,
            'passwd'   => $this->passwd,
            'num'      => 0
        ];
        if ($old_id) {
            $params['novel_id'] = $old_id;
        }
        if ($par) {
            $params = array_merge($params, $par);
        }
        $section = $this->novelSections->model
            ->where('novel_id', $new_id)
            ->orderBy('num', 'desc')
            ->select(['id', 'num'])
            ->first();
        if ($section && $section['num']) {
            $params['num'] = $section['num'];
        }

        $add_section = 0;
        while (true) {
            $curl_data = CallCurl($this->syncHost . '/api/novel/sync', $params, 1);
            $data = json_decode($curl_data, 1);

            if (empty($data) && strpos($curl_data, '<title>Too Many Requests</title>')) {
                // 接口请求过多就暂停一下再来
                sleep(10);
                continue;
            }
            if ($data['err_code'] > 0 || empty($data['data'])) {
                break;
            }
            foreach ($data['data'] as $section) {
                if ($had_section = $this->novelSections->findByMap([
                    ['novel_id', $new_id],
                    ['num', $section['num']],
                ], ['id', 'num'])) {
                    continue;
                }

                unset($section['id']);
                $section['content'] = $this->uploadCloud($section['content']);
                $section['novel_id'] = $new_id;
                $this->novelSections->create($section);
                $add_section++;
            }
            $params['num'] = $section['num'];
        }
        return [$add_section, $params['num']];
    }
    /**
     * 上传图片或者文件到云对象存储
     * @param string $path
     * @param string $str 文件流
     */
    protected function uploadCloud($link, $re = 0) {
        $sub_start = strpos($link, '.com/');
        $path = substr($link, ($sub_start ? $sub_start + 5 : 0));
        if (strpos($path,'lm-novelspider/') !== false) {
            $arr = explode('lm-novelspider/', $path);
            $path = end($arr);
        }
        $dir = 'lm-novelspider/sync/' . $path;

        $str = @file_get_contents($link);

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
                return $this->uploadCloud($link, $re);
            }
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * 检查作者是否存在；不存在就新建
     * @param string $novel
     */
    private function getAuthor($novel) {
        $had = $this->authors->findByMap([
            // ['id', $novel['author_id']],
            ['name', $novel['author_name']],
        ], ['id']);
        if (!$had || !$had['id']) {
            $author = $this->authors->create(['name'=>$novel['author_name']]);
            $novel['author_id'] = $author['id'];
        } else {
            $novel['author_id'] = $had['id'];
        }

        return $novel;
    }

    private function checkNoSection() {
        $min_id = 0;
        while (true) {
            $list = $this->novels->model->where('id', '>', $min_id)->limit(200)->orderBy('id')->select(['id', 'title', 'tags', 'desc', 'origin_web', 'sections', 'status'])->get();
            if (!$list) break;
            foreach ($list as $had_novel) {
                $sections = $this->novelSections->model->where('novel_id', $had_novel['id'])->count();
                if ($sections != $had_novel['sections']) {
                    $params = [
                        'title' => $had_novel['title'],
                        'tags'  => $had_novel['tags'],
                        'desc'  => $had_novel['desc'],
                    ];
                    list($upsec, $num) = $this->addSectionData($had_novel['id'], 0, $params);
                    if ($upsec) {
                        $up_data = ['sections'=>$num];
                        if ($num > 20 && $had_novel['status'] == 0) {
                            $up_data['status'] = 1;
                        }
                        $this->novels->update($up_data, $had_novel['id']);
                    }
                }
            }
            $min_id = $had_novel['id'];
        }
    }
}
