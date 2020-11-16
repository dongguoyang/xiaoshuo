<?php
namespace App\Http\Controllers\Platform;

use App\Http\Controllers\BaseController;
use App\Logics\Repositories\src\NovelPayStatisticsRepository;

class UserReadController extends BaseController {

    public function log() {
        (new NovelPayStatisticsRepository())->update_read_day();
    }

}
