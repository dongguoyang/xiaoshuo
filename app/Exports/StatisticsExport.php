<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StatisticsExport implements FromCollection, WithHeadings, WithStrictNullComparison, WithEvents
{
    use Exportable;

    private $data;
    private $headings;

    //数据注入
    public function __construct($data, $headings)
    {
        $this->data = $data;
        $this->headings = $headings;
    }

    //实现FromCollection接口
    public function collection()
    {
        return collect($this->data);
    }

    //实现WithHeadings接口
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                // 排列方式
                $event->sheet->getDelegate()->getStyle('A1:D21')->getAlignment()->setVertical('center');
                $event->sheet->getDelegate()->getStyle('A2:A21')->getAlignment()->setHorizontal('center');
                $event->sheet->getDelegate()->getStyle('A1:D1')->getAlignment()->setHorizontal('center');
                // 表头字体
                $event->sheet->getDelegate()->getStyle('A1:D1')->applyFromArray([
                    'font' => [
                        'name'  =>  '华文新魏',
                        'bold'  =>  true,
                        'strikethrough' => false,
                        'color' =>  [
                            'rgb'   =>  'FF5722'
                        ]
                    ],
                ]);
                // 设置 A2:D21 范围内文本自动换行
                $event->sheet->getDelegate()->getStyle('A1:D21')
                    ->getAlignment()->setWrapText(true);
                // 列宽
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(30);
                // 行高
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(20);
                for ($i = 2; $i <= 21; $i++) {
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(120);
                }
            },
        ];
    }
}
