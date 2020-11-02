<?php
/**
 * 项目定制的 Snowflake，用于数据库模型自动生成唯一ID
 */

namespace App\Libraries\Snowflake;
use App\Libraries\Snowflake\Snowflake;

trait HasSnowflakePrimary {
    public static function bootHasSnowflakePrimary()
    {
        static::saving(function ($model) {
            if (is_null($model->getKey())) {
                $keyName    = $model->getKeyName();
                $id         = resolve(Snowflake::class)->next();
                $model->setAttribute($keyName, $id);
            }
        });
    }

    public static function insert($array)
    {
        $object = new self;
        foreach ($array as $key => $value)
        {
            $object->$key = $value;
        }
        $object->save();
    }
}