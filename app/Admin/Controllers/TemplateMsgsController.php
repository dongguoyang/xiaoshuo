<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/4
 * Time: 15:19
 */

namespace App\Admin\Controllers;
use App\Admin\Extensions\Grids\Buttons\DeleteAll;
use App\Admin\Models\Wechat;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Admin\Models\TemplateMsg;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Console\Commands\SendTemplateForWechat;
use App\Admin\Extensions\Tools\BatchWechatUser;

class TemplateMsgsController extends AdminController
{

    protected $title="模板消息管理";
    public function grid(){
        $grid=new Grid(new TemplateMsg);
        $grid->model()->orderBy('id', 'desc');
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if(Admin::user()['pid']){
            $grid->model()->where('customer_id',$customer->id);
        } else {
            $grid->column('id', __('ID'));
            $grid->column('customer.name', __('所属客户'));
            $grid->column('platformWechat.app_name', __('所属公众号'));
        }
        $grid->column('title',__('标题'));
        $grid->column('template_id',__('公众号的模板ID'));
        //$grid->column('content',__('内容信息json'));
        $grid->column('type',__('类型'))->display(function ($val){ return TemplateMsg::selectList(1)[$val]; });

        $grid->column('status',__('状态'))->switch(TemplateMsg::switchStatus());
        $grid->column('created_at',__('创建时间'));
        $grid->actions(function ($actions) {
            $key= $actions->getKey();
            // 去掉查看
            $actions->disableView();
            $actions->disableEdit();
            $actions->append('<a href="/administrator/template_msgs/'.$key.'/edit" class=" btn btn-xs btn-primary">修改</a>');
            $actions->append(new DeleteAll($actions->getKey())); //删除
            if (in_array($actions->row['type'], [1, 2, 5]) && $actions->row['status'] == 1) {
                $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" data-url="/'.config('admin.route.prefix').'/template_msgs/sendnow" class="confirm2do btn btn-xs btn-warning" title="立即发送"><i class="fa fa-send"></i> 立即发送</a>');
                //$actions->append(new BatchWechatUser());
            }
        });
        $grid->filter(function($filter){

            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->equal('platform_wechat_id', '平台ID');

        });


        return $grid;
    }
    protected function form(){
         $form=new Form(new TemplateMsg);

         $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if(request()->route()->getActionMethod() === 'edit') {
            $input = request()->route()->parameters();
            $result = TemplateMsg::find($input['template_msg']);
            if ($result->customer_id != $customer->id && !Admin::user()->isAdministrator()) {
                throw new \Exception('权限不足!!', 0);
            }
        } else {
            $form->hidden('customer_id','客户ID')->default($customer->id)->readonly();
            $plamt=$this->selectPlat($customer->id);
            $form->hidden('platform_wechat_id','公众号ID')->default($plamt->id)->readonly();
        }
        $form->text('title',__('标题'))->required();
        $form->text('template_id',__('模板ID'))->required();
        $form->text('url',__('链接地址'))->required();
        // $form->select('type',__('类型'))->options(TemplateMsg::selectList());
        $form->switch('status',__('状态'))->states(TemplateMsg::switchStatus());


        $form->table('content',__('内容'),function ($table){
            $table->text('keyword',__('keyword值'));
            $table->text('value',__('value值'));
            $table->color('color',__('颜色'))->default('#ccc');
        })->help('自动替换字段{type} = 类型、{status} = 状态、{money} = 金额、{username} = 用户名；以上字段推送时会被自动替换成对应值！');
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            //$tools->disableList();
        });

         return $form;
    }
    //获取平台信息
    public function selectPlat($userid){ //获取平台信息
        $data= Wechat::where('customer_id',$userid)->first();
        return $data;
    }

    /** 删除
     * @param $id plateform-id
     * @throws \Exception
     */
    public function delete($key){
        try {
            if ($key != 1) {
                throw  new \Exception('地址有误!', 0);
            }
            $id = request()->input('id');
            $result = TemplateMsg::find($id);
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if ($result->customer_id != $customer->id && !Admin::user()->isAdministrator()) {
                throw  new \Exception('权限不足!', 0);
            }
            if ($result->delete()) {
                $msg=['code'=>200,'msg'=>'删除成功'];
            } else {
                $msg=['code'=>100,'msg'=>'删除失败'];
            }
        }catch (\Exception $e){
            $msg=['code'=>$e->getMessage(),'msg'=>$e->getMessage()];
        }
        $msg=\GuzzleHttp\json_encode($msg);
        echo $msg;
    }
    /**
     * 立即发送模板消息
     */
    public function SendNow() {
        $id = request()->input('id');

        if (!$id) {
            $msg=['code'=>2000,'msg'=>'参数异常'];
        }
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $template = TemplateMsg::where('id', $id)->where('customer_id', $customer['id'])->first();
        if (!$template) return ['status'=>2000, 'msg'=>'模板异常！'];
        try{
            Artisan::call('novel:send-template-for-wechat', [ 'id' => $id ]);
            return ['err_code'=>0, 'err_msg'=>'发送成功！'];
        } catch (\Exception $e) {
            return ['err_code'=>$e->getLine(), 'err_msg'=>$e->getMessage()];
        }
    }



}