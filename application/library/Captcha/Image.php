<?php

namespace Captcha;

/**
* 验证码画图类
*/
class Image {
    private $width;
    private $height;
    private $im;
    private $bg;
    private $m;
    private $type = array('num' => 9,'letter' => 61 );
    public $code;

    function __construct($width, $height, $m){
        $this->width = $width;
        $this->height = $height;
        $this->m = $m;
        $this->im = imagecreatetruecolor($this->width, $this->height);
        $this->bg = imagecolorallocate($this->im, 220, 220, 220);
    }

    function getCode(){
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $t = '';
        for($i = 0; $i < $this->m; $i++){
            $t .= $str[rand(0, $this->type['letter'])];
        }

        $this->code = $t;
        return $t;
    }

    function drawCode(){
        imagefill($this->im, 0, 0, $this->bg);
        //加干扰点
        for ($i = 0; $i < 200 ; $i++) {
            imagesetpixel($this->im, rand(0, $this->width), rand(0, $this->height), rand(0, 255));
        }

        //加干扰线
        imageline($this->im, rand(0, $this->width), rand(0, $this->height), rand(0, $this->width), rand(0, $this->height), rand(0, 255));
        $c = imagecolorallocate($this->im, rand(0, 200), rand(0, 200), rand(0, 200));
        for($i = 0; $i < $this->m; $i++){
            imagettftext($this->im, 18, rand(-40, 40), 8+(18*$i), 24, $c, __dir__.'/assets/ttfs/2.ttf', $this->code[$i] );
        }
    }

    function printImage(){
        $this->drawCode();
        header("Content-Type:image/jpeg");
        imagepng($this->im);
        $this->destroy();
    }

    function destroy(){
        imagedestroy($this->im);
    }
}