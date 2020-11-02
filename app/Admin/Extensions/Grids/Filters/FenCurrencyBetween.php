<?php

namespace App\Admin\Extensions\Grids\Filters;

use Encore\Admin\Grid\Filter\Between;

class FenCurrencyBetween extends Between
{
    // 重载condition方法，https://github.com/z-song/laravel-admin/issues/2016
    public function condition($inputs)
    {
        // $inputs即为传进来的参数，格式化成timestamp再去构建条件

        if (!array_has($inputs, $this->column)) {
            return;
        }

        $this->value = array_get($inputs, $this->column);

        $value = array_filter($this->value, function ($val) {
            return $val !== '';
        });

        if (empty($value)) {
            return;
        }

        if (!isset($value['start'])) {


            $value['end'] = $value['end'] * 100;//数据库单位为分

            return $this->buildCondition($this->column, '<=', $value['end']);
        }

        if (!isset($value['end'])) {

            $value['start'] = $value['start'] * 100;//数据库单位为分

            return $this->buildCondition($this->column, '>=', $value['start']);
        }

        $this->query = 'whereBetween';

        $value['end'] = $value['end'] * 100;//数据库单位为分
        $value['start'] = $value['start'] * 100;//数据库单位为分


        //return $this->buildCondition($this->column, $this->value);
        //这里需要注意$this->value的值会作用于页面reset按钮，不能直接修改这个值，否则会导致按reset回显原来的单位-分
        return $this->buildCondition($this->column, $value);


    }
}