<?php
/**
 * 笔趣阁 wap 章小说采集的命令
 *
 * 运行命令
 * php artisan novel:collect-biquge-wap urlencode(cookie)     --host= // 采集玄幻分类
 * php artisan novel:collect-biquge-wap asdidkd%3d1%3b+Hm_lvt_447fadd6bad32bfe438b95a891015d5b%3d1593164267%3b+Hm_lpvt_447fadd6bad32bfe438b95a891015d5b%3d1593164381 --host=https://wap.biqiuge.com --link=https://wap.biqiuge.com/book_44988 --type_id=19  --suitable_sex=1
php artisan novel:collect-changdu-zhongwen  --host=https://sitel5mowkgk15re0qx9.iycdm.com
 *
 */

namespace App\Console\Commands;

use App\Logics\Traits\CollectTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Logics\Repositories\src\TypeRepository;


class CollectUpdateNovel extends Command
{
    use CollectTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'novel:collect-update-wap';
    protected $novelWeb = 'dkxsb';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->type_model = new TypeRepository();
        parent::__construct();
        $this->initCollect();
    }

    public function handle()
    {
        $section = $this->novelSections->getAllByMap([['novel_id', 188659]], ['id','num', 'novel_id', 'title', 'spider_url','content'],['num'=>'asc']);
        foreach ($section as $key=>$value){
            $content = file_get_contents($value->content);
            //$content  = explode('<p style="width:100%;text-alight:center;">',$content);
            //$content_o  = explode('百度直接搜索:',$content[0]);
            //$content_oo = str_replace('<p><p>还在找"私欲"免费小说?</p></p><p></p></p><p><p>','',$content_o[0]);
            $data['novel_id']   = 189093;
            $data['num']        = $value['num'];
            $data['title']      = $value['title'];
            $path = 'html/' . 189093 . date('/Ymd/His') . RandCode(5, 12) . '.html';
            $data['content']    = $this->uploadCloud($path, $content);
            $data['spider_url'] = '/v1/read/7786673';
            $result = $this->novelSections->create($data);
            echo $value['num'];
        }

    }
}
