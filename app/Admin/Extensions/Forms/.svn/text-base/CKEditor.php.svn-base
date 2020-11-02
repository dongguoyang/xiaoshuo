<?php

namespace App\Admin\Extensions\Forms;

use Encore\Admin\Form\Field;

class CKEditor extends Field
{
    public static $js = [
        '/vendor/ckeditor-4.11.2_full/ckeditor.js',
        '/vendor/ckeditor-4.11.2_full/adapters/jquery.js',
        '/vendor/ckeditor-4.11.2_full/adapters/config.js',
    ];

    protected $view = 'admin.ckeditor';

    public function render()
    {
        $this->script = "$('textarea.{$this->getElementClassString()}').ckeditor();";

        return parent::render();
    }
}