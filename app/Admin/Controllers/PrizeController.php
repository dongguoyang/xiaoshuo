<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Prize;
use App\Admin\Models\Coupon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;

class PrizeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '抽奖管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Prize);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('奖品ID'));
        $grid->column('img', __('图标'))->image('', 80, 80);
        $grid->column('name', __('名称'));
        $grid->column('prize_detail', __('奖品明细'))->display(function () {
            switch ($this->type) {
                case 1:
                    $style = 'color:#daaa3d;';
                    $title = '书币：'.$this->coin.' 枚';
                    break;
                case 2:
                    $style = 'color:#ef0837;';
                    $coupon = Coupon::find($this->coupon_id);
                    if($coupon) {
                        switch ($coupon['type']) {
                            case 1:
                                $type = '全文';
                                break;
                            case 2:
                                $type = '章节';
                                break;
                            default:
                                $type = '其他';
                                break;
                        }
                        $title = '书券：【'.$type.'】'.$coupon['name'];
                    } else {
                        $title = '！！书券已丢失！！';
                    }
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '！！未知！！';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $grid->column('count', __('总量'));
        $grid->column('send_num', __('送出'));
        $grid->column('chance', __('中奖概率'))->display(function ($chance) {
            return $chance / 10000;
        });
        $grid->column('status', __('启用状态'))->switch(Prize::switchStatus());
        $grid->column('updated_at', __('更新时间'));
        $grid->column('created_at', __('创建时间'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('name', '名称');
            $filter->equal('status', '状态')->radio([
                0   =>  '停用',
                1   =>  '启用'
            ]);
            $filter->equal('type', '状态')->radio([
                1   =>  '书币',
                2   =>  '书券'
            ]);
        });
        $grid->disableExport();
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            // $actions->append('<label data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="删除" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-trash"></i> 删除</label>');
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
        $show = new Show(Prize::findOrFail($id));

        $show->field('id', __('奖品ID'));
        $show->field('name', __('名称'));
        $show->field('img', __('图标'))->image('', 80, 80);
        $show->field('prize_detail', __('奖品类型'))->as(function () {
            switch ($this->type) {
                case 1:
                    $style = 'color:#daaa3d;';
                    $title = '书币：'.$this->coin.' 枚';
                    break;
                case 2:
                    $style = 'color:#ef0837;';
                    $coupon = Coupon::find($this->coupon_id);
                    if($coupon) {
                        switch ($coupon['type']) {
                            case 1:
                                $type = '全文';
                                break;
                            case 2:
                                $type = '章节';
                                break;
                            default:
                                $type = '其他';
                                break;
                        }
                        $title = '书券：【'.$type.'】'.$coupon['name'];
                    } else {
                        $title = '！！书券已丢失！！';
                    }
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '！！未知！！';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $show->field('count', __('总量'));
        $show->field('send_num', __('送出'));
        $show->field('chance', __('中奖概率'))->as(function ($chance) {
            return $chance / 10000;
        });
        $show->field('status', __('启用状态'))->as(function ($status) {
            return $status > 0 ? '正常' : '停用';
        });
        $show->field('updated_at', __('更新时间'));
        $show->field('created_at', __('创建时间'));

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
        $form = new Form(new Prize);

        $form->text('name', __('名称'))->rules([
        'required', 'regex:/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u',
        'between:2,64',
        Rule::unique('prizes')->where(function ($query) use ($form) {
            $query->where('id', '<>', $form->model()->id);
        })
    ], ['regex' => '名称只能由汉字、字母、数字和下划线组成']);
        $form->image('img', __('图标'))->uniqueName()->move('prize/images')->rules(['required', 'image']);
        $form->number('count', __('总量'))->default(10)->rules(['required', 'min:1']);
        $form->number('send_num', __('送出'))->default(0)->rules(['required', 'min:0']);
        $form->radio('type', __('奖品类型'))->options(['1' => '书币', '2'=> '书券'])->default(1)->required();
        $form->number('coin', __('价值'))->help('奖励书币时，此项必填');
        $form->select('coupon_id', __('书券'))->options(function ($coupon_id) {
            $coupon = Coupon::find($coupon_id);
            if ($coupon) {
                return [$coupon->id => $coupon->name];
            }
        })->ajax('/administrator/api/coupons')->help('奖励书券时，此项必填');
        $form->number('chance', __('中奖概率'))->rules(['required', 'between:1,10000'])->help('比例基数为一万，这里请填写1至10000的数字');
        $form->switch('status', __('启用状态'))->states(Prize::switchStatus())->default(1)->required();

        $form->saving(function (Form $form) {
            if($form->count < $form->send_num) {
                $error = new MessageBag([
                    'title'   => '奖品数量配置错误',
                    'message' => '奖品总量不足',
                ]);
                return back()->with(compact('error'));
            }
            switch($form->type) {
                case 1:
                    if($form->coin < 1) {
                        $error = new MessageBag([
                            'title'   => '老兄，你的骚操作我是真的看不懂',
                            'message' => '奖品是怎么都要给的，哪怕是一毛钱，大哥哥/大姐姐',
                        ]);
                        return back()->with(compact('error'));
                    }
                    break;
                case 2:
                    $coupon = Coupon::find($form->coupon_id);
                    if(!$coupon) {
                        $error = new MessageBag([
                            'title'   => '老兄，你的骚操作我是真的看不懂',
                            'message' => '奖品是怎么都要给的，请选择正确的书券，大哥哥/大姐姐',
                        ]);
                        return back()->with(compact('error'));
                    }
                    break;
                default:
                    $error = new MessageBag([
                        'title'   => '天天都要你猜',
                        'message' => '你猜我猜不猜',
                    ]);
                    return back()->with(compact('error'));
                    break;
            }
        });

        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
        });

        return $form;
    }
}
