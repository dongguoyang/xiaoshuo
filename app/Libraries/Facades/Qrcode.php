<?php
/**
 * Created by LWL.
 * User: LUWENLONG
 * Date: 2019/3/14
 * Time: 20:01
 */

namespace App\Libraries\Facades;


use Illuminate\Support\Facades\Facade;

class Qrcode extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Libraries\Images\Qrcode::class;
    }
}