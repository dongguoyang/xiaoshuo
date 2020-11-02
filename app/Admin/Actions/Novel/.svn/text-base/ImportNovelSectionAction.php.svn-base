<?php

namespace App\Admin\Actions\Novel;

use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Encore\Admin\Form;

class ImportNovelSectionAction extends Action
{
    public $name = '导入章节';
    protected $selector = '.import-novel-section';

    public function handle(Request $request)
    {
        return $this->response()->redirect($this->href());
    }

    public function href()
    {
        return route('novel_sections_import_page');
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-default import-novel-section">导入章节</a>
HTML;
    }
}