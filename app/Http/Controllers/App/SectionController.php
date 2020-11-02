<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\BaseController;
use App\Logics\Services\src\NovelService;

use App\Admin\Models\NovelSection;
use App\Admin\Models\Novel;
use Illuminate\Support\Facades\Storage;

class SectionController extends BaseController
{
    public function addSections() {
        $info = request()->input();
        if (!isset($info['content']) || !TrimAll(strip_tags($info['content']))) {
            return '章节内容不存在！';
        }
        if (!isset($info['novel_id']) || !is_numeric($info['novel_id']) || $info['novel_id']<1) {
            return '小说ID异常！';
        }
        if (!isset($info['section_id']) || !is_numeric($info['section_id']) || $info['section_id']<1) {
            return '章节ID异常！';
        }
        $novel = Novel::where('id', $info['novel_id'])->select(['id', 'sections', 'status'])->first();
        if (!$novel) {
            return '小说不存在';
        }
        $hadSec = NovelSection::where('novel_id', $info['novel_id'])->orderBy('num', 'desc')->select(['num', 'id'])->first();
        $hadSec['num'] = $info['section_id'];
        $info['section_sep'] = 'section_title';
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
                //$num = $hadSec['num'] + 1;
                $num = $hadSec['num'];
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
                //$num = $hadSec['num'] + 1;
                $num = $hadSec['num'];
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
                //$num = $hadSec['num'] + 1;
                $num = $hadSec['num'];
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
        //$filesystems_default = 'local';
        try {
            if (!Storage::disk(config('filesystems.default'))->put($dir, $str))
            //if (!Storage::disk(local)->put($dir, $str))
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
