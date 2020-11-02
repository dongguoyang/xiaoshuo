<?php
/**
 * RSA 加密组件
 * @notice 注意：对于secret字段的加密格式为：my_base64_encode( RSA( json_encode( ['field1' => 'value1', 'field2' => 'value2', ...])))
 *          可加密任意长度的数据，但是仍然要注意，不要应用RSA到过长的数据中，RSA其实比较消耗资源的。加密较大数据，可以采用 RSA + 对称加密 解决，即RSA加密随机生成的会话对称加密秘钥，使用此秘钥去执行数据加密工作
 */
namespace App\Libraries\Openssl;

if (! function_exists('url_safe_base64_encode')) {
    function url_safe_base64_encode ($data) {
        return str_replace(array('+','/', '='),array('-','_', ''), base64_encode($data));
    }
}

if (! function_exists('url_safe_base64_decode')) {
    function url_safe_base64_decode ($data) {
        $base_64 = str_replace(array('-','_'),array('+','/'), $data);
        return base64_decode($base_64);
    }
}

class RSA {
    const CHAR_SET = 'UTF-8';
    const BASE_64_FORMAT = 'UrlSafeNoPadding';
    const RSA_ALGORITHM_KEY_TYPE = OPENSSL_KEYTYPE_RSA;
    const RSA_ALGORITHM_SIGN = OPENSSL_ALGO_SHA512;
    const RSA_PADDING = OPENSSL_PKCS1_PADDING; // 请勿修改PADDING 模式，不同的PADDING对应不同的分段长度

    protected $publicKey;
    protected $privateKey;
    protected $keyLen; // 请勿修改PADDING 模式，不同的PADDING对应不同的分段长度

    /**
     * RSA 初始化
     * @param string $pub_key 公钥，路径或公钥字符串
     * @param string $pri_key 私钥，路径或私钥字符串
     * @param string $passphrase 私钥密码
     */
    public function __construct($pub_key = '', $pri_key = '', $passphrase = '') {
        $pub_key = $pub_key ?: config('app.rsa_public_key');
        $pri_key = $pri_key ?: config('app.rsa_private_key');
        $passphrase = $passphrase ?: config('app.rsa_passphrase');
        $this->publicKey = openssl_pkey_get_public($pub_key);
        $this->privateKey = openssl_pkey_get_private($pri_key, $passphrase);
        $this->keyLen = openssl_pkey_get_details($this->publicKey)['bits'];
    }

    /**
     * 创建密钥对
     * @param int $key_size 私钥位数：512, 1024, 2048, 3072, 4096
     * @param string $passphrase 私钥密码
     * @return bool|array 失败返回false，成功则返回秘钥对
     */
    public static function createKeys(int $key_size = 2048, string $passphrase = '') {
        $passphrase = $passphrase ?: config('app.rsa_passphrase');
        $config = [
            'digest_alg'        =>  'sha512',
            'private_key_bits'  =>  $key_size,
            'private_key_type'  =>  self::RSA_ALGORITHM_KEY_TYPE
        ];
        $res_id = openssl_pkey_new($config);
        if(false === $res_id) {
            return false;
        }
        $res = openssl_pkey_export($res_id, $private_key, $passphrase);// 加密私钥
        if(false === $res) {
            return false;
        }
        $public_key_detail = openssl_pkey_get_details($res_id);
        if(false === $public_key_detail) {
            return false;
        }
        $public_key = $public_key_detail['key'];

        return [
            'public_key'    =>  $public_key,
            'private_key'   =>  $private_key
        ];
    }

    /**
     * 公钥加密
     * @param string $data 待加密的数据
     * @return bool|string 成功则返回加密数据[定制的base64编码]，失败则返回false
     */
    public function publicEncrypt($data) {
        $encrypted = '';
        $part_len = $this->keyLen / 8 - 11;
        $parts = str_split($data, $part_len);

        foreach ($parts as $part) {
            $encrypted_temp = '';
            $res = openssl_public_encrypt($part, $encrypted_temp, $this->publicKey, self::RSA_PADDING);
            if(false === $res) {
                return false;
            }
            $encrypted .= $encrypted_temp;
        }

        return url_safe_base64_encode($encrypted);
    }

    /**
     * 私钥解密
     * @param string $encrypted 待解密的数据[定制的base64编码]
     * @return bool|string 成功则返回解密后数据，失败则返回false
     */
    public function privateDecrypt($encrypted) {
        $decrypted = '';
        $part_len = $this->keyLen / 8;
        $base64_decoded = url_safe_base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);

        foreach ($parts as $part) {
            $decrypted_temp = '';
            $res = openssl_private_decrypt($part, $decrypted_temp, $this->privateKey, self::RSA_PADDING);
            if(false === $res) {
                return false;
            }
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }

    /**
     * 私钥加密
     * @param string $data 待加密的数据
     * @return bool|string 成功则返回加密数据[定制的base64编码]，失败则返回false
     */
    public function privateEncrypt($data) {
        $encrypted = '';
        $part_len = $this->keyLen / 8 - 11;
        $parts = str_split($data, $part_len);

        foreach ($parts as $part) {
            $encrypted_temp = '';
            $res = openssl_private_encrypt($part, $encrypted_temp, $this->privateKey, self::RSA_PADDING);
            if(false === $res) {
                return false;
            }
            $encrypted .= $encrypted_temp;
        }

        return url_safe_base64_encode($encrypted);
    }

    /**
     * 公钥解密
     * @param string $encrypted 待解密的数据[定制的base64编码]
     * @return bool|string 成功则返回解密后数据，失败则返回false
     */
    public function publicDecrypt($encrypted) {
        $decrypted = '';
        $part_len = $this->keyLen / 8;
        $base64_decoded = url_safe_base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);

        foreach ($parts as $part) {
            $decrypted_temp = '';
            $res = openssl_public_decrypt($part, $decrypted_temp, $this->publicKey, self::RSA_PADDING);
            if(false === $res) {
                return false;
            }
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }

    /**
     * 数据加签
     * @param string $data 待签名的数据
     * @return bool|string 成功则返回签名字符串[定制的base64编码]，失败则返回false
     */
    public function sign($data) {
        $res = openssl_sign($data, $sign, $this->privateKey, self::RSA_ALGORITHM_SIGN);
        if(false === $res) {
            return false;
        }
        return url_safe_base64_encode($sign);
    }

    /**
     * 数据签名验证
     * @param string $data 待验证的原数据
     * @param string $sign 待校验的签名
     * @return int 1 签名正确；0 签名错误；-1 发生其他错误
     */
    public function verify($data, $sign) {
        $pub_id = openssl_get_publickey($this->publicKey);
        $res = openssl_verify($data, url_safe_base64_decode($sign), $pub_id, self::RSA_ALGORITHM_SIGN);
        return $res;
    }
}