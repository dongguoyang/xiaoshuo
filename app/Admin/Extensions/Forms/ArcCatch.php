<?php
/**
 * Created by PhpStorm.
 * User: byhenry
 * Date: 2019/1/12
 * Time: 5:20 PM
 */

namespace App\Admin\Extensions\Forms;


use Encore\Admin\Form\Field\Url;

class ArcCatch extends Url
{
    public function render()
    {
        $this->prepend('<i class="fa fa-internet-explorer fa-fw"></i>')
            ->append('<a class="btn-sm btn-primary tocatch" type="button" style="cursor: pointer">马上采集</a>')
            ->defaultAttribute('type', 'text');
        return parent::render();
    }
}