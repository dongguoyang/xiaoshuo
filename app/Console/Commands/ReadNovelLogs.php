<?php
/**
 * 同步小说阅读数据
 *
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Logics\Models\ReadLog;
use App\Logics\Models\ReadNovelLogs as ReadNovelLogsModel;
use App\Logics\Repositories\src\ReadNovelLogsRepository;


class ReadNovelLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     *
     */
    protected $signature = 'novel:novel-read-logs';
    protected $description = '公众号wap站的小说采集程序';
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ReadNovelLogsRepository = new ReadNovelLogsRepository();
        DB::table('read_logs')->orderBy('id')->chunk(100, function($read_logs) use($ReadNovelLogsRepository){
            foreach ($read_logs as $read_log) {
                //$customer_id,$platform_wechat_id,$novel_id,$novel_section_id,$novel
                $sectionlist =  trim($read_log->sectionlist,',');
                $sectionArray = explode(',',$sectionlist);
                foreach ($sectionArray as $section){
                    $ReadNovelLogsRepository->AddNumLog($read_log->customer_id,$read_log->platform_wechat_id,$read_log->novel_id,$section,['title'=>$read_log->name,'section_title'=>$read_log->title]);
                }
                //dd($sectionArray);
            }
        });
        dd('end');
    }

}
