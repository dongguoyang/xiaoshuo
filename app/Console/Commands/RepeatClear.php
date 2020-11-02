<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepeatClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ClearDobleNovel:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '去掉重复脚本';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $database;
    public function __construct()
    {
        parent::__construct();
        $this->database='mysql';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $novel=DB::connection($this->database)->table('novels')->where('status',1)->get()->toArray(); //查询所有status为1的
        foreach ($novel as $key=>$vs){
            $title=$vs->title;
            $info=DB::connection($this->database)->table('novels')->where('status',1)->where('title',$title)->orderBy('id','desc')->get();
            $count=count($info);
            if($count == 1){
                continue;
            }else{
                $this->info($title.'--的小说正在停用');
               foreach ($info as $inkey=>$inval){
                   if($inkey == 0){
                       continue;
                   }

                   DB::connection($this->database)->table('novels')->where('id',$inval->id)->update(['status'=>0]);  //停用除了最大id 其他全停用
               }
            }

        }
    }
}
