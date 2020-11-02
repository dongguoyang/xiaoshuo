<?php
/**
 * Created by LWL.
 * User: LUWENLONG
 * Date: 2019/3/18
 * Time: 14:38
 */

namespace App\Libraries\Share;


class Secret
{
    /**
     * 创建一个TOKEN
     * @return array
     * @throws \Exception
     */
    public function create($key = null)
    {
        $data = [
            'ip'        => request()->getClientIp(),
            'share_id'  => request()->input('share_id'),
            'user_id'   => request()->input('user_id'),
            'ua'        => request()->server('HTTP_USER_AGENT'),
            'key'       => $key ?: date('dHis') . $this->code(10, 1),
        ];
        ksort($data);
        $data = array_prepend($data, '784152jfdweDOdeo', 'ZMRToken');
        $string = http_build_query($data);
        return ['passwd' => md5($string), 'key' => $data['key']];
    }

    public function check()
    {
        $key = request()->input('key');
        $passwd = request()->input('passwd');

    }

    /**
     * 生成随机码
     * @param int $length   随机码长度
     * @param int $type     类型 -1 数字+大小写字母+特殊字符 0 数字+大小写字母 1 数字 2 小写字母 3 大写字母 4特殊字符 5 16进制字符
     * @return string
     * @throws \Exception
     */
    public function code($length = 18, $type = 0)
    {
        $arr = [
            1 => '0123456789',
            2 => 'abcdefghijklmnopqrstuvwxyz',
            3 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            4 => '~@#$%^&*(){}[]|',
            5 => '0123456789abcdef',
        ];
        switch ($type) {
            case 0:
                $array = [$arr[1], $arr[2], $arr[3]];
                $string = implode('', $array);
                break;
            case -1:
                $string = implode("", $arr);
                break;
            default:
                if ($type < 1 || $type > 5)
                {
                    throw new \Exception('Type error');
                }
                $string = $arr[$type];
                break;
        }

        $count = strlen($string) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }
}