<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/3
 * Time: 16:05
 */

namespace App\Admin\Extensions\Grids\Buttons;


use Encore\Admin\Grid\Tools\AbstractTool;
class WechatmsgInsert extends AbstractTool
{
    protected  $url;
    protected  $icon;
    protected $text;
    protected $id;
    function __construct($url,$icon,$text,$id)
    {
        $this->url = $url;
        $this->icon = $icon;
        $this->text = $text;
        $this->id=$id;
    }

    public function render()
    {
        $url = $this->url;
        $icon = $this->icon;
        $text = $this->text;
        $id=$this->id;
        return view('admin.tools.button', compact('url','icon','text','id'));
    }

}