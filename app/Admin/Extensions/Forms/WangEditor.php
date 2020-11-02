<?php

namespace App\Admin\Extensions\Forms;

use Encore\Admin\Form\Field;

class WangEditor extends Field {
	protected $view = 'admin.extensions.forms.wang-editor';

	protected static $css = [
		'/vendor/wangEditor-3.1.1/release/wangEditor.min.css',
	];

	protected static $js = [
		'/vendor/wangEditor-3.1.1/release/wangEditor.min.js',
	];

	public function render() {
		$name = $this->formatName($this->column);

		$this->script = <<<EOT

var E = window.wangEditor
var editor = new E('#{$this->id}');
editor.customConfig.zIndex = 0
editor.customConfig.uploadImgServer = '/administrator/editorupload'
editor.customConfig.onchange = function (html) {
    $('input[name=\'$name\']').val(html);
}
editor.create()

EOT;
		return parent::render();
	}
}