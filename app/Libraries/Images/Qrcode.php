<?php
/**
 * Created by LWL.
 * User: LUWENLONG
 * Date: 2019/3/14
 * Time: 17:32
 * 二维码生成封装
 * $qrcode = new Qrcode();
 * $qrcode->setSize(200)->create($face, $url, $id)
 */

namespace App\Libraries\Images;


use Intervention\Image\ImageManagerStatic;

class Qrcode
{
    private $size;

    private $face;

    private $url;

    private $id;

    private $path;
    /**
     * @param $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param $face
     * @return $this
     */
    public function setFace($face)
    {
        $this->face = $face;
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function create($face = null, $url = null, $id = null)
    {
        if ($face != null)  $this->setFace($face);
        if ($url != null)  $this->setUrl($url);
        if ($id != null)  $this->setId($id);

        if (!$this->path) $this->setPath('upload/userreg/qrcode' . $this->id . '.jpg');

        $Qrcode = new \SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
        @chmod($this->path, 0777);
        $Qrcode->format(0)->size($this->size)->margin(0)->merge($this->face, .12)->generate($this->url, public_path($this->path));

        return $this->path;
    }
    
    /**
     * 生产制定内容的二维码
     * @param unknown $content
     */
    public function create_qrcode($content) {
        $Qrcode = new \SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
        $Qrcode->format('png')->size(300)->margin(0)->generate($content, public_path($this->path));
        return $this->path;
    }

    /**
     * 图片更改尺寸
     * @param array $option  ['img'=> '源图片', 'width'=>'重置后的宽度', 'height'=>'重置后的高度']
     * @return mixed
     */
    public function resizeImg($option = [])
    {
        $image = ImageManagerStatic::make($option['img'])->resize($option['width'], $option['height']);
        $image->save($option['img']);
        return $option['img'];
    }

    /**
     * 合成图片
     * @param array $option
     * @return mixed
     */
    public function insertPic($option = [])
    {
//        $option = [
//            'width'     => 600,
//            'height'    => 800,
//            'bg'        => '',       //背景图文件名
//            'qr'        => '',       //二维码文件名
//            'complete'  => '',       //完成后的文件名
//            'left'      => '',       //左边距
//            'top'       => '',       //上边距
//        ];
        $image = ImageManagerStatic::make($option['bg'])->resize($option['width'], $option['height']);
        $image->insert($option['qr'], 'top-left', $option['left'], $option['top']);
        $image->save($option['complete']);
        return $option['complete'];
    }
}