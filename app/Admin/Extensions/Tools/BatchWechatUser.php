<?php
/**
 * 批量生成微信公众号菜单
 */
namespace App\Admin\Extensions\Tools;
use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class BatchWechatUser extends AbstractTool {
 //   public $platform_wechat_id;
 //   public $customer_id;
 //   public function __construct($platform_wechat_id,$customer_id)
 //   {
 //       $this->platform_wechat_id = $platform_wechat_id;
 //       $this->customer_id = $customer_id;
 //       var_dump($this->platform_wechat_id,$this->customer_id);
 //   }
    
    protected function script() {
        return <<<SCRIPT
$('.batch-generate-menu).on('click', function () {
        console.log(12312312);
});
SCRIPT;
    }

    public function render() {
        Admin::script($this->script());
        //var_dump($res);
        //return "<a class='btn btn-xs btn-success batch-generate-menu' title='生成所有菜单'><i class='fa fa-sitemap'></i></a>";
        return "<a class='btn btn-xs btn-success batch-generate-menu' title='单人发送'>单人发送</a>";
        //return '<button data-method="offset" data-type="auto" class="layui-btn layui-btn-normal">居中弹出</button>';
        //return view('admin.tools.message');
    }

    public function __toString() {
        return $this->render();
    }
}