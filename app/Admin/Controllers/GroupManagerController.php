<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/6
 * Time: 12:03
 */

namespace App\Admin\Controllers;


use App\Admin\Extensions\Grids\Buttons\DeleteAll;
use App\Admin\Models\GroupManager;
use App\Admin\Models\Platform;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\RuleUsers;
use App\Admin\Models\Wechat;
use App\Admin\Models\WechatConfig;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Support\Facades\Artisan;
use Mockery\Exception;

class GroupManagerController extends AdminController
{

    protected $title="组员管理";

    protected function grid(){
        return redirect('/' . config('admin.route.prefix') . '/customers');

        $grid=new Grid(new GroupManager());
        $customer= \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $grid->model()->where('pid','=',$customer->id);
        $grid->model()->where('status','=',1);
        $grid->column('id',__('用户Id'));
        $grid->column('username',__('账户'));
        $grid->column('name',__('昵称'));
        $grid->column('created_at',__('创建时间'));
        $grid->actions(function ($actions) {
            // 去掉查看
            $actions->disableView();
            $actions->disableEdit();
            //$actions->append('<a href="/administrator/template_msgs/'.$key.'/edit" class=" btn btn-xs btn-primary">修改</a>');
            $actions->append(new DeleteAll($actions->getKey(),'踢出')); //删除

        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('username', '账户');
            $filter->like('name', '昵称');
        });
        return $grid;

   }
   protected function form(){
       $customer= \Illuminate\Support\Facades\Auth::guard('admin')->user();

       $form=new Form(new GroupManager());
       //$form->text('pid','代理组长')->value($customer->id)->readonly();
       $form->select('group_id','代理组长')->options(function ($id) {
           return optional((new GroupManager())->where('group_id', '1')->where('status',1)->orWhere('id','1')->get())->pluck('name', 'id');
         }
       );
       /*$form->select('role_id','代理权限')->options(function ($id) {
            return optional((new Role())->get())->pluck('name', 'id');
            }
       );*/
       $form->display('pid_name','代理名称')->value($customer->name);
       $form->text('appid','公众号APPID')->creationRules('required',['required'=>'必须填APPID']);

       $form->text('username',__('用户账号'))->creationRules('required|min:4|unique:customers',
           [
               'required'=>'请填写用户账号',
               'unique'=>'该账号已经存在',
               'min'   => '用户账号至少八位',
           ]);
       $form->password('password',__('用户密码'))->default('zmr000000');
       $form->text('name',__('昵称'))->creationRules('required|min:6',
           [
               'required'=>'请填写用户昵称',
               'min'   => '昵称不能少于6个字符',
           ]);
       $form->mobile('tel',__('手机'));
       $form->text('wexin',__('联系微信'))->creationRules('required',['required'=>'必须填微信']);
       $form->text('truename',__('真实姓名'));
       $form->text('company',__('公司名称'))->creationRules('required',['required'=>'请输入公司名称']);
       $form->distpicker(['province_id'=>'省','city_id'=>'市区','district_id'=>'区|县城'])->placeholder('请输入。。。'); //必须加placeholder 不然要报错。。
       $form->text('addr',__('地区'))->creationRules('required',['required'=>'请输入地区']);
       $form->email('email',__('邮箱'));
       $form->text('qq',__('QQ'));

       $form->footer(function ($footer) {

           // 去掉`重置`按钮
           //$footer->disableReset();

           // 去掉`提交`按钮
          // $footer->disableSubmit();

           // 去掉`查看`checkbox
           $footer->disableViewCheck();

           // 去掉`继续编辑`checkbox
           $footer->disableEditingCheck();

           // 去掉`继续创建`checkbox
           $footer->disableCreatingCheck();

       });
       $form->submitted(function (Form $form) {
           $form->ignore(['appid']);
       });
       //dd($form->appid);
       $form->ignore(['role_id']);
       $form->saved(function (Form $form) use ($customer){
           $customer_id=$form->model()->id;
           //$role=RuleUsers::where('user_id',$customer->id)->first(); //用户权限
           $create=new RuleUsers();
           $create->role_id = 3;
           $create->user_id=$customer_id;
           $create->save();  //权限增加
           // 新建则初始化统计数据
           if(true /*$form->isCreating()*/) {
               $exit_code = Artisan::call('statistics:init', [
                   'customer_id' => $form->model()->id,
               ]);
           }
       });


       return $form;
   }

    /** 踢出组员
     * @param $check
     */
   public function deleteGroupUser(){
       try{
           $id=request()->input('id');
           $group_user=GroupManager::find($id);
           $pid=$group_user->pid; //获取pid
           $customer= \Illuminate\Support\Facades\Auth::guard('admin')->user(); //登陆者信息
           if($customer->id !=$pid && $customer->id != 1){
               throw new \Exception('错误！！该用户不是你的组员',0);
           }
           $group_user->pid=0;
           $group_user->status=0;
           $group_user->save();
           $group_user->refresh();//刷新mode
           //RuleUsers::where('user_id',$id)->delete();
           WechatConfig::where('customer_id',$id)->delete();
           Wechat::where('customer_id',$id)->update(['status'=>0]);
           $msg=["code"=>200,"msg"=>"踢出成功！！！"];
       }catch (\Exception $e){
            $msg=["code"=>$e->getCode(),"msg"=>$e->getMessage()];
       }
       $msg=\GuzzleHttp\json_encode($msg);
       return $msg;
   }





}