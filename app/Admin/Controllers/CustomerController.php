<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grids\Buttons\DeleteAll;
use App\Admin\Models\Customer;
use App\Admin\Models\GetcashLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\Field\Table;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Logics\Traits\ApiResponseTrait;

class CustomerController extends AdminController
{
    
    use ApiResponseTrait;
    protected $title = '组员管理';
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
        $grid = new Grid(new Customer);
        if($pid = $this->customer()->group_id){
            if($pid == 1){
                $grid->model()->where('status',1)->where('group_id',$this->customer()->id)->orWhere('id',$this->customer()->id);
            }else{
                $grid->model()->where('status',1)->where('group_id',$pid)->orWhere('id',$pid);
            }
        }else{
            $grid->model()->where('status',1);
        }
        //$grid->model()->where('id', 1)->orWhere('pid', 1); 
        $grid->column('id', __('ID'));
        $grid->column('avatar', __('微信二维码'))->image('', 50, 50);
        //$grid->column('username', __('用户名'));
        $grid->column('name', __('昵称'))->editable();
        //$grid->column('truename', __('真实姓名'));
        $grid->column('gCustomer.name', __('上级'));
        /*$grid->column('pid', __('上级'))->display(function ($pid) {
            if($pid) {
                $parent = Customer::find($pid);
                return $parent ? $parent['name'] : '<span style="color:red">不存在</span>';
            }
            return '<span style="color:red">不存在</span>';
        });*/
        $grid->column('balance', __('余额'))->fen2yuan();
        //$grid->column('recharge_money', __('总收入'))->fen2yuan();
        // $grid->column('cash_money', __('已提现'))->fen2yuan();
        $grid->column('总收入')->display(function() {
            //return $this->pid;
            return $this->recharged_sum(1,$this->id == 1?0:$this->id);
        })->fen2yuan();
        $grid->column('今日收入')->display(function() {
            return $this->recharge_money(1,$this->id == 1?0:$this->id);
        })->fen2yuan();
        /*$grid->column('cashing_num', __('提现次数'));
        $grid->column('cashed_num', __('成功提现次数'));
        $grid->column('tel', __('电话'));
        $grid->column('qq', __('QQ'));
        $grid->column('wexin', __('微信'));
        $grid->column('company', __('公司'));
        $grid->column('addr', __('地址'));
        $grid->column('email', __('邮箱'));
        $grid->column('bank_name', __('开户行/支付宝'))->display(function ($bank_name) {
            return $bank_name == '支付宝' ? '<span style="color:#0e9edc;">支付宝</span>' : $bank_name;
        });
        $grid->column('bank_info', __('开户行地址'));
        $grid->column('bank_no', __('银行卡号/支付宝账号'));
        $grid->column('web_info', __('网站信息'))->view('admin.customer.webinfo');
        $grid->column('web_tpl', __('网站模板'));*/
        $customer = $this->customer();
        $grid->column('status', __('状态'))->display(function ($status, $column) use ($customer) {
            if($this->id != 1 && $this->id != $customer->id && ($customer->id == 1 || $customer->id == $this->pid)) {
                return $column->switch([
                    'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
                    'on' => ['value' => 1, 'text' => '启用', 'color' => 'success']
                ]);
            } else {
                return $column->display(function () use ($status) {
                    return $status > 0 ? '<span style="color:#36ef08;">启用</span>' : '<span style="color:#ef0837;">禁用</span>';
                });
            }
        });
        /*
        if($this->customer()->isAdministrator() || $this->customer()->id == 1) {
            $grid->column('status', __('状态'))->switch([
                'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
                'on' => ['value' => 1, 'text' => '启用', 'color' => 'success']
            ]);
        } else {
            $grid->column('status', __('状态'))->display(function ($status) {
                return $status > 0 ? '<span style="color:#36ef08;">启用</span>' : '<span style="color:#ef0837;">禁用</span>';
            });
        }
        */
        $grid->column('updated_at', __('更新于'));
        // $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('name', __('昵称'));
            /*$filter->like('truename', __('真实姓名'));
            $filter->like('bank_no', __('银行卡号/支付宝账号'));*/
            $filter->equal('pid', '上级')->select(function () {
                return Optional(Customer::orderBy('id', 'asc')->where('pid', '>', 0)->limit(10)->get())->pluck('name', 'id');
            })->ajax('/administrator/api/customers');
            $filter->equal('status', '状态')->radio([
                0   =>  '禁用',
                1   =>  '启用'
            ]);
        });
        $grid->disableCreateButton();
        $grid->disableExport();
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            // $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            if (Admin::user()['id'] != $actions->getKey()) {
                $actions->append('<a href="'.$uri.'/changelogin?id=' . $actions->getKey() . '" class="grid-row-edit btn btn-xs btn-primary" title="切换账户"><i class="fa fa-edit"></i> 切换账户</a>');
                $actions->append('<br><label data-url="/'.config('admin.route.prefix').'/customers/withdrawalLog?id=' . $actions->getKey() . '" data-status="1" data-id="' . $actions->getKey() . '" title="提现" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-refresh"></i> 提现</label>');
                if (!Admin::user()['pid']) {
                    $actions->append(new DeleteAll($actions->getKey(),'踢出')); //删除
                }
            }
            //$actions->append('<a href="/common/getlongqrcode?customer_id=' . $actions->getKey() . '&sc=searchsub" class="grid-row-edit btn btn-xs btn-info" target="_blank" title="获取带参数二维码"><i class="fa fa-qrcode"></i> 参数二维码</a>');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
            $customer = $this->customer();
            if(!$customer->pid || $customer->pid == $customer->id) {
                $tools->append('<a href="/' . config('admin.route.prefix') . '/group_manager/create" class="grid-row-edit btn btn-sm btn-success" title="添加组员"><i class="fa fa-plus"></i> 添加组员</a>');
            }
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Customer::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('avatar', __('微信二维码'))->image('', 50, 50);
        $show->field('username', __('用户名'));
        $show->field('name', __('昵称'));
        $show->field('truename', __('真实姓名'));
        $show->field('pid', __('上级'))->as(function ($pid) {
            if($pid) {
                $parent = Customer::find($pid);
                return $parent ? $parent['name'] : '不存在';
            }
            return '不存在';
        });
        $show->field('balance', __('余额'))->fen2yuan();
        $show->field('recharge_money', __('总收入'))->fen2yuan();
        $show->field('cash_money', __('已提现'))->fen2yuan();
        $show->field('cashing_num', __('提现次数'));
        $show->field('cashed_num', __('成功提现次数'));
        $show->field('tel', __('电话'));
        $show->field('qq', __('QQ'));
        $show->field('wexin', __('微信'));
        $show->field('company', __('公司'));
        $show->field('addr', __('地址'));
        $show->field('email', __('邮箱'));
        $show->field('bank_name', __('开户行/支付宝'))->as(function ($bank_name) {
            return $bank_name == '支付宝' ? '支付宝' : $bank_name;
        });
        $show->field('bank_info', __('开户行地址'));
        $show->field('bank_no', __('银行卡号/支付宝账号'));
        $show->field('web_info', __('网站信息'))->as(function ($web_info) {
            if($web_info) {
                return '网站名称：'.$web_info['title'].'; 
                        客服电话：'.$web_info['tel'].'; 
                        微信客服：'.$web_info['weixin'].'; 
                        工作时间：'.$web_info['service'].'; ';
            } else {
                return '';
            }
        });
        $show->field('web_tpl', __('网站模板'));
        $show->field('status', __('状态'))->as(function ($status) {
            return $status > 0 ? '启用' : '禁用';
        });
        $show->field('created_at', __('创建于'));
        $show->field('updated_at', __('更新于'));

        $show->panel()
            ->tools(function (Show\Tools $tools) {
                $tools->disableDelete();
            });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Customer);

        $form->fieldset('基本信息', function (Form $form) {
            $form->image('avatar', __('微信二维码'));
            $form->text('username', __('用户名'))->rules([
                'required',
                'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_[:punct:]]+$/u',
                'between:2,64',
                Rule::unique('customers')->where(function ($query) use ($form) {
                    $query->where('id', '<>', $form->model()->id);
                })
            ], ['regex' => '用户名只能由汉字、字母、数字、.和下划线组成']);
            $form->text('name', __('昵称'))->rules([
                'required',
                'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_[:punct:]]+$/u',
                'between:2,64',
                Rule::unique('customers')->where(function ($query) use ($form) {
                    $query->where('id', '<>', $form->model()->id);
                })
            ], ['regex' => '昵称只能由汉字、字母、数字、.和下划线组成']);
            /*$form->text('truename', __('真实姓名'))->rules([
                'nullable',
                'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_·[:punct:]]+$/u',
                'between:2,64'
            ], ['regex' => '真实姓名只能由汉字、字母、数字和下划线和·组成']);*/
            $form->password('password', __('密码'))->rules('nullable|confirmed');
            $form->password('password_confirmation', __('重复密码'))->rules('nullable')
                ->default(function (Form $form) {
                    return $form->model()->password;
                });
            $form->ignore(['password_confirmation']);
            $form->hidden('pid', __('父级'));
            /*if($form->isCreating()) {
                $form->select('pid', __('上级'))->ajax('/administrator/api/customers')->default(0);
            } else {
                $form->select('pid', __('上级'))->options(function ($pid) {
                    $parent = Customer::find($pid);
                    if ($parent) {
                        return [$parent->id => $parent->name];
                    }
                })->ajax('/administrator/api/customers');
            }*/
            $form->switch('status', __('状态'))->states([
                'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
                'on' => ['value' => 1, 'text' => '启用', 'color' => 'success']
            ])->default(1)->required();
        });

        $form->fieldset('账户数据', function (Form $form) {
            $form->fencurrency('balance', __('余额'))->readonly();
            $form->fencurrency('recharge_money', __('总收入'))->readonly();
            $form->fencurrency('cash_money', __('已提现'))->readonly();
            $form->number('cashing_num', __('提现次数'))->readonly();
            $form->number('cashed_num', __('成功提现次数'))->readonly();
            /*$form->text('bank_name', __('开户行/支付宝'))->default('支付宝')->help('如果是支付宝，请填写支付宝；若为银行账户，请填写对应的开户行。');
            $form->text('bank_info', __('开户行地址'));
            $form->text('bank_no', __('银行卡号/支付宝账号'));*/
        });

        $form->fieldset('额外数据', function (Form $form) {
            $form->mobile('tel', __('手机号'))->rules([
                'nullable',
                'regex:/^[1]([3-9])[0-9]{9}$/'
            ], ['regex' => '手机号不符合规范']);
            $form->text('qq', __('QQ'))->rules([
                'nullable',
                'integer'
            ]);
            $form->text('wexin', __('微信'));
            $form->email('email', __('邮箱'));
            $form->text('company', __('公司'));
            $form->text('addr', __('地址'));
        });

        $form->fieldset('网站配置', function (Form $form) {
            $form->embeds('web_info', __('网站信息'), function (Form\EmbeddedForm $form) {
                $form->text('title', '网站标题')->default('网站标题');
                $form->text('tel', '预留电话')->default('预留电话');
                $form->text('weixin', '预留微信')->default('预留微信');
                $form->text('service', '服务说明')->default('服务说明');
            });
            $form->text('web_tpl', __('网站模板'));
        });

        $form->saving(function (Form $form) {
            // 检查权限
            if(/*(!$this->customer()->isAdministrator() && $form->model()->id != $this->customer()->id) ||*/ ($this->customer()->pid > 0 && $this->customer()->id != $form->model()->id)) {
                $error = new MessageBag([
                    'title'   => '无权操作',
                    'message' => '对不起，你无权进行此项操作',
                ]);
                return back()->with(compact('error'));
            }
        });
        $form->saved(function (Form $form) {
            // 新建则初始化统计数据
            if(true /*$form->isCreating()*/) {
                $exit_code = Artisan::call('statistics:init', [
                    'customer_id' => $form->model()->id,
                ]);
            }
        });

        $form->tools(function (Form\Tools $tools) {
            // $tools->disableList();// 去掉`列表`按钮
            $tools->disableDelete();// 去掉`删除`按钮
            $tools->disableView();// 去掉`查看`按钮
        });
        $form->footer(function ($footer) {
            $footer->disableViewCheck();// 去掉`查看`checkbox
            $footer->disableEditingCheck();// 去掉`继续编辑`checkbox
            $footer->disableCreatingCheck();// 去掉`继续创建`checkbox
        });

        return $form;
    }


    /**
     *
     * 切换登录账户
     *
     * @return mixed
     */
    public function changeLogin(Request $request)
    {
        $customer = Customer::find($request->id);

        Auth::guard('admin')->login($customer, true);

        return redirect()->intended(config('admin.route.prefix'));
    }
    
    
    /*
     * 生成提现记录
     */
    public function withdrawalLog(Request $request){
        $customer = Customer::find($request->id);
        if($customer->balance){
            $log = [
                'name'  => $customer->name,
                'user_id'  => $customer->id,
                'money'  => $customer->balance,
                'status'  => 0,
                'remark'  => $customer->name . ' - 提现',
                'mch_id'  => -1,
            ];
            try{
                DB::beginTransaction();
                //1.添加提现记录
                if (!$log = GetcashLog::create($log)) {
                    throw new \Exception('提现记录添加失败！', 2000);
                }
                //2.扣除提现金额 更新提现次数和提现总金额
                $data = [
                    'balance'=>0,
                    //'cash_money'=>$customer->cash_money+$log['money'],
                    'cashing_num'=>$customer->cashing_num + 1,
                ];
                if (!Customer::where('id', $log['user_id'])->update($data)) {
                    throw new \Exception('用户余额更新失败！', 2000);
                }
                DB::commit();
                return $this->result(['提现成功！'], 0);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage() . '___'. '___user_id = '.$log['user_id'].'___money = '.$log['money']);
                return $this->result([], 2000, $e->getMessage());
            }
        }else{
            return $this->result(['提现失败'], 2000, '没有可提现余额');
        }
    }
}
