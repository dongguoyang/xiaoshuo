<?php

namespace App\Admin\Controllers;

use App\Admin\Models\ExtendLink;
use App\Admin\Models\Customer;
use App\Admin\Models\Novel;
use App\Admin\Models\NovelSection;
use App\Logics\Repositories\src\DomainRepository;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExtendLinkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '推广链接管理';
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
        $grid = new Grid(new ExtendLink);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('ID'));
        $grid->column('customer', __('商家'))->display(function ($customer) {
            return $customer['company'] ? '【'.$customer['company'].'】'.$customer['name'] : ($customer ? $customer['name'] : '');
        });
        $grid->column('novel', __('小说'))->display(function ($novel) {
            if ($novel) {
                return ($novel['title'] ?: Str::limit($novel['desc'], 36));
            }
            return '';
        });
        $grid->column('novel_section_num', __('章节'))->display(function ($novel_section_num) {
            $novel_section = NovelSection::where('novel_id', $this->novel_id)->where('num', $novel_section_num)->first();
            if($novel_section) {
                if($novel_section['novel_id'] == $this->novel_id) {
                    return '【第'.$novel_section['num'].'章】'.($novel_section['title'] ? $novel_section['title'] : Str::limit($novel_section['content'], 36));
                } else {
                    return '<span style="color:red">-数据不匹配-</span>';
                }
            } else {
                return '<span style="color:red">-未配置或数据遗失-</span>';
            }
        });
        $grid->column('type', __('类型'))->display(function ($type) {
            switch ($type) {
                case 1:
                    $style = 'color:#3dda42;';
                    $title = '外推';
                    break;
                case 2:
                    $style = 'color:#ef0837;';
                    $title = '内推';
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $grid->column('title', __('渠道名称'));
        $grid->column('link', __('链接'))->display(function ($val){return "<a href='$val' target='_blank'>".substr($val, 0, 36)."</a>"; });
        $grid->column('cost', __('成本'))->fen2yuan();
        $grid->column('must_subscribe', __('是否强制关注'))->switch(ExtendLink::switchStatus(0, ['否', '是']));
        $grid->column('subscribe_section', __('强制关注章节'))->help('开始的几章免费观看，以便吸引读者，后续的要求关注，以增长公众号粉丝')->display(function ($subscribe_section) {
            return $subscribe_section ? '第 '.$subscribe_section.' 章' : '';
        });
        $grid->column('status', __('是否启用'))->switch(ExtendLink::switchStatus());
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            if($this->customer()->isAdministrator()) {
                $filter->equal('customer_id', '商家')->select(function () {
                    return Optional(Customer::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
                })->ajax('/administrator/api/customers');
            }
            $filter->equal('novel_id', '小说名')->select(function () {
                return Optional(Novel::orderBy('id', 'asc')->limit(10)->get())->pluck('title', 'id');
            })->ajax('/administrator/api/novels');
            $filter->like('title', __('渠道名称'));
            $filter->equal('type', '类型')->radio([
                1   =>  '外推',
                2   =>  '内推'
            ]);
            $filter->equal('must_subscribe', '是否强制关注')->radio([
                0   =>  '否',
                1   =>  '是'
            ]);
            $filter->equal('status', '状态')->radio([
                0   =>  '禁用',
                1   =>  '启用'
            ]);
        });
        // $grid->disableCreateButton();
        $grid->disableExport();
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
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
        $show = new Show(ExtendLink::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('customer', __('商家'))->as(function ($customer) {
            return $customer['company'] ? '【'.$customer['company'].'】'.$customer['name'] : ($customer ? $customer['name'] : '');
        });
        $show->field('novel', __('小说'))->as(function ($novel) {
            return ($novel['title'] ?: Str::limit($novel['desc'], 36));
        });
        $show->field('novel_section_id', __('章节'))->as(function ($novel_section_id) {
            $novel_section = NovelSection::find($novel_section_id);
            if($novel_section) {
                if($novel_section['novel_id'] == $this->novel_id) {
                    return '【第'.$novel_section['num'].'章】'.($novel_section['title'] ? $novel_section['title'] : Str::limit($novel_section['content'], 36));
                } else {
                    return '-数据不匹配-';
                }
            } else {
                return '-未配置或数据遗失-';
            }
        });
        $show->field('type', __('类型'))->display(function ($type) {
            switch ($type) {
                case 1:
                    $title = '外推';
                    break;
                case 2:
                    $title = '内推';
                    break;
                default:
                    $title = '其他';
                    break;
            }
            return $title;
        });
        $show->field('title', __('渠道名称'));
        $show->field('link', __('链接'))->link();
        $show->field('cost', __('成本'))->fen2yuan();
        $show->field('must_subscribe', __('是否强制关注'))->as(function ($status) {
            return $status > 0 ? '是' : '否';
        });
        $show->field('subscribe_section', __('强制关注的章节序号'))->as(function ($subscribe_section) {
            return '第 '.$subscribe_section.' 章';
        });
        $show->field('status', __('是否启用'))->as(function ($status) {
            return $status > 0 ? '启用' : '禁用';
        });
        $show->field('updated_at', __('更新于'));
        $show->field('created_at', __('创建于'));

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

        $form->hidden('customer_id', __('客户ID'))->default(\Illuminate\Support\Facades\Auth::guard('admin')->user()['id']);
        $form->hidden('novel_id', __('小说ID'))->default(request()->input('novel_id'));
        /*$form->select('novel_id', __('小说'))->options(function ($novel_id) {
            $novel_id = $novel_id ?: request()->input('novel_id');
            $novel = Novel::select(['id', 'title'])->find($novel_id);
            if ($novel) {
                return [$novel->id => $novel->title];
            }
        })->ajax('/administrator/api/novels')->required();*/
        if(request()->route()->getActionMethod() === 'edit') {
            $input = request()->route()->parameters();
            $info = ExtendLink::find($input['extend_link']);
            $novel = Novel::select(['title'])->find($info['novel_id']);
            $form->display(__('作品名称'))->default($novel['title']);
            $form->select('novel_section_num', __('章节'))->options(NovelSection::num2titlePluck($info['novel_id']))->required();
        } else {
            $novel = Novel::select(['title'])->find(request()->input('novel_id'));
            $form->display(__('作品名称'))->default($novel['title']);
            $form->select('novel_section_num', __('章节'))->options(NovelSection::num2titlePluck(request()->input('novel_id')))->required();
        }
        /*$form->select('novel_section_id', __('章节'))->options(function ($novel_section_id) {
            $novel_section = NovelSection::find($novel_section_id);
            if ($novel_section) {
                return [$novel_section->id => $novel_section->title];
            }
        })->ajax('/administrator/api/novel-sections')->required();*/
        $form->radio('type', __('类型'))->options(['1' => '外推', '2'=> '内推'])->default(1)->required();
        $form->text('title', __('渠道名称'))->required();
        $form->fencurrency('cost', __('成本'));
        // $form->radio('must_subscribe', __('是否强制关注'))->options([0 => '否', 1 => '是'])->default(1)->required();
        $form->switch('must_subscribe', __('是否强制关注'))->states(ExtendLink::switchStatus(0, ['否', '是']))->default(0);
        $form->number('subscribe_section', __('强制关注的章节序号'))->min(0);
        $form->switch('status', __('是否启用'))->states(ExtendLink::switchStatus())->default(1);
        $form->url('link', __('链接'))->disable()->placeholder('提交后自动生成');

        $form->saving(function (Form $form) {
            // 检查权限
            if(!$this->customer()->isAdministrator() && $form->customer_id != $this->customer()->id) {
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
            /*$link = $host . route('novel.extendlink', ['sign'=>encrypt(json_encode([
                    'cid'               => $info['customer_id'],
                    'novel_id'          => $info['novel_id'],
                    'section_num'       => $info['novel_section_num'],
                    'extend_link_id'    => $info['id'],
                    'type'              => $info['type'],
                    'must_subscribe'    => $info['must_subscribe'],
                    'subscribe_section' => $info['subscribe_section'],
                ]))], false);*/
            $link = $host . route('novel.extendlink', ['sign'=>encrypt(json_encode(['extend_link_id' => $info['id'], 'cid'=>$info['customer_id']]))], false);
            ExtendLink::where('id', $info['id'])->update(['link'=>$link]);
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
    }
}
