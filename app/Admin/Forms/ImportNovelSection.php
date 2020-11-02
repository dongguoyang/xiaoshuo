<?php

namespace App\Admin\Forms;

use App\Admin\Models\Novel;
use App\Admin\Models\NovelSection;
use Encore\Admin\Form\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use DOMDocument;
use ZipArchive;

class ImportNovelSection extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = '导入章节';
    public $disk;
    protected $file_notice = '仅支持 .txt、.docx 类型的文件，文件命名最好遵循规范如：[小说名]第N章 - 章节标题.txt / .docx . 内容规范：非空的首行必须包含章节序号（章节数）和标题（标题可空），格式：[序号N] - 标题 . 单次请勿上传过多文件';
    public $fail_detail = [];
    public $novel_id = 0;
    public $total = 0;
    public $skip_count = 0;
    public $success_count = 0;
    public $failure_count = 0;
    public $override_count = 0;
    public $override_allow = 'on';// default value
    public $allow_states = [
        'on'  => ['value' => 1, 'text' => '允许', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => '禁止', 'color' => 'danger'],
    ];
    public $chapter_no_max = 0;
    public $indentation_array = [];
    public $content_html = '';
    const SECTIONS_FIELD = 'sections';
    const VALID_EXTENSIONS = ['docx', 'txt'];// 请勿更改其顺序
    const FORMAT = '.html';
    const DELIMITER = '-';
    const DOCX_CONTENT_XML = 'word/document.xml';
    const INDENTATION_UNIT = '    ';// 只能由 空格 组成
    const NEWLINE_HTML = '<br/>';
    const SPACE_HTML = '&nbsp;';


    /**
     * Handle the form request.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle()
    {
        set_time_limit(0);
        $this->disk = config('filesystems.default');
        $request = request();
        if(!$request->isMethod('post')) {
            return response('Content Not Found', 404);
        }
        // 检查小说是否存在
        if(!$request->has('novel_id') || 1 > $this->novel_id = $request->post('novel_id', 0)) {
            admin_warning('导入失败', '请填写正确的小说ID');
            return back();
        }
        $novel = Novel::find($this->novel_id);
        $novel = $novel ? $novel->toArray() : [];
        $this->chapter_no_max = $novel['sections'];
        if(empty($novel)) {
            admin_warning('导入失败', '请填写正确的小说ID');
            return back();
        }
        if(!$request->hasFile(self::SECTIONS_FIELD)) {
            admin_warning('导入失败', '请上传数据文件（'.$this->file_notice.'）');
            return back();
        }
        $sections = $request->file(self::SECTIONS_FIELD);
        $this->override_allow = $request->post('override_allow', $this->override_allow);
        Storage::disk($this->disk);
        foreach($sections as $section) {
            ++$this->total;
            // 解析开关
            $summary_parsed = false;
            // 章节ID
            $chapter_id = 0;
            // 重置内容
            $this->content_html = '';
            $extension = $section->extension();
            $client_original_name = $section->getClientOriginalName();
            $real_path = $section->getRealPath();
            if(!$section->isValid() || !$section->isFile() || !in_array($extension, self::VALID_EXTENSIONS)) {
                ++$this->skip_count;
                $this->fail_detail[] = [$client_original_name => '不支持的文件类型'];
                continue;
            }
            // 读取
            if($extension == self::VALID_EXTENSIONS[0]) {
                // MS WORD 文件
                $zip = new ZipArchive;
                // Open received archive file
                if (true === $zip->open($real_path)) {
                    // If done, search for the data file in the archive
                    if (($index = $zip->locateName(self::DOCX_CONTENT_XML)) !== false) {
                        // If found, read it to the string
                        $data = $zip->getFromIndex($index);
                        // Close archive file
                        $zip->close();
                        // Load XML from a string
                        // Skip errors and warnings
                        $xml = new DOMDocument();
                        $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                        $paragraphs = $xml->documentElement->firstChild->childNodes;
                        $paragraphs_count = $paragraphs->count();
                        // 依次读取
                        for($paragraph_no = 0; $paragraph_no < $paragraphs_count; ++$paragraph_no) {
                            $paragraph = $paragraphs->item($paragraph_no);
                            if($paragraph->nodeName != 'w:p') {
                                continue;
                            }
                            $paragraph_text = $paragraph->nodeValue;
                            if(!$summary_parsed) {
                                $parse_result = $this->parseSummary(trim($paragraph_text), $client_original_name);
                                if($parse_result) {
                                    $summary_parsed = $parse_result['summary_parsed'];
                                    $chapter_id = $parse_result['chapter_id'];
                                }
                                continue;// 未解析之前的行通通死啦死啦地
                            }
                            // 正文来咯
                            $line = '';
                            $paragraph_children = $paragraph->childNodes;
                            $paragraph_children_count = $paragraph_children->count();
                            for($i = 0; $i < $paragraph_children_count; ++$i) {
                                $paragraph_child = $paragraph_children->item($i);
                                $paragraph_child_name = $paragraph_child->nodeName;
                                // 不严格检查行内具体格式，只关注开头/结尾  注意：一下顺序不能调整，必须先开头，最后结尾
                                // 检查开头空格
                                if($paragraph_child_name == 'w:pPr') {
                                    $paragraph_child_subs = $paragraph_child->childNodes;
                                    $paragraph_child_subs_count = $paragraph_child_subs->count();
                                    for($m = 0; $m < $paragraph_child_subs_count; ++$m) {
                                        $paragraph_child_sub = $paragraph_child_subs->item($m);
                                        $paragraph_child_sub_name = $paragraph_child_sub->nodeName;
                                        if($paragraph_child_sub_name == 'w:ind') {
                                            $ind_attributes = $paragraph_child_sub->attributes;
                                            $indentation = $this->getIndentation($ind_attributes);
                                            $line .= $this->getIndentationTag($indentation);// 空行标记，最后替换
                                        }
                                    }
                                }
                                $line .= strip_tags($paragraph_child->nodeValue);// 不保证空，所以保证顺序
                                // 行内换行
                                if($paragraph_child_name == 'w:r') {
                                    $paragraph_child_subs = $paragraph_child->childNodes;
                                    $paragraph_child_subs_count = $paragraph_child_subs->count();
                                    for($m = 0; $m < $paragraph_child_subs_count; ++$m) {
                                        $paragraph_child_sub = $paragraph_child_subs->item($m);
                                        $paragraph_child_sub_name = $paragraph_child_sub->nodeName;
                                        if($paragraph_child_sub_name == 'w:br') {
                                            $line .= $this->getNewLineTag();// 换行
                                        }
                                    }
                                }
                            }
                            $line .= $this->getNewLineTag();// 行结束
                            $this->content_html .= $line;
                        }
                        // 组装
                        if($this->indentation_array) {
                            // 缩进
                            ksort($this->indentation_array);
                            $search = [];
                            $replace = [];
                            $offset = 0;
                            foreach($this->indentation_array as $index => $ind) {
                                $search[] = $this->getIndentationTag($index);
                                $replace[] = str_repeat(self::INDENTATION_UNIT, ++$offset);
                            }
                            $this->content_html = str_replace($search, $replace, $this->content_html);
                        }
                        $this->content_html = $this->spaceTransform($this->content_html);// 转换空格
                        $this->content_html = str_replace($this->getNewLineTag(), self::NEWLINE_HTML, $this->content_html);// 替换换行就是你的益达了
                    } else {
                        $zip->close();
                        ++$this->skip_count;
                        $this->fail_detail[] = [$client_original_name => '文件不完整'];
                        continue;
                    }
                } else {
                    ++$this->skip_count;
                    $this->fail_detail[] = [$client_original_name => '读取文件出错'];
                    continue;
                }
            } else {
                // 文本
                $content = auto_read($real_path, 'UTF-8');// 重新编码
                if(!$content) {
                    ++$this->skip_count;
                    $this->fail_detail[] = [$client_original_name => '文件内容为空或编码不支持'];
                    continue;
                }
                file_put_contents($real_path, $content);// 覆盖原文
                // 行行读取，追加格式
                $txt = fopen($real_path, 'r');
                while(!feof($txt)) {
                    $line = fgets($txt);
                    if(!$summary_parsed) {
                        $parse_result = $this->parseSummary(trim($line), $client_original_name);
                        if($parse_result) {
                            $summary_parsed = $parse_result['summary_parsed'];
                            $chapter_id = $parse_result['chapter_id'];
                        }
                        continue;// 未解析之前的行通通死啦死啦地
                    }
                    $this->content_html .= ($this->spaceTransform(strip_tags($line)).self::NEWLINE_HTML);// 这是你的益达
                }
                fclose($txt);
            }
            $this->updateContent($chapter_id, $this->content_html, $client_original_name, $summary_parsed);
        }

        $detail_str = '';
        if(!empty($this->fail_detail)) {
            $detail_str = '<br/>错误明细：<br/>';
            foreach ($this->fail_detail as $fail_desc) {
                $detail_str .= ('文件：[ ' . $fail_desc[0] . ' ] => ' . $fail_desc[1] . '<br/>');
            }
        }
        if($this->success_count) {
            admin_success('导入成功', '总量：'.$this->total.'；成功：'.$this->success_count.'；覆盖：'.$this->override_count.'；失败：'.$this->failure_count.'；跳过：'.$this->skip_count.$detail_str);
        } else {
            admin_error('导入失败', '总量：'.$this->total.'；成功：'.$this->success_count.'；失败：'.$this->failure_count.'；跳过：'.$this->skip_count.$detail_str);
        }
        return back();
    }

    /**
     * 解析概要行
     * @param string $summary 待解析的字符串
     * @param string $client_original_name 客户端原文件名
     * @return array|bool 返回结果：成功则返回 ['summary_parsed' => true, 'chapter_id' => xxx] ；失败则返回 false
     */
    public function parseSummary(string $summary, string $client_original_name) {
        if(!empty($summary) && mb_strlen($summary) > 0) {
            // 或许可用的概要解析行
            $delimiter_pos = mb_stripos($summary, self::DELIMITER);
            if(false !== $delimiter_pos) {
                $chapter_no = (int)getNumber(mb_substr($summary, 0, $delimiter_pos));
                $chapter_title = trim(mb_substr($summary, $delimiter_pos + mb_strlen(self::DELIMITER)));
            } elseif(is_number($summary)) {
                // 只有章节序号
                $chapter_no = (int)getNumber($summary);
                $chapter_title = '';
            } else {
                // 无用的行 ... PS: 谁叫我？       ---- 吴用 留
                $chapter_no = 0;
                $chapter_title = '';
            }
            // 检查章节是否可用
            if($chapter_no > 0) {
                if($chapter_no > 100000) {
                    ++$this->skip_count;
                    $this->fail_detail[] = [$client_original_name => '章节序号过大，请检查'];
                    return false;
                }
                $this->chapter_no_max = max($this->chapter_no_max, $chapter_no);
                $chapter = NovelSection::where([
                    ['novel_id', '=', $this->novel_id],
                    ['num', '=', $chapter_no]
                ])->first();
                // 最后再统一更新内容节点
                if($chapter) {
                    // update
                    if(isset($this->allow_states[$this->override_allow]) && $this->allow_states[$this->override_allow]['value']) {
                        // override
                        $update_data = [
                            'updated_num' => DB::raw('updated_num + 1'),
                            'updated_at' => time()
                        ];
                        if (!empty($chapter_title) && $chapter_title != $chapter->title) {
                            $update_data['title'] = $chapter_title;
                        }
                        try {
                            $res = NovelSection::where('id', '=', $chapter->id)->update($update_data);
                            if (!$res) {
                                ++$this->failure_count;
                                $this->fail_detail[] = [$client_original_name => '更新数据失败'];
                                return false;
                            }
                            ++$this->override_count;
                        } catch (\Exception $e) {
                            ++$this->failure_count;
                            $this->fail_detail[] = [$client_original_name => '更新数据失败，原因：' . $e->getMessage()];
                            return false;
                        }
                    } else {
                        // skip
                        ++$this->skip_count;
                        $this->fail_detail[] = [$client_original_name => '跳过'];
                        return false;
                    }
                } else {
                    // insert
                    $current_time = time();
                    $chapter = [
                        'novel_id'  =>  $this->novel_id,
                        'num'       =>  $chapter_no,
                        'title'     =>  $chapter_title,
                        'content'   =>  '',
                        'updated_num'   =>  0,
                        'updated_at'    =>  $current_time,
                        'created_at'    =>  $current_time,
                        'spider_url'    =>  ''
                    ];
                    try {
                        $chapter = NovelSection::create($chapter);
                        if (!$chapter) {
                            ++$this->failure_count;
                            $this->fail_detail[] = [$client_original_name => '保存数据失败'];
                            return false;
                        }
                    } catch (\Exception $e) {
                        ++$this->failure_count;
                        $this->fail_detail[] = [$client_original_name => '保存数据失败，原因：' . $e->getMessage()];
                        return false;
                    }
                }
                // 解析完成
                return ['summary_parsed' => true, 'chapter_id' => $chapter->id];
            } else {
                ++$this->skip_count;
                $this->fail_detail[] = [$client_original_name => '缺少章节序号'];
                return false;
            }
        }
        return false;
    }

    /**
     * 更新章节数据节点
     * @param int $chapter_id 章节ID
     * @param string $content_stream 章节具体内容
     * @param string $client_original_name 客户端原文件名
     * @param bool $summary_parsed 概要是否已解析
     * @return bool 处理结果
     */
    public function updateContent(int $chapter_id, string $content_stream, string $client_original_name, bool $summary_parsed) {
        if($summary_parsed) {
            // 上传并保存内容节点
            $file_name = 'novels/' . ceil($this->novel_id / 100) . '/'. ceil($chapter_id / 1000) . '/' . uniqid() . self::FORMAT;
            $res = Storage::put($file_name, $content_stream);
            if($res) {
                try {
                    $res = NovelSection::where('id', '=', $chapter_id)->update(['content' => Storage::url($file_name)]);
                    if (!$res) {
                        ++$this->failure_count;
                        $this->fail_detail[] = [$client_original_name => '更新数据失败'];
                        return false;
                    }
                    Novel::where([
                        ['id', '=', $this->novel_id],
                        ['sections', '<', $this->chapter_no_max]
                    ])->update(['sections' => $this->chapter_no_max]);
                    ++$this->success_count;
                    return true;
                } catch (\Exception $e) {
                    ++$this->failure_count;
                    $this->fail_detail[] = [$client_original_name => '更新数据失败，原因：' . $e->getMessage()];
                    return false;
                }
            } else {
                ++$this->failure_count;
                $this->fail_detail[] = [$client_original_name => '保存章节失败'];
                return false;
            }
        } else {
            ++$this->skip_count;
            $this->fail_detail[] = [$client_original_name => '没有正确内容'];
            return false;
        }
    }

    /**
     * Build a form here.
     */
    public function form()
    {

        $this->number('novel_id', __('小说ID'))->required();
        $this->switch('override_allow', __('允许覆盖'))->states($this->allow_states)->required();
        $this->multipleFile(self::SECTIONS_FIELD, '请选择文件')
            ->help($this->file_notice)
            ->removable()
            ->required();
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return [
            'novel_id'              =>  '',
            'override_allow'        =>  $this->allow_states[$this->override_allow]['value'],
            self::SECTIONS_FIELD    =>  ''
        ];
    }

    public function spaceTransform(string $str) {
        if(!mb_strlen($str)) {
            return '';
        }
        return str_replace([' '], self::SPACE_HTML, $str);
    }

    /**
     * 从缩进标签获取缩进属性
     * @param \DOMNamedNodeMap $ind_attributes
     * @return int 缩进值
     */
    public function getIndentation($ind_attributes) {
        foreach($ind_attributes as $ind_attribute_name => $ind_attribute) {
            if(stripos('w:firstLineChars', $ind_attribute_name) !== false) {
                $this->indentation_array[$ind_attribute->value] = self::INDENTATION_UNIT;
                return $ind_attribute->value;
            }
        }
        return 0;
    }

    /**
     * 获取缩进标记
     * @param int $size 缩进值
     * @return string
     */
    public function getIndentationTag($size) {
        return '[[indentation'.$size.']]';
    }

    public function getNewLineTag() {
        return '[[newline]]';
    }
}
