<?php
/**
 * Created by LWL.
 * User: LUWENLONG
 * Date: 2019/3/14
 * Time: 21:26
 */

namespace App\Libraries\Facades;


use Illuminate\Support\Facades\Facade;

class Manage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Libraries\Images\Manage::class;
    }
}