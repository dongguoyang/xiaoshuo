<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grids\Filters\TimestampBetween;
use App\Admin\Models\User;
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

class UserController extends AdminController
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
        $grid = new Grid(new User);
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('用户ID'));
        $grid->column('name', __('用户名'));
       /* $grid->column('email', __('邮箱'));
        $grid->column('invite_code', __('邀请码'));
        $grid->column('parent1', __('师父级'))->display(function ($parent1_id) {
            $parent1 = User::find($parent1_id);
            return $parent1 ? $parent1->name : '';
        });
        $grid->column('parent2', __('祖师级'))->display(function ($parent2_id) {
            $parent2 = User::find($parent2_id);
            return $parent2 ? $parent2->name : '';
        });
        $grid->column('child_num', __('邀请人数(直接)')); // “徒弟”数量
        $grid->column('child2_num', __('邀请人数(间接)')); // “徒孙”数量*/
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
        $grid->column('subscribe', __('关注公众号？'))->switch(User::switchStatus(0, ['否', '是']));
        $grid->column('pay_openid', __('支付Openid'));
        $grid->column('openid', __('Openid'));
        //$grid->column('unionid', __('Unionid'));
        $grid->column('customer', __('商家'))->display(function ($customer) {
            return $customer['company'] ? '【'.$customer['company'].'】'.$customer['name'] : ($customer ? $customer['name'] : '');
        });
        //$grid->column('platformWechat.app_name', __('当前开放平台微信'));
        $grid->column('sign_day', __('签到天数'));
        $grid->column('recharge_money', __('充值金额'))->fen2yuan();
        $grid->column('balance', __('账户余额（书币）'));
        $grid->column('vip_end_at', __('会员截止时间'));
        /*$grid->column('extendLink', __('来路'))->display(function ($extendLink) {
            return '<a target="_blank" href="'.$extendLink['title'].'">'.$extendLink['link'].'</a>';
        });*/
        $grid->column('created_at', __('创建时间'));
        $grid->column('updated_at', __('更新时间'));

        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            // $actions->append('<label data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="删除" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-trash"></i> 删除</label>');
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
        });

        $grid->filter(function (Grid\Filter $filter) {
            $filter->like('name', '用户名');
            /*$filter->equal('email', '邮箱');
            $filter->equal('invite_code', '邀请码');
            $filter->equal('parent1', '师父级ID');
            $filter->equal('parent2', '祖师级ID');*/
            $filter->equal('openid', 'Openid');
            $filter->equal('pay_openid', '支付Openid');
            //$filter->equal('unionid', 'Unionid');

            /*if($this->customer()->isAdministrator()) {
                $filter->equal('customer_id', '商家')->select(function () {
                    return Optional(Customer::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
                })->ajax('/administrator/api/customers');
            }

            $filter->equal('platform_wechat_id', '当前开放平台微信')->select(function () {
                return Optional(PlatformWechat::orderBy('id', 'asc')->limit(10)->get())->pluck('app_name', 'id');
            })->ajax('/administrator/api/platform-wechats');

            $filter->equal('extend_link_id', '来路')->select(function () {
                return Optional(ExtendLink::orderBy('id', 'asc')->limit(10)->get())->pluck('title', 'id');
            })->ajax('/administrator/api/extend-links');*/

            $filter->equal('subscribe', '关注公众号？')->select([0 => '否', 1 => '是']);
            $filter->use(new TimestampBetween('created_at', '注册时间'))->datetime();
            $filter->equal('first_account', '主账户ID');
        });
        $grid->disableExport();
        $grid->disableRowSelector();

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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('用户ID'));
        $show->field('name', __('用户名'));
        $show->field('email', __('邮箱'));
        $show->field('invite_code', __('邀请码'));
        /*$show->field('parent1', __('师父级'))->as(function ($parent1_id) {
            $parent1 = User::find($parent1_id);
            return $parent1 ? $parent1->name : '';
        });;
        $show->field('parent2', __('祖师级'))->as(function ($parent2_id) {
            $parent2 = User::find($parent2_id);
            return $parent2 ? $parent2->name : '';
        });
        $show->field('child_num', __('邀请人数(直接)'));
        $show->field('child2_num', __('邀请人数(间接)'));*/
        $show->field('img', __('头像'))->image('', 100, 100);
        $show->field('sex', __('性别'))->as(function ($sex) {
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
        });;
        $show->field('subscribe', __('关注公众号？'))->as(function ($subscribe) {
            return $subscribe > 0 ? '是' : '否';
        });
        $show->field('openid', __('Openid'));
        //$show->field('unionid', __('Unionid'));
        $show->field('customer', __('商家'))->as(function ($customer) {
            return $customer['company'] ? '【'.$customer['company'].'】'.$customer['name'] : $customer['name'];
        });;
        //$show->field('platformWechat.app_name', __('当前开放平台微信'));
        $show->field('sign_day', __('签到天数'));
        $show->field('recharge_money', __('充值金额'))->fen2yuan();
        $show->field('balance', __('账户余额（书币）'));
        $show->field('vip_end_at', __('会员截止时间'));
        /*$show->field('extendLink', __('来路'))->as(function ($extendLink) {
            return '<a target="_blank" href="'.$extendLink['title'].'">'.$extendLink['link'].'</a>';
        });*/
        $show->field('created_at', __('创建时间'));
        $show->field('updated_at', __('更新时间'));

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
        $form = new Form(new User);

        $form->text('name', __('用户名'))->required();
        /*$form->text('name', __('用户名'))->rules([
            'required', 'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u',
            'between:2,64',
            Rule::unique('users')->where(function ($query) use ($form) {
                $query->where('id', '<>', $form->model()->id);
            })
        ], ['regex' => '用户名只能由汉字、字母、数字和下划线组成']);
        $form->email('email', __('邮箱'))->rules([
            'nullable',
            'email',
            Rule::unique('users')->where(function ($query) use ($form) {
                $query->where('id', '<>', $form->model()->id);
            })
        ]);
        $form->password('password', __('密码'))->rules([
            'nullable',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*?[#?!@$%^&*\.\|\~\(\)\-\_\+\=\{\}\[\]:;\'\"\/\\\,])[A-Za-z\d#?!@$%^&*\.\|\~\(\)\-\_\+\=\{\}\[\]:;\'\"\/\\\,]{6,18}$/'
        ], ['regex' => '密码中含有非法字符']);*/
        $form->number('invite_code', __('邀请码'))->pattern('[0-9]{10}')->default('0000000000')->disable();
        /*$form->number('parent1', __('师父级ID'));
        $form->number('parent2', __('祖师级ID'));
        $form->number('child_num', __('邀请人数(直接)'));
        $form->number('child2_num', __('邀请人数(间接)'));*/
        $form->display('img', __('头像'));//->uniqueName()->move('user/avatar')->rules(['required', 'image']);
        $form->radio('sex', __('性别'))->options(['1' => '男', '2'=> '女'])->default(2);
        $form->switch('subscribe', __('关注公众号？'))->states(User::switchStatus(0, ['否', '是']))->default(User::STATUS_0)->rules('required');
        $form->display('openid', __('Openid'));
        //$form->text('unionid', __('Unionid'));
        /*if(!$this->customer()->isAdministrator()) {
            $form->select('customer_id', __('商家ID'))->options(function ($customer_id) {
                $customer = Customer::find($customer_id);
                if ($customer) {
                    return [$customer->id => $customer->name];
                }
            })->ajax('/administrator/api/customers')->default($this->customer()->id)->readonly();
        } else {
            $form->select('customer_id', __('商家ID'))->options(function ($customer_id) {
                $customer = Customer::find($customer_id);
                if ($customer) {
                    return [$customer->id => $customer->name];
                }
            })->ajax('/administrator/api/customers')->default($this->customer()->id);
        }
        $form->select('platform_wechat_id', __('当前开放平台微信ID'))->options(function ($platform_wechat_id) {
            $platform_wechat = PlatformWechat::find($platform_wechat_id);
            if ($platform_wechat) {
                return [$platform_wechat->id => $platform_wechat->app_name];
            }
        })->ajax('/administrator/api/platform-wechats');*/
        $form->number('sign_day', __('签到天数'));
        $form->fencurrency('recharge_money', __('充值金额(元)'))->disable();
        $form->number('balance', __('账户余额（书币）'))->min(0);
        $form->date('vip_end_at', __('会员截止时间'))->format('YYYY-MM-DD')->default('1979-01-01');
        /*$form->select('extend_link_id', __('来路'))->options(function ($extend_link_id) {
            $extend_link = ExtendLink::find($extend_link_id);
            if ($extend_link) {
                return [$extend_link->id => $extend_link->title];
            }
        })->ajax('/administrator/api/extend-links');*/

        /*$form->saving(function (Form $form) {
            // 主要检查当为超级管理员时，检查数据关系是否对应
            $customer_id = $form->customer_id;
            if($customer_id) {
                if(!$this->customer()->isAdministrator() && $customer_id != $this->customer()->id) {
                    $error = new MessageBag([
                        'title'   => '家花不如野花香？！',
                        'message' => '对不起，你无权设置平台微信',
                    ]);
                    return back()->with(compact('error'));
                } else {
                    // 平台微信
                    $platform_wechat_id = $form->platform_wechat_id;
                    $platform_wechat = $platform_wechat_id ? PlatformWechat::find($platform_wechat_id) : [];
                    if($platform_wechat && $platform_wechat['customer_id'] != $customer_id) {
                        $error = new MessageBag([
                            'title'   => '平台微信设置错误',
                            'message' => '平台微信非对应商家所有',
                        ]);
                        return back()->with(compact('error'));
                    } elseif(empty($platform_wechat)) {
                        $error = new MessageBag([
                            'title'   => '请设置平台微信',
                            'message' => '请设置平台微信',
                        ]);
                        return back()->with(compact('error'));
                    }
                    // 来路
                    $extend_link_id = $form->extend_link_id;
                    $extend_link = $extend_link_id ? ExtendLink::find($extend_link_id) : [];
                    if($extend_link && $extend_link['customer_id'] != $customer_id) {
                        $error = new MessageBag([
                            'title'   => '来路设置错误',
                            'message' => '来路非对应商家所有',
                        ]);
                        return back()->with(compact('error'));
                    } elseif(empty($extend_link)) {
                        $error = new MessageBag([
                            'title'   => '请设置来路',
                            'message' => '请设置来路',
                        ]);
                        return back()->with(compact('error'));
                    }
                }
            } else {
                $error = new MessageBag([
                    'title'   => '请先选择商家',
                    'message' => '请先选择商家',
                ]);
                return back()->with(compact('error'));
            }
        });*/
        $form->saved(function (Form $form) {
            $user_id = $form->model()->id;
            $key = config('app.name') . 'user_info_' . $user_id;
            if (Cache::has($key)) Cache::forget($key);
            $user = User::select(['first_account', 'customer_id'])->find($user_id);
            $key = config('app.name') . 'sub_user_info_'.$user['first_account'] .'_'.$user['customer_id'];
            if (Cache::has($key)) Cache::forget($key);
        });

        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
        });
        $form->footer(function (Form\Footer $footer) {
            // 去掉`重置`按钮
            $footer->disableReset();

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });

        return $form;
    }
}
