<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Domain;
use App\Admin\Models\Novel;
use App\Admin\Models\WechatQrcode;
use App\Admin\Models\Wechat;
use App\Libraries\Images\Qrcode;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Traits\OfficialAccountTrait;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Storage;

class WechatQrcodesController extends AdminController
{
    use OfficialAccountTrait;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '带参二维码管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WechatQrcode);
        $domainRep = new DomainRepository();

        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('customer.username', __('公众号'));
        $grid->column('out_link', __('外推链接'))->display(function () use ($domainRep){
            // http://pgsw.cxfw.etinvji.cn/home/out-section.html#novel_id=188027&section=3&subscribe=5&customer_id=8&cid=8
            $link = $domainRep->randOne(3, $this->customer_id) . route('jumpto', [
                    'route'         =>'section_out',
                    'wechat_qrcode' =>$this->id,
                    'novel_id'      =>$this->novel_id,
                    'section'       =>$this->section,
                    'subscribe'     =>$this->section + 1,
                    'customer_id'   => $this->customer_id,
                    'cid'           => $this->customer_id,
                ], false);
            return "<span data-content='". $link ."' target='_blank' class='copybtn' title='点击复制'><i class='fa fa-clipboard'></i> ".substr($link, 0, 36)."</span>";
        });
        $grid->column('name', __('二维码名称'));
        //$grid->column('params', __('参数'));
        $grid->column('user_num',__('推广数据'))->display(function($data){
            if($data) {
                $str = "<p>扫码次数:" . $data['scan_num'] . "</p>
                        <p>关注次数:" . $data['sub_num'] . "</p>
                        <p>新用户数:" . $data['user_num'] . "</p>
                        <p>充值/关注比:" . $data['recharge2sub'] . "</p>
                        <p>充值成功/关注比例:" . $data['rechargesucc2sub'] . "</p>";
                return $str;
            }
        });
        $grid->column('recharge_num',__('充值信息'))->display(function($data){
            if($data) {
                $str = "<p>充值金额:￥" . $data['recharge_money'] . "</p>
                        <p>渠道成本:￥" . $data['cost'] . "</p>
                        <p>回本率:" . $data['rechargesucc2cost'] . "</p>
                        <p>订单数:" . $data['order_num'] . "</p>
                        <p>成交数:" . $data['recharge_num'] . "</p>
                        <p>充值成功率:" . $data['recharge2succ'] . "</p>";
                return $str;
            }
        });
        /*
        $grid->column('scan_num', __('扫码次数(包含关注次数)'));
        $grid->column('sub_num', __('关注次数'));*/
        $grid->column('img', __('推广图片'))->image('', 100);
        $grid->column('qrcode', __('二维码图片'))->image('', 50);
        $grid->column('status', __('状态'))->switch(WechatQrcode::switchStatus())->sortable();
        $grid->column('updated_at', __('最近更新'));

