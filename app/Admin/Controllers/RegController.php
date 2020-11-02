<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/29
 * Time: 13:53
 */

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Models\Reg;
class RegController extends AdminController
{
    protected $title = '采集信息配置';
    protected function grid(){
        //站点url配置规则  url+type_url+page+site_end_url
        $data=array();
        $grid = new Grid(new Reg());
        $grid->column('id',__("ID"));
        $grid->column('site_url',__("站点配置"));
        $grid->column('list_reg',__('列表行配置'));
        $grid->column('list_tag',__('小说地址配置'));
        $grid->column('reg1_title',__('标题正则匹配'));
        $grid->column('reg1_author',__('作者匹配'));
        $grid->column('reg1_img',__('海报匹配'));
        $grid->column('reg1_remark',__('简介匹配'));
        $grid->column('reg1_status',__('状态匹配'));
        $grid->column('reg1_num',__('章节匹配'));
        $grid->column('reg1_content',__('内容行匹配'));
        $grid->column('reg1_content_title',__('章节标题匹配'));
        $grid->column('reg1_content_num',__('章节数匹配'));
        $grid->column('status',__('状态'));
        $grid->column('page',__('页数'));
        $grid->column('domian',__('域名'));
        $grid->column('site_end_url',__('尾路径参数'));
        $grid->actions(function ($actions) { //去掉修改和删除
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->disableCreateButton();
        $grid->disableBatchActions(); //去掉批量操作

            return $grid;
    }
    protected function detail($id){
        $show = new Show(Reg::findOrFail($id));
        $show->field('id',__("ID"));
        $show->field('site_url',__("站点配置"));
        $show->field('list_reg',__('列表行配置'));
        $show->field('list_tag',__('小说地址配置'));
        $show->field('reg1_title',__('标题正则匹配'));
        $show->field('reg1_author',__('作者匹配'));
        $show->field('reg1_img',__('海报匹配'));
        $show->field('reg1_remark',__('简介匹配'));
        $show->field('reg1_status',__('状态匹配'));
        $show->field('reg1_num',__('章节匹配'));
        $show->field('reg1_content',__('内容行匹配'));
        $show->field('reg1_content_title',__('章节标题匹配'));
        $show->field('reg1_content_num',__('章节数匹配'));
        $show->field('status',__('状态'));
        $show->field('page',__('页数'));
        $show->field('domian',__('域名'));
        $show->field('site_end_url',__('尾路径参数'));
        $show->regType('小说类型url', function ($regType) {
                $regType->name('路径');
                $regType->typesid('小说类型id');
                 $regType->actions(function ($actions) { //去掉修改和删除
                $actions->disableDelete();
                $actions->disableEdit();
            });
            $regType->disableBatchActions(); //去掉批量操作
            $regType->disableExport();
            $regType->disableCreateButton();
        });

        return $show;
    }

    protected function form(){
        return redirect('administrator/reg');
        $form = new Form(new Reg);
        $form->display('id',__("ID"));
        $form->display('site_url',__("站点配置"));
        $form->display('list_reg',__('列表行配置'));
        $form->display('list_tag',__('小说地址配置'));
        $form->display('reg1_title',__('标题正则匹配'));
        $form->display('reg1_author',__('作者匹配'));
        $form->display('reg1_img',__('海报匹配'));
        $form->display('reg1_remark',__('简介匹配'));
        $form->display('reg1_status',__('状态匹配'));
        $form->display('reg1_num',__('章节匹配'));
        $form->display('reg1_content',__('内容行匹配'));
        $form->display('reg1_content_title',__('章节标题匹配'));
        $form->display('reg1_content_num',__('章节数匹配'));
        $form->display('status',__('状态'));
        $form->display('page',__('页数'));
        $form->display('domian',__('域名'));
        $form->display('site_end_url',__('尾路径参数'));
        return $form;
    }



}