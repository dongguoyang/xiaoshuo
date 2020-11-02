<?php

namespace App\Admin\Controllers;

use App\Admin\Models\NovelSection;
use App\Admin\Models\Novel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;
use App\Admin\Actions\Novel\ImportNovelSectionAction;
use App\Admin\Forms\ImportNovelSection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class NovelSectionAddController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '章节添加管理 - 一次添加章节不易过多，过多添加时候需要等待很久且容易添加失败；建议一次不超过30章';

    protected function grid()
    {
        $grid = new Grid(new NovelSection);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('章节ID'));
        $grid->column('novel', __('小说'))->display(function () {
            return '【'.$this->novel['id'].'】'.$this->novel['title'];
        });
        $grid->column('num', __('序号'));
        $grid->column('title', __('名称'));
        $grid->column('content', __('内容节点'));
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('title', '名称');
            $filter->equal('novel_id', '小说ID');
            $filter->equal('num', '序号');
        });
        $grid->disableExport();
        $grid->disableRowSelector();
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function (Grid\Displayers\Actions $actions) use ($uri) {
            $actions->disableDelete();
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
        });
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new ImportNovelSectionAction());
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
        $show = new Show(NovelSection::findOrFail($id));

        $show->field('id', __('章节ID'));
        $show->field('novel', __('小说'))->as(function () {
            return '【'.$this->novel['id'].'】'.$this->novel['title'];
        });
        $show->field('num', __('序号'));
        $show->field('title', __('名称'));
        $show->field('content', __('内容节点'));
        $show->field('updated_num', __('更正次数'));
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
        set_time_limit(0);
        $form = new Form(new NovelSection);

        $form->number('num', __('序号'))->min(0)->help('如果填写已有章节序号；会覆盖原有章节；不填或 0 默认自动递增');
        $form->text('title', __('章节标题'))->help('只有添加一个章节才起作用，一次添加多个章节会取每个章节内容第一行为标题');
        $form->select('section_sep', __('章节分隔符'))->options(['section_title'=>'章节标题分割', 'page_char'=>'打印分页符'])->default('section_title');
        $form->text('novel_id', __('小说ID'))->required();
        //$form->select('novel_id', __('小说ID'))->options(Novel::select('id', 'title')->orderBy('id', 'desc')->get()->pluck('title', 'id'))->required();
        $form->ckeditor('content', __('章节内容'))->required()
            ->help('如果一次上传多个章节；需要插入“打印分页符”来分隔章节；如果多个章节或单章节没有填写章节标题，会默认取第一行做为章节标题，如果标题包含“第”“章”作为标题后在正文内容会被自动过滤掉');

        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
        });
        $form->footer(function (Form\Footer $footer) {
            // 去掉`重置`按钮
            //$footer->disableReset();

            // 去掉`查看`checkbox
            $footer->disableViewCheck();

            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();

            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });

        // 抛出成功信息
        $form->saving(function ($form) {
            $msg = $this->addSections();
            if (strpos($msg, '操作成功！')!== false) {
                $success = new MessageBag([
                    'title'   => '...',
                    'message' => $msg,
                ]);
                return back()->with(compact('success'));
            } else {
                $error = new MessageBag([
                    'title'   => '...',
                    'message' => $msg,
                ]);
                return back()->with(compact('error'));
            }
        });

        return $form;
    }

    private function addSections() {
        $info = request()->input();
        if (!isset($info['content']) || !TrimAll(strip_tags($info['content']))) {
            return '章节内容不存在！';
        }
        if (!isset($info['novel_id']) || !is_numeric($info['novel_id']) || $info['novel_id']<1) {
            return '小说ID异常！';
        }
        $novel = Novel::where('id', $info['novel_id'])->select(['id', 'sections', 'status'])->first();
        if (!$novel) {
            return '小说不存在';
        }
        $hadSec = NovelSection::where('novel_id', $info['novel_id'])->orderBy('num', 'desc')->select(['num', 'id'])->first();

        switch ($info['section_sep'] ) {
            case 'page_char':
                list($add_num, $up_num) = $this->pageCharSep($info, $hadSec);
                break;
            default:
                list($add_num, $up_num) = $this->sectionTitleSep($info, $hadSec);
                break;
        }

        $uphad = NovelSection::where('novel_id', $info['novel_id'])->orderBy('num', 'desc')->select(['num', 'id'])->first();
        if ($uphad && $uphad['num'] != $novel['sections']) {
            $novel->sections = $uphad['num'];
            $novel->save();
        }

        return '操作成功！添加 '. $add_num . ' 个章节；更新 '. $up_num . ' 个章节！';
    }
    // 章节标题分隔章节
    private function sectionTitleSep($info, $hadSec) {
        $add_num = 0;
        $up_num = 0;
        $content = $info['content'];
        $content = str_replace(['<br />', '<br>', '<br/>'], '</p>
<p>', $content);
        $sec_content = str_replace(['&ldquo;', '&hellip;', '&rdquo;', '&nbsp;'], ['“', '...', '”', ' '], $content);
        preg_match_all('/<p>第.*?章.*?<\/p>/', $sec_content, $match);

        //if (!$match || !count($match)) return [$add_num, $up_num]; // 没有找到分隔符
        if (isset($match[0]) && count($match[0])>2) {
            $match = $match[0];

            if ($hadSec && $hadSec['num']) {
                $num = $hadSec['num'] + 1;
            } else {
                $num = 1;
            }
            foreach ($match as $k=>$title) {
                if ($k >= 100) break;
                if (isset($match[$k+1])) {
                    $arr = explode($match[$k+1], $sec_content);
                    if (count($arr) > 2) { // 多个的时候；第一个可能为空
                        if (empty(TrimAll(strip_tags($arr[0])))) {
                            array_shift($arr);
                            $arr[0] = $match[$k+1] . $arr[0];
                        }
                    }

                    if (count($arr) > 2) {
                        $section = $arr[0];
                        $i = 1;
                        $sec_content = '';
                        while ($i < count($arr)) {
                            $sec_content .= ($match[$k+1] . $arr[$i]);
                            $i++;
                        }
                    } else {
                        $section = $arr[0];
                        $sec_content = $match[$k+1] . $arr[1];
                    }
                } else {
                    $section = $sec_content;
                }
                //dump($section);$add = $up = 0;
                list($title, $content) = $this->sepTitleContent($section);
                list($add, $up) = $this->addSection2DB($title, $content, $num, $info['novel_id']);
                $num++;
                $add_num += $add;
                $up_num += $up;
            }
        } else {
            list($add, $up) = $this->addOneSection($content, $info, $hadSec);
            $add_num += $add;
            $up_num += $up;
        }

        return [$add_num, $up_num];
    }
    private function addOneSection($content, $info, $hadSec) {
        list($title, $content) = $this->sepTitleContent($content);
        if (isset($info['title']) && $info['title']) {
            $title = $info['title'];
        }
        if (isset($info['num']) && $info['num']) {
            $num = $info['num'];
        } else {
            if ($hadSec && $hadSec['num']) {
                $num = $hadSec['num'] + 1;
            } else {
                $num = 1;
            }
        }
        list($add, $up) = $this->addSection2DB($title, $content, $num, $info['novel_id']);
        return [$add, $up];
    }
    // 分页符分隔章节
    private function pageCharSep($info, $hadSec) {
        $content = $info['content'];
        $sections = explode('<div style="page-break-after:always"><span style="display:none">&nbsp;</span></div>', $content);
        $add_num = 0;
        $up_num = 0;
        if (count($sections) > 1) {
            if ($hadSec && $hadSec['num']) {
                $num = $hadSec['num'] + 1;
            } else {
                $num = 1;
            }
            foreach ($sections as $section) {
                list($title, $content) = $this->sepTitleContent($section);
                list($add, $up) = $this->addSection2DB($title, $content, $num, $info['novel_id']);
                $num++;
                $add_num += $add;
                $up_num += $up;
            }
        } else {
            list($add, $up) = $this->addOneSection($content, $info, $hadSec);
            $add_num += $add;
            $up_num += $up;
        }

        return [$add_num, $up_num];
    }
    // 添加章节到数据库
    private function addSection2DB($title, $content, $num, $novel_id) {
        $had = NovelSection::where('novel_id', $novel_id)->where('num', $num)->first();

        if ($had) {
            $path = 'html/' . $novel_id . '/' . explode('html/'.$novel_id . '/', $had['content'])[1];
            $content = $this->uploadCloud($path, $content);
            $data['content'] = $content;
            $had->title = $title;
            $had->content = $content;
            $had->save();
            return [0, 1];
        } else {
            $path = 'html/' . $novel_id . date('/Ymd/') . RandCode(18, 12) . '.html';
            $content = $this->uploadCloud($path, $content);
            $data = [
                'title' => $title,
                'num'   => $num,
                'novel_id'  => $novel_id,
                'content'   => $content,
            ];
            NovelSection::create($data);
            return [1, 0];
        }
    }
    /**
     * 上传图片或者文件到云对象存储
     * @param string $path
     * @param string $str 文件流
     */
    private function uploadCloud($path, $str, $re = 0) {

        $dir = 'lm-novelspider/handleupload/' . $path;

        try {
            if (!Storage::disk(config('filesystems.default'))->put($dir, $str))
            {
                throw new \Exception('文件上传失败！', 2001);
            }

            $url = Storage::disk(config('filesystems.default'))->url($dir);
            return $url;
        } catch (\Exception $e) {
            dump(
                date('Y-m-d H:i:s'),
                $e->getMessage(),
                'error line : ' . $e->getLine(),
                '--------------------------------------------------'
            );
            if ($re < 3) {
                $re++;
                sleep($re * 3);
                return $this->uploadCloud($path, $str, $re);
            }
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
    // 分隔标题和内容
    private function sepTitleContent($section) {
        $section = str_replace(['<br />', '<br>', '<br/>'], '</p>
<p>', $section);
        $section = str_replace(['&ldquo;', '&hellip;', '&rdquo;', '&nbsp;'], ['“', '...', '”', ' '], $section);

        $arr = explode('</p>', $section);
        $title = '';
        foreach ($arr as $line) {
            $temp = TrimAll(strip_tags($line));
            if ($temp) {
                $title = $temp;
                if (strpos($title, '第')===0 && (strpos($title, '章') || strpos($title, '张'))) {
                    $section = str_replace($line . '</p>', '', $section);
                }
                break;
            }
        }
        return [$title, $section];
    }

}
