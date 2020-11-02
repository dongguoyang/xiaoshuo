<?php

namespace App\Admin\Extensions\Forms;

use Encore\Admin\Form\Field\Currency;

class FenCurrency extends Currency {
	protected $symbol = '￥';

	/**
	 * Prepare for a field value before update or insert.
	 * 保存之前将值乘以 100
	 * @param $value
	 *
	 * @return mixed
	 */
	public function prepare($value) {
		return $value * 100;
	}

	public function render() {
		$options = json_encode($this->options);

		$this->script = <<<EOT

$('{$this->getElementClassSelector()}').inputmask($options);

EOT;

		$this->prepend($this->symbol)
			->defaultAttribute('style', 'width: 120px')
			->defaultAttribute('value', old($this->column, $this->value() / 100));

		return parent::render();
	}
}