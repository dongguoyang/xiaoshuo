<?php

namespace App\Console\Commands;
use App\Logics\Traits\CollectTrait;
use Illuminate\Console\Command;



class SectionUpdate extends Command
{
    use CollectTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'novel:section-update';
    protected $description = '章节域名更新';
    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->initCollect();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $page = 1;
        while(true){
            $this->section_update($page);
            $page++;
        }

    }

    private function section_update($page = 1){
        $result = $this->novelSections->limitPage([],$page,50,['id','content']);
        if($result){
            foreach ($result as $key=>$value){
                $url = str_replace('https://www.zhengzhiwen.net','http://hangzhouxiaoshuo.oss-cn-hangzhou.aliyuncs.com',$value['content']);
                if($url){
                    $res = $this->novelSections->update(['content'=>$url], $value['id']);
                    var_dump($res);
                }
            }
        }
    }

    private  function  novel_update_img($page = 1){
        $result = $this->novels->limitPage([],$page,50,['id','img']);
        if($result){
            foreach ($result as $key=>$value){
                $url = str_replace('https://www.zhengzhiwen.net','http://hangzhouxiaoshuo.oss-cn-hangzhou.aliyuncs.com',$value['img']);
                if($url){
                    $res = $this->novels->update(['img'=>$url], $value['id']);
                    var_dump($res);
                }
            }
        }
    }

}
