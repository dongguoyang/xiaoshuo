<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class NovelsCollect implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $call_name;
    protected $call_arg;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($call_name,$call_arg)
    {
        //
        $this->call_name = $call_name;
        $this->call_arg  = $call_arg;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 3) {
            Log::info('小说采集 attempts > 3 后失败！');
        } else {
            Artisan::call($this->call_name, $this->call_arg);
        }
    }
}
