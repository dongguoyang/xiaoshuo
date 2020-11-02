<?php
/**
 * 复制熊总的小说
 *
 * 运行命令
 * php artisan novel:copy-xiong {page}
 *
 */

namespace App\Console\Commands;

use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class CopyZMRNovelResource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'novel:copy-zmr-resource {page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '下载小说资源到新部署环境；novel:copy-zmr-resource {page}；page=0/all表示全部复制';


    private $novels;
    private $novelSections;
    private $limit = 50;// 每页几条数据
    private $disk;
    private $bucket;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->novels  = new NovelRepository();
        $this->novelSections    = new NovelSectionRepository();

        $this->disk = config('filesystems.default');
        $this->bucket = '//' . config('filesystems.disks.'. $this->disk . '.bucket') . '.oss-cn-';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $page = intval($this->argument('page'));

        try {
            $this->copyResource($page);
        } catch (\Exception $e) {
            dump('----------------------- Copy failed, $page = '.$page.'------------------------', $e->getMessage(), 'line : '.$e->getLine());
        }
    }
    /**
     * copy novel img
     */
    private function copyResource($page = 0) {
        if ($page == 0) {
            while (true) {
                if (!$this->copyPage(++$page)) {
                    break;
                }
            }
        } else {
            $this->copyPage($page);
        }
    }
    private function copyPage($page) {
        $novellist = $this->novelList($page);
        if (empty($novellist)) {
            return false;
        }
        foreach ($novellist as $k => $novel) {
            if (!empty($novel['img']) && strpos($novel['img'], $this->bucket) === false) {
                try {
                    $img = $this->getFileResource($novel['img'], 'img', $novel['id']);
                    if (!empty($img) && !$this->novels->update(['img'=>$img], $novel['id'])) {
                        throw new \Exception('小说 ' . $novel['id'] . ' banner图更新失败！', 2000);
                    }
                } catch (\Exception $e) {
                    dump('novel = '.$novel['id'] . ' 小说banner图获取失败！', $e->getMessage(), $e->getLine());
                    // continue;
                }
            }

            $this->copySectionResource($novel['id']);
        }
        dump('================ All over ! 第 '.$page. ' 页，数据复制完毕！====================');
        return true;
    }
    private function copySectionResource($novel_id) {
        $min_id = 0;
        while (true) {
            $list = $this->novelSections->model
                ->where('novel_id', $novel_id)
                ->where('id', '>', $min_id)
                ->where('content', 'not like', '%'.$this->bucket.'%')
                ->limit($this->limit)
                ->select(['id', 'content'])
                ->get();
            if (!$list || !count($list) || empty($list)) {
                break;
            }
            foreach ($list as $section) {
                try {
                    $content = $this->getFileResource($section['content'], 'html', $novel_id);
                    if (!empty($content) && !$this->novelSections->update(['content'=>$content], $section['id'])) {
                        dump('section = '.$section['id'] . ' 章节内容更新失败！');
                    }
                } catch (\Exception $e) {
                    dump(
                        '*************** novel = '.$novel_id . ' section = '. $section['id'] . ' ___ '. $section['content'] .' 章节内容获取失败！****************',
                        $e->getMessage(),
                        $e->getLine()
                    );
                    // continue;
                }
            }
            $min_id = $section['id'];
        }
        dump('novel = '. $novel_id. ' 章节内容更新完毕！');
    }
    /**
     * 获取小说列表
     */
    private function novelList($page) {
        $query = $this->novels->model->orderBy('id')->select(['id', 'img']);
        if ($page) {
            $query = $query->offset($this->limit * ($page - 1))->limit($this->limit);
        }
        $alllist = $query->get();
        //$ids = $this->novels->toPluck($alllist, 'id');
        $alllist = $this->novels->toArr($alllist);
        return $alllist;
        /*$list = $this->novels->model
            ->whereIn('id', $ids)
            ->where('img', 'not like', $this->bucket)
            ->orderBy('id')
            ->select(['id', 'img'])
            ->get();
        $list = $this->novels->toArr($list);
        foreach ($alllist as $k=>$item) {
            if ($item['id'] >= $list[0]['id']) {
                if (isset($alllist[$k-1])) {
                    array_unshift($list, $alllist[$k-1]);
                }
                break;
            }
        }*/

        return $list;
    }
    /**
     * 获取文件资源
     */
    private function getFileResource($url, $type='img', $id=0) {
        if (strpos($url, 'http') !== 0) {
            return '';
        }

        try {
            $resource = file_get_contents($url);
        } catch (\Exception $e) {
            dump(
                'file_get_contents error $type = '.$type . '|| $novel_id = '.$id,
                '++++ url = '.$url,
                $e->getMessage(),
                $e->getLine()
            );
            throw new \Exception($e->getMessage(), 2000);
        }

        $ext = substr($url, strrpos($url, '.'));
        $name = $type . '/' . $id . date('/Ymd/His_') . RandCode(18, 12) . $ext;
        $url = $this->uploadCloud($name, $resource);
        return $url;
    }
    /**
     * 上传图片或者文件到云对象存储
     * @param string $path
     * @param string $str 文件流
     */
    private function uploadCloud($path, $str, $re = 0) {
        $dir = 'lm-novelspider/copyzmr/' . $path;

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
    /**
     * 上传图片或者文件到云对象存储
     * @param string $path
     * @param string $str 文件流
     */
    private function deleteCloud($path) {
        $path = substr($path, strpos($path, '.com/') + 5);

        if (!Storage::disk($this->disk)->delete($path))
        {
            throw new \Exception('文件删除失败！', 2001);
        }
    }
}
