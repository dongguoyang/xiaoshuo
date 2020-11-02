<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Wechat;
use App\Admin\Models\WechatConfig;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class WechatsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '运营公众号管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new Wechat);
        // 组员只能查询自己公众号信息
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if ($customer['pid']) {
            $grid->model()->where('customer_id', $customer['id']);
        }

        $grid->column('id', __('Id'));
        $grid->column('name', __('公众号名称'));
        $grid->column('img', __('公众号二维码'))->image('', 100, 100);
        $grid->column('appid', __('Appid'));
        $grid->column('appsecret', __('App秘钥'));
        $grid->column('redirect_uri', __('授权域名'));
        $grid->column('bak_host', __('备用授权域名'));
        $grid->column('type', __('公众号类型'))->display(function ($val){return Wechat::selectList(1, ['订阅号', '订阅号老', '服务号'])[$val];});
        $grid->column('公众号粉丝数')->display(function() {
            return $this->get_fans_number($this->id);
        });
        $grid->column('status', __('状态'))->switch(Wechat::switchStatus());
        $grid->column('updated_at', __('最近更新'));

        $grid->disableExport(); // 去掉导出按钮
        // 添加表格头部工具
        $grid->tools(function ($actions) {
            // $actions->prepend('<label class="btn btn-warning btn-sm"><a target="_blank" href="' . route('platform.auth') . '" title="去授权" style="color: #fff;"><i class="fa fa-podcast"></i> 去授权 </label></a>');
            // $actions->append('<label class="btn btn-warning btn-sm"><a target="_blank" href="' . route('platform.auth') . '" title="去授权" style="color: #fff;"><i class="fa fa-podcast"></i> 去授权 </label></a>');
            // $actions->append('<label class="confirm2doall btn btn-danger btn-sm" data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="批量删除" style="color: #fff;"><i class="fa fa-minus"></i> 批量删除 </label>');
        });
        // 添加表格行工具
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function ($actions) use($uri) {
            // $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            // $actions->append('<label data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="删除" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-trash"></i> 删除</label>');
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();// 去掉默认的id过滤器
            $filter->column(1/2, function ($filter) {
                $filter->like('name', '公众号');
                $filter->like('redirect_uri', '授权域名');
                $filter->equal('status', '状态')->select(['关闭', '开启']);
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal ('appid', 'Appid');
                $filter->like ('bak_host', '备用授权域名');
            });
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
        $show = new Show(Wechat::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('img', __('Img'));
        $show->field('appid', __('Appid'));
        $show->field('appsecret', __('Appsecret'));
        $show->field('token', __('Token'));
        $show->field('token_out', __('Token key'));
        $show->field('status', __('Status'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Wechat);

        if (request()->route()->getActionMethod() != 'edit') {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            $form->hidden('customer_id', __('所属客户'))->value($customer['id']);
        }

        $form->text('name', __('公众号名称'))->required();
        $form->image('img', __('公众号二维码'));
        $form->text('appid', __('Appid'))->required()->help('请先配置公众号网页授权域名');
        $form->text('appsecret', __('App秘钥'))->required();
        $form->text('service_token', __('令牌(Token)'))->required()->help('该处配置与公众号服务器配置一样');
        $form->text('service_aes_key', __('消息加解密密钥'))->required();
        $form->text('redirect_uri', __('授权域名'))->help('请先配置公众号网页授权域名；http://公众号网页授权域名')->required();
        $form->text('bak_host', __('备用授权域名'))->help('请先配置公众号网页授权域名；http://公众号网页授权域名');
        $form->switch('status', __('状态'))->default(Wechat::STATUS_1)->states(Wechat::switchStatus());
        $form->display('token', __('Token'));
        $form->display('token_out', __('Token失效时间'));
        $form->select('type', __('类型'))->options(Wechat::selectList(0, ['订阅号', '订阅号老', '服务号']))->required();

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
        //保存前回调
        $form->saving(function (Form $form) {
            $error = new MessageBag([
                'title'   => '出错了φ(≧ω≦*)♪',
                'message' => '公众号被他人使用或配置异常！',
            ]);

            if ((request()->route()->getActionMethod() == 'update')) {
                $id = request()->route()->parameter('wechat');
                if ($had = Wechat::where('appid', $form->appid)->first()) {
                    if ($had['id'] != $id)
                        return back()->with(compact('error'));
                }
            } else {
                //request()->route()->getActionMethod() == 'store'
                if (Wechat::where('appid', $form->appid)->first()) {
                    return back()->with(compact('error'));
                }
            }
        });

        //保存后回调
        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            if (WechatConfig::where('platform_wechat_id', $id)->first()) {
                return true;
            }
            $subscribe_msg_next = [
                'nid'=>["188603", "188620", "188624"],
                'title'=>["最强医圣", "你与岁月共朝夕", "勾人心弦"],
                'bottom'=>["需要人工帮助！添加", "http:\/\/wd.htagsfl.cn\/front\/#\/contact.html?cid=29&customer_id=29&dtype=2&jumpto=index", "人工客服"]
            ];
            $subscribe_msg_next = json_encode($subscribe_msg_next);

            $subscribe_msg_12h = [
                'nid'=>["188699", "188700", "188650", "188697"],
                'title'=>["继续阅读：《贾二胡和嫂子》", "推荐书籍", "推荐书籍", "推荐书籍"]
            ];
            $subscribe_msg_12h = json_encode($subscribe_msg_12h);

            $user_hpush = [
                's'=>[
                    [
                        "n"=>"188699",
                        "h"=>"8",
                        "t"=>null,
                        "d"=>"👆贾二虎和大嫂的故事",
                        "p"=>null
                    ],
                    [
                        "n"=>"188699",
                        "h"=>"8",
                        "t"=>null,
                        "d"=>"👆贾二虎和大嫂的故事",
                        "p"=>null
                    ],
                    [
                        "n"=>"188699",
                        "h"=>"8",
                        "t"=>null,
                        "d"=>"👆贾二虎和大嫂的故事",
                        "p"=>null
                    ],
                    [
                        "n"=>"188699",
                        "h"=>"8",
                        "t"=>null,
                        "d"=>"👆贾二虎和大嫂的故事",
                        "p"=>null
                    ],
                    [
                        "n"=>"188699",
                        "h"=>"8",
                        "t"=>null,
                        "d"=>"👆贾二虎和大嫂的故事",
                        "p"=>null
                    ]
                ],
                'switch'=>0
            ];
            $user_hpush = json_encode($user_hpush);
            
             $centre_menu = [
                'm'=>[
                    [
                        "name"=>"",
                        "novel_id"=>"",
                        "sections_id"=>0,
                        "url"=>"",
                    ],
                    [
                        "name"=>"",
                        "novel_id"=>"",
                        "sections_id"=>0,
                        "url"=>"",
                    ],
                    [
                        "name"=>"",
                        "novel_id"=>"",
                        "sections_id"=>0,
                        "url"=>"",
                    ],
                    [
                        "name"=>"",
                        "novel_id"=>"",
                        "sections_id"=>0,
                        "url"=>"",
                    ],
                    [
                        "name"=>"",
                        "novel_id"=>"",
                        "sections_id"=>0,
                        "url"=>"",
                    ]
                ],
                'switch'=>0
            ];
            $centre_menu = json_encode($centre_menu);

            $menu_list = [
                'type'=>1,
                'menu'=>[
                    'button'=>[
                        [
                            'type'=>"view",
                            'name'=>"阅读记录",
                            'url'=>"http:\/\/menuw.1.loansl.cn\/jiaoyu\/weivip?cid=1&customer_id=1&dtype=2&route=read_log"
                        ],
                        [
                            'type'=>"view",
                            'name'=>"精品书城",
                            'url'=>"http:\/\/menuw.1.loansl.cn\/jiaoyu\/weivip?cid=1&customer_id=1&dtype=2&route=index"
                        ],
                        [
                            'type'=>"view",
                            'name'=>"签到送币",
                            'url'=>"http:\/\/menuw.1.loansl.cn\/jiaoyu\/weivip?cid=1&customer_id=1&dtype=2&route=sign"
                        ]
                    ]
                ]
            ];
            $menu_list = json_encode($menu_list);
            $pushconf = [
                'day_read'=>"8",
                'first_recharge'=>"1",
                'sign'=>"1",
                'subs12h'=>0,
                'readed8h'=>0,
                'nopay'=>0
            ];
            $pushconf = json_encode($pushconf);
            $daily_push = [
                "h21"=>"1",
                "h6"=>0,
                "h12"=>0,
                "h18"=>0,
                "h23"=>0
            ];
            $daily_push = json_encode($daily_push);
            $search_sub = [
                "nid"=>["188627", "188624", "188623", "188622"],
                "snum"=>["1", "1", "1", "1"],
                "title"=>["点击继续阅读章节", "勾人心弦", "其乐融融", "纯净如雪"],
                "switch"=>"1"
            ];
            $search_sub = json_encode($search_sub);
            $data = [
                'customer_id'   => $form->model()->customer_id,
                'platform_wechat_id'   => $id,
                'subscribe_msg_next'=>$subscribe_msg_next,
                'subscribe_msg_12h'=>$subscribe_msg_12h,
                'menu_list'=>$menu_list,
                'pushconf'=>$pushconf,
                'daily_push'=>$daily_push,
                'search_sub'=>$search_sub,
                'user_hpush'=>$user_hpush,
                'centre_menu'=>$centre_menu,
            ];
            WechatConfig::create($data);
        });

        return $form;
    }
}
