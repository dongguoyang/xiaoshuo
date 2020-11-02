<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grids\Filters\TimestampBetween;
use App\Admin\Models\User;
use App\Admin\Models\WechatsUser;
use App\Admin\Models\Wechat;
use App\Admin\Models\Customer;
use App\Admin\Models\ExtendLink;
use App\Admin\Models\PlatformWechat;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Admin\Extensions\Tools\BatchWechatUser;
use Illuminate\Support\Facades\Artisan;

class WechatsUserListController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户管理';
    public $customer;

    public function customer() {
        if(!empty($this->customer)) {
            return $this->customer;
        } else {
            return $this->customer = Admin::user();
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $this->customer();
        $customer = $this->customer;
        $wechat = Wechat::where('customer_id', $customer['id'])->where('status', 1)->first();
        $grid = new Grid(new WechatsUser);
        $grid->model()->where('platform_wechat_id', $wechat['id']);
        //$grid = new Grid(new WechatsUser());
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('用户ID'));
        $grid->column('name', __('用户名'));
        $grid->column('img', __('头像'))->image('', 100, 100);
        $grid->column('sex', __('性别'))->display(function ($sex) {
            switch ($sex) {
                case 1:
                    $style = 'color:blue;';
                    $sex_name = '男';
                    break;
                case 2:
                    $style = 'color:#ff7888;';
                    $sex_name = '女';
                    break;
                default:
                    $style = 'color:#0aff07;';
                    $sex_name = '外星人';
                    break;
            }
            return '<span style="'.$style.'">'.$sex_name.'</span>';
        });
        $grid->column('openid', __('Openid'));
        $grid->column('subscribe_time', __('关注时间'));
        $grid->column('subscribe_scene', __('关注来源'))->display(function ($subscribe_scene) {
            switch ($subscribe_scene) {
                case 'ADD_SCENE_SEARCH ':
                    $subscribe_scene_name = '公众号搜索';
                    break;
                case 'ADD_SCENE_ACCOUNT_MIGRATION ':
                    $subscribe_scene_name = '公众号迁移';
                    break;
                case 'ADD_SCENE_ACCOUNT_MIGRATION ':
                    $subscribe_scene_name = '名片分享';
                    break;
                case 'ADD_SCENE_QR_CODE   ':
                    $subscribe_scene_name = '扫描二维码';
                    break;
                case 'ADD_SCENE_PROFILE_LINK    ':
                    $subscribe_scene_name = '图文页内名称点击';
                    break;
                case 'ADD_SCENE_PROFILE_ITEM    ':
                    $subscribe_scene_name = '图文页右上角菜单';
                    break;
                case 'ADD_SCENE_PAID     ':
                    $subscribe_scene_name = '支付后关注';
                    break;
                case 'ADD_SCENE_WECHAT_ADVERTISEMENT    ':
                    $subscribe_scene_name = '微信广告';
                    break;
               case 'ADD_SCENE_OTHERS     ':
                    $subscribe_scene_name = '其他';
                    break;
                default:
                    $subscribe_scene_name = '其他';
                    break;
            }
            return '<span>'.$subscribe_scene_name.'</span>';
        });
        //$grid->column('created_at', __('创建时间'));
        //$grid->column('updated_at', __('更新时间'));
        $grid->tools(function ($tools) use($wechat){
            //$tools->append(new BatchWechatUser($wechat['id'],$wechat['customer_id']));
            $tools->append('<a href="/'.config('admin.route.prefix').'/wechat_user_list/updateUser?platform_wechat_id='.$wechat['id'].'&customer_id='.$wechat['customer_id'].'" class="btn btn-sm btn-default" title="更新用户">更新用户</a>');
        });
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
        });

        $grid->filter(function (Grid\Filter $filter) {
            $filter->like('name', '用户名');
            $filter->equal('openid', 'Openid');
        });
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableRowSelector();

        return $grid;
    }
    
    public function updateUser(){

    }
    
    
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $id = request()->input('platform_wechat_id');
        $customer_id = request()->input('customer_id');
        try{
            Artisan::call('novel:wechat-get-user-list', [ 'id' => $id ,'customer_id'=>$customer_id]);
            $msg = ['err_code'=>0, 'err_msg'=>'发送成功！'];
        } catch (\Exception $e) {
            $msg = ['err_code'=>$e->getLine(), 'err_msg'=>$e->getMessage()];
        }
        $msg=\GuzzleHttp\json_encode($msg);
        echo $msg;
    }
    
}