        $grid->disableExport(); // 去掉导出按钮
        $grid->disableFilter(); // 禁用查询过滤器
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
            $actions->append('<br><a href="'. $uri.'/'.$actions->getKey() .'/edit?do=resetqrcode" class="grid-row-edit btn btn-xs btn-warning" title="重新生成二维码"><i class="fa fa-qrcode"></i> 重新生成二维码</a>');
            $actions->append('<br><a target="_blank" href="/'. config('admin.route.prefix').'/recharge-logs?_columns_=balance,created_at,desc,money,out_trade_no,pay_time,status,user.created_at,user.name&wechat_qrcode_id='.$actions->getKey() .'" class="grid-row-edit btn btn-xs btn-info" title="订单明细"><i class="fa fa-list-ol"></i> 订单明细</a>');
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();// 去掉默认的id过滤器
            $filter->like('name', '二维码名称');
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
        $show = new Show(WechatQrcode::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('img', __('Img'));
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
        $form = new Form(new WechatQrcode);

        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $wechat = Wechat::where([
            ['customer_id', '=', $customer->id],
            ['status', '=', 1]
        ])->select('id')->first();
        $form->hidden('customer_id', '客户ID')->value($customer['id']);
        if(!$wechat) {
            $form->display('plat_wechat_id', '公众号ID')->with(function () {
                return '<span class="btn btn-xs btn-danger" onclick="window.location.href=\'/'.config('admin.route.prefix').'/wechats\';">未配置公众号，请前往配置</span>';
            });
            $form->ignore(['plat_wechat_id']);
        } else {
            $form->hidden('plat_wechat_id', '公众号ID')->value($wechat->id);
        }
        if (request()->input('novel_id')) {
            $form->hidden('novel_id', '小说ID')->value(request()->input('novel_id'));
        } else {
            $form->text('novel_id', '小说ID')->required();
        }
        if (request()->input('section')) {
            $form->hidden('section', '小说章节')->value(request()->input('section'));
        } else {
            $form->text('section', '小说章节')->required();
        }

        $form->text('name', __('二维码名称'))->required();
        $form->fencurrency('cost', __('渠道成本'))->required();
        $form->table('params', __('附加参数列表'), function ($table) {
            $table->text('key');
            $table->text('value');
        });
        $form->switch('status', __('状态'))->states(WechatQrcode::switchStatus())->default(WechatQrcode::STATUS_1);
        $form->tools(function (Form\Tools $tools) {
            // $tools->disableList();// 去掉`列表`按钮
            $tools->disableDelete();// 去掉`删除`按钮
            $tools->disableView();// 去掉`查看`按钮
        });
        $form->footer(function (Form\Footer $footer) use ($wechat) {
            $footer->disableViewCheck();// 去掉`查看`checkbox
            $footer->disableEditingCheck();// 去掉`继续编辑`checkbox
            $footer->disableCreatingCheck();// 去掉`继续创建`checkbox
            if(!$wechat) {
                $footer->disableSubmit();
            }
        });
        //保存后回调
        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            $this->productQrcode($id);

        });
        // 抛出错误信息
        $form->saving(function (Form $form) {
            $params = request()->input('params');
            if ($params && is_array($params)) {
                foreach ($params as $item) {
                    if ($item['key'] == 'n' && !Novel::where('id', $item['value'])->where('status', '>', 0)->first()->toArray()) {
                        $error = new MessageBag([
                            'title'   => '错误',
                            'message' => '小说数据异常',
                        ]);
                        return back()->with(compact('error'));
                    }
                }
            }
            /*if (request()->route()->getActionMethod() === 'store' && $form->type == 1 && !is_file($form->img)) {
                $error = new MessageBag([
                    'title'   => '错误',
                    'message' => '轮播图必须上传图片',
                ]);
                return back()->with(compact('error'));
            }*/
        });

        return $form;
    }

    public function productQrcode($pkid = 0) {
        if (!$pkid) {
            $id = request()->input('id');
        } else {
            $id = $pkid;
        }
        if (!$id) {
            throw new \Exception('没有找到对应的二维码配置', 2000);
        }
        $info = WechatQrcode::find($id);
        $refer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (!$info || ($pkid && $info->updated_at != $info->created_at && !strpos($refer, 'do=resetqrcode'))) {
            return false; // 直接生成二维码；不是创建就不生成
        }
        $customer_id = $info->customer_id;
        $params = $info->params;
        $novel_id = $info->novel_id;
        $section = $info->section;

        $wec = Wechat::where([['status', 1], ['customer_id', $customer_id]])->select(['id', 'name', 'appid'])->first();
        if (!$wec) exit('<h1 style="color:red;">customer_id 参数异常，没有找到合适的公众号信息</h1>');
        if (!is_array($params)) {
            $params = json_decode($params, 1);
        }
        $params['id'] = $id;
        $params['n']  = $novel_id;
        $params['s']  = $section;

        $qrcode = $this->ProductParamsQRCode($customer_id, $params, 'QR_LIMIT_STR_SCENE');
        $temp_qrcode = public_path('img/temp/') . RandCode() . '.jpg';
        $han = fopen($temp_qrcode, 'w+');
        fwrite($han, $qrcode);
        fclose($han);
        $QrcodeMerage = new Qrcode();
        $QrcodeMerage->resizeImg(['img'=>$temp_qrcode, 'width'=>185, 'height'=>185]);
        $QrcodeMerage->insertPic([
            'width' => 566,
            'height'=> 256,
            'bg'    => public_path('img/wechatqrcodebg.jpg'),
            'qr'    => $temp_qrcode,
            'complete'  => $temp_qrcode,
            'left'  => 380,
            'top'   => 45,
        ]);
        $dir = config('app.name').'/wechat-qrcode/qr2bg/'.$id.'.jpg';
        $dir_qr = config('app.name').'/wechat-qrcode/qrcode/'.$id.'.jpg';
        //if (Storage::disk('oss')->put($dir, $qrcode))
        //if (Storage::disk('oss')->put($dir, file_get_contents($temp_qrcode)) && Storage::disk('oss')->put($dir_qr, $qrcode))
        if (Storage::disk('oss')->put($dir, file_get_contents($temp_qrcode)))
        {
            $url = Storage::disk('oss')->url($dir);
            //$url_qr = Storage::disk('oss')->url($dir_qr);
            $url_qr = $url . '?x-oss-process=image/crop,x_-3,y_-28,w_180,h_180,g_se'; // 裁剪获取二维码图片
            WechatQrcode::where('id', $id)->update(['img' => $url, 'qrcode'=>$url_qr]);
            unlink($temp_qrcode);
            if (!$pkid) {
                return ['err_code'=>0, 'err_msg'=>'二维码生成成功'];
            }
        }
        if (!$pkid) {
            return ['err_code'=>2000, 'err_msg'=>'二维码保存失败'];
        }
    }
}
