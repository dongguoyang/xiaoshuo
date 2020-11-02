<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Coupon;
use App\Admin\Models\Novel;
use App\Admin\Models\NovelSection;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class CouponController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '书券管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Coupon);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('书券ID'));
        $grid->column('img', __('图标'))->image('', 100, 100);
        $grid->column('name', __('券名'));
        $grid->column('type', __('类型'))->display(function ($type) {
            switch ($type) {
                case 1:
                    $style = 'color:#3dda42;';
                    $title = '全文';
                    break;
                case 2:
                    $style = 'color:#ef0837;';
                    $title = '章节';
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $grid->column('novel_info', __('书券对象'))->display(function ($novel_info) {
            $node = $this->type == 2 ? NovelSection::find($novel_info) : ($this->type == 1 ? Novel::find($novel_info) : []);
            $node = $node ? $node->toArray() : [];
            return $node ? (
                    $this->type == 2 ?
                    '【'.($node['novel']['title'] ?: Str::limit($node['novel']['desc'], 36)).'】'.$node['title'] :
                    '【'.($node['title'] ?: Str::limit($node['desc'], 36)).'】') :
                '<span style="color:red">-未知-</span>';
        });
        $grid->column('count', __('总数'))->display(function ($count) {
            return $count == -1 ? '<span style="color:#ef0837;">无限</span>' : $count;
        });
        $grid->column('send_num', __('已发放'));
        $grid->column('use_num', __('已使用'));
        $grid->column('status', __('是否启用'))->switch(Coupon::switchStatus());
        $grid->column('valid_date_range', __('有效期'))->display(function () {
            return '<span style="color:#36ef08;">'.$this->start_at.'</span>&nbsp;&nbsp;至&nbsp;&nbsp;<span style="color:#ef0837;">'.$this->end_at.'</span>';
        });
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('name', '券名');
            $filter->equal('type', '类型')->radio([
                1   =>  '全文',
                2   =>  '章节'
            ]);
            $filter->equal('status', '状态')->radio([
                0   =>  '停用',
                1   =>  '启用'
            ]);
            $filter->where(function (Builder $query) {
                $query->where('start_at', '>=', strtotime($this->input));
            }, '起始时间', 'start_at')->date()->default(date('Y-m-d'));
            $filter->where(function (Builder $query) {
                $query->where('end_at', '<=', strtotime($this->input));
            }, '截止时间', 'end_at')->date()->default(date('Y-m-d'));
            // $filter->nlt('start_at', __('起始时间'))->datetime(['format' => 'Y-m-d']);
            // $filter->ngt('end_at', __('截止时间'))->datetime(['format' => 'Y-m-d']);
        });
        $grid->disableExport();
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
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
        $show = new Show(Coupon::findOrFail($id));

        $show->field('id', __('书券ID'));
        $show->field('img', __('图标'))->image('', 150, 150);
        $show->field('name', __('券名'));
        $show->field('type', __('类型'))->as(function ($type) {
            switch ($type) {
                case 1:
                    $style = 'color:#3dda42;';
                    $title = '全文';
                    break;
                case 2:
                    $style = 'color:#ef0837;';
                    $title = '章节';
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $show->field('novel_info', __('书券对象'))->as(function ($novel_info) {
            $node = $this->type == 2 ? NovelSection::find($novel_info) : ($this->type == 1 ? Novel::find($novel_info) : []);
            $node = $node ? $node->toArray() : [];
            return $node ? (
            $this->type == 2 ?
                '【'.($node['novel']['title'] ?: Str::limit($node['novel']['desc'], 36)).'】'.$node['title'] :
                '【'.($node['title'] ?: Str::limit($node['desc'], 36)).'】') :
                '<span style="color:red">-未知-</span>';
        });
        $show->field('count', __('总数'));
        $show->field('send_num', __('已发放'));
        $show->field('use_num', __('已使用'));
        $show->field('status', __('是否启用'))->as(function ($status) {
            return $status > 0 ? '是' : '否';
        });
        $show->field('start_at', __('起始时间'));
        $show->field('end_at', __('截止时间'));
        $show->field('updated_at', __('更新于'));
        $show->field('created_at', __('创建于'));

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
        $form = new Form(new Coupon);

        $form->text('name', __('券名'))->rules([
            'required', 'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u',
            'between:2,64',
            Rule::unique('coupons')->where(function ($query) use ($form) {
                $query->where('id', '<>', $form->model()->id);
            })
        ], ['regex' => '券名只能由汉字、字母、数字和下划线组成']);
        $form->image('img', __('图标'))->uniqueName()->move('coupon/images')->rules(['required', 'image']);
        $form->radio('type', __('类型'))->options(['1' => '全文', '2'=> '章节'])->default(1)->required();
        $form->number('novel_info', __('书券对象ID'))->required()->help('当类型为“全文”时，请填写小说ID；当类型为“章节”时，请填写章节ID。');
        $form->number('count', __('总数'))->default(-1)->required();
        if($form->isCreating()) {
            $form->number('send_num', __('已发放'))->default(0);
            $form->number('use_num', __('已使用'))->default(0);
        } else {
            $form->number('send_num', __('已发放'))->default(0)->readonly();
            $form->number('use_num', __('已使用'))->default(0)->readonly();
        }
        $form->switch('status', __('是否启用'))->states(Coupon::switchStatus())->default(1)->required();
        $form->date('start_at', __('起始时间'))->format('YYYY-MM-DD')->default(date('Y-m-d'));
        $form->date('end_at', __('截止时间'))->format('YYYY-MM-DD')->default(date('Y-m-d'));

        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
        });

        return $form;
    }
}
