<?php

namespace App\Admin\Extensions\Grids\Custom;

use App\Admin\Models\CommonSet;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExpoter extends AbstractExporter {
	public function export() {
		Excel::create('测试列表 ' . date('Y-m-d H:i:s'), function ($excel) {

			$excel->sheet('Sheetname', function ($sheet) {
				$plan = request()->input('plan');
				$plan = $plan ? $plan : 'open';
				switch ($plan) {
				case 'open':
					$model = new CommonSet();
					break;
				}
				$host = $model->where('type', 'export_host')->where('name', $plan)->first();
				$host = $host['value'];
				$data = $this->getData();
				foreach ($data as $k => $v) {
					if (!$v['status']) {
						unset($data[$k]);
						continue;
					}
					$data[$k]['id'] = $host . $v['id'];
				}
				// 这段逻辑是从表格数据中取出需要导出的字段
				$rows = collect($data)->map(function ($item) {
					return array_only($item, ['id', 'name']);
				});

				$sheet->rows($rows);

			});

		})->export('xls');
	}
}