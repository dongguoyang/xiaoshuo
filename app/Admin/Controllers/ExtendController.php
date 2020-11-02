<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/8
 * Time: 21:20
 */

namespace App\Admin\Controllers;


use App\Admin\Extensions\Grids\Buttons\DeleteAll;
use App\Admin\Models\ExtendLink;
use App\Admin\Models\Novel;
use App\Admin\Models\NovelSection;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\ExtendLinkRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Cache;

class ExtendController extends AdminController
{
    protected $title="推广链接";
    public function grid(){
        $grid=new Grid(new ExtendLink());
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id','ID');
        $grid->column('subscribe_section','关注的章节序号');
        $grid->column('status',__('状态'))->switch(ExtendLink::switchStatus(0,['关闭','打开']));;
        $grid->column('extend_data',__('推广数据'))->display(function($avs){
            if($avs) {
                $rs = \GuzzleHttp\json_decode($avs, 1);
                $str = "<p>新增关注数:" . $rs['关注数'] . "</p>
                        <p>原文访问人数:" . $rs['人数'] . "</p>
                        <p>关注率:" . $rs['关注率'] . "</p>
                        <p>充值/关注比:" . $rs['sub2rech'] . "</p>
                        <p>充值成功/关注比例:" . $rs['sub2rechsucc'] . "</p>";
                return $str;
            }
        });
        $grid->column('qd_info',__('渠道信息'))->display(function($avs){
            $rs=\GuzzleHttp\json_decode($avs,1);
            $str="<p>渠道名称:".$rs['title']." ID:".$rs['ID']."</p><p>"."书名/页面名:".$rs['novel_title']."</p><p>".
                "<span data-content='".$rs["link"]."' target='_blank' class='copybtn' title='点击复制'><i class='fa fa-clipboard'></i> ".substr($rs['link'], 0, 36)."</span>".
                $rs['type']."</p><p></p><p>创建时间:".date('Y-m-d H:i:s',$rs['created_at']);
            return $str;
        });
        $grid->column('recharge_info',__('充值信息'))->display(function($avs){
            if($avs) {
                $rs = \GuzzleHttp\json_decode($avs, 1);
                $str = "<p>充值金额:" . ($rs['充值金额']/100) .
                    "<p>渠道成本:" . $rs['渠道成本'] . "</p><p>" .
                    "回本率:" . $rs['回本率'] . "</p><p>订单数:" .
                    $rs['充值笔数'] . "</p><p>成交数:" .
                    $rs['成交数'] . "</p><p>充值成功率:" . $rs['充值成功率'];
                return $str;
            }
        });
        $grid->filter(function($filter){
            $filter->like('title', '渠道名称');
            $filter->like('link', '推广域名');
        });
        $grid->disableCreateButton();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $key=$actions->getKey();
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->append('<p style="margin-top: 4px"><a href="extend/extendpage?id='.$key.'" class="btn-info btn btn-xs" target="_blank">获取推广文案 </a> </p>');
            $actions->append('<p style="margin-top: 4px"><a class="grid-row-edit btn-primary btn btn-xs" href="extend/'.$key.'/edit">修改 </a> </p>');
            $actions->append(new DeleteAll($key,'移除链接'));
            $actions->append('<p style="margin-top: 4px"><a target="_blank" href="recharge-logs?_columns_=balance,created_at,desc,money,out_trade_no,pay_time,status,user.created_at,user.name&extend_link_id='.$key.'" class="btn-info btn btn-xs" target="">订单明细 </a> </p>');
            //$actions->append('<p style="margin-top: 4px"><a href="orderInfo_ex?id='.$key.'" class="btn-info btn btn-xs" target="">订单明细 </a> </p>');


        });
        return $grid;
    }
    public function detail($id){
         $show=new Show(ExtendLink::findOrFail($id));
         $show->field('id', __('ID'));
         return $show;
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ExtendLink);

        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $form->hidden('customer_id', __('客户ID'))->default($customer['id']);
        $form->hidden('novel_id', __('小说ID'))->default(request()->input('novel_id'));

        if($form->isEditing()) {
            $input = request()->route()->parameters();
            $info = ExtendLink::find($input['extend']);
            $novel = Novel::select(['title'])->find($info['novel_id']);
            $form->display(__('作品名称'))->default($novel['title']);
            $form->select('novel_section_num', __('章节'))->options(NovelSection::num2titlePluck($info['novel_id']))->readOnly();
            $form->radio('type', __('类型'))->options(['1' => '外推', '2'=> '内推'])->disable()->readOnly();
            $form->display('title', __('渠道名称'));
        } else {
            $novel = Novel::select(['title'])->find(request()->input('novel_id'));
            $form->display(__('作品名称'))->default($novel['title']);
            $form->select('novel_section_num', __('章节'))->options(NovelSection::num2titlePluck(request()->input('novel_id')))->required();
            $form->radio('type', __('类型'))->options(['1' => '外推', '2'=> '内推'])->default(1)->required();
            $form->text('title', __('渠道名称'))->required();
        }

        $form->fencurrency('cost', __('成本'));
        $form->switch('must_subscribe', __('是否强制关注'))->states(ExtendLink::switchStatus(0, ['否', '是']))->default(0);
        $form->number('subscribe_section', __('强制关注的章节序号'))->min(0);
        $form->switch('status', __('是否启用'))->states(ExtendLink::switchStatus())->default(1);
        $form->url('link', __('链接'))->disable()->placeholder('提交后自动生成');

        $form->saving(function (Form $form) use ($customer) {
            // 检查权限
            if(!$customer && $form->customer_id != $customer['id']) {
                $error = new MessageBag([
                    'title'   => '无权操作',
                    'message' => '对不起，你无权进行此项操作',
                ]);
                return back()->with(compact('error'));
            }
            // 检查数据是否匹配
            $novel_section = NovelSection::where([['novel_id', $form->novel_id], ['num', $form->novel_section_num]])->first();
            if(empty($novel_section)) {
                $error = new MessageBag([
                    'title'   => '请设置小说章节',
                    'message' => '请设置小说章节',
                ]);
                return back()->with(compact('error'));
            } else {
                if($novel_section['novel_id'] != $form->novel_id) {
                    $error = new MessageBag([
                        'title'   => '家花不如野花香？！',
                        'message' => '你选择的章节不属于你选定的小说，请重新选择',
                    ]);
                    return back()->with(compact('error'));
                }
            }
        });
        //保存后回调
        $form->saved(function (Form $form) {
            $info = $form->model();
            $domainRep = new DomainRepository();
            if ($info['type'] == 1) {
                $host = $domainRep->randOne(3, $info['customer_id']);
            } else {
                $host = $domainRep->randOne(1, $info['customer_id']);
            }

            $link = $host . route('novel.extendlink', ['id'=>$info['id']], false);
            ExtendLink::where('id', $info['id'])->update(['link'=>$link]);

            $key = config('app.name').'extend_link_'.$info['id'];
            Cache::forget($key);
        });
        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
        });
        $form->footer(function (Form\Footer $footer) {
            // 去掉`重置`按钮
            // $footer->disableReset();

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });

        return $form;
    }/*
    public function form(){
        $states = [
            'on'  => ['value' => 1, 'text' => '打开', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
        ];
         $form=new Form(new ExtendLink());
         $form->text('title',__('渠道名称'));
         $form->text('cost',__('渠道成本'));
         $form->switch('must_subscribe',__('强制关注开关'))->states($states);
         $form->switch('status',__('状态'))->states($states);
         $form->text('subscribe_section',__('强制关注的章节序号'));

        $form->disableEditingCheck();

        $form->disableCreatingCheck();

        $form->disableViewCheck();

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });
         return $form;

    }*/

    public function deleteExtend(Request $request){
        try {
            $id = $request->input('id');
            $post = ExtendLink::find($id);
            if ($post->delete()) {
                $msg = ['code' => 200, 'msg' => '删除成功!!'];
            } else {
                $msg = ['code' => 101, 'msg' => '删除失败!!'];
            }
        }catch (\Exception $e){
            $msg=['code'=>$e->getCode(),'msg'=>$e->getMessage()];
        }
        $msg=\GuzzleHttp\json_encode($msg);
        return $msg;

    }

    /**
     * 订单明细
     */
    public function orderInfo(Request $request, Content $content){
        $id=$request->input('id');
        $content->header('推广链接');

        // 选填
        $back_url = '/'.config('admin.route.prefix').'/extend';
        $content->description('每日订单明细');
        $content->breadcrumb(
            ['text' => '推广链接', 'url' => $back_url],
            ['text' => '每日订单明细']
        );
        $data=ExtendLink::find($id);
        if(!$data) {
            return redirect($back_url);
        }
        $data = $data->toArray();
        $dxs = $data['data_info'] ?? []; //推广信息

        $content->view('admin.extend.orderinfo', ['data' => $dxs, 'title' => $data['title']]);
        return $content;

    }
    /**
     * 订单明细
     */
    public function extendPage(Request $request,Content $content){
        $id=$request->input('id');
        $data = ExtendLink::find($id);
        $novelSectionRep = new NovelSectionRepository();
        if (request()->method() == 'POST') {
            $data = $request->input();
            unset($data['id']);
            if (ExtendLink::where('id', $id)->update(['page_conf' => json_encode($data, JSON_UNESCAPED_UNICODE)])) {
                // $novelSectionRep->ExtendLinkSections($data['novel_id'], $data['novel_section_num'], true);

                return ['code'=>0, 'msg'=>'保存成功！'];
            }
        }
        $sections = $novelSectionRep->ExtendLinkSections($data['novel_id'], $data['novel_section_num']-1);

        $tpl_data = ['data' => $data,'page_conf'=>json_decode($data['page_conf'], 1), 'sections'=>$sections, 'is_admin'=>true];
        $extendLinkRep = new ExtendLinkRepository();
        $tpl_data = array_merge($tpl_data, $extendLinkRep->ExtendPageInfos());
        return view('front.novel.extendpage', $tpl_data);
    }

    public function getnice()
    {
        $image = \request()->input('data');
        $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
        if (strstr($image,",")){
            $image = explode(',',$image);
            $image = $image[1];
        }
        $imageSrc=  $imageName; //图片名字
        Storage::disk('local')->put($imageSrc, base64_decode($image));
        $url=public_path('storage/'.$imageSrc);
        $msg=['code'=>200,'msg'=>$url];
        return response()->json($msg);

    }

    public function dowloads(){
        $url=request()->input('url');
        $name=time().mt_rand(100000,100000000).'.png';
        return response()->download($url,$name);
    }





}