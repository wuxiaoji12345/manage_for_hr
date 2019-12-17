<?php
/**
 * @PHP验证码类
 *
 * 使用方法：
 * $image=new captchaModel();
 * $image->config('宽度','高度','字符个数','验证码session索引');
 * $image->create();//这样就会向浏览器输出一张图片
 * 如：
 * new captchaModel(80,20,4,'captcha_code');
 * //所有参数都可以省略，
 * 默认是：宽80 高20 字符数4 验证码session索引captcha_code
 * 第四个参数即把验证码存到$_SESSION['captcha_code']
 * 最简单使用示例:
 * $image=new captchaModel();
 * $image->create();//这样就会向浏览器输出一张图片
 *
 * @author fankey 2017-09-27
 */
namespace app\common\controller;
class Captcha{


    //验证码宽度
    private $width = 80;

    //验证码高度
    private $height = 20;

    //验证码长度
    private $codenum = 4;

    //产生的验证码
    public $checkcode;

    //验证码图片
    private $checkimage;

    //干扰像素
    private $disturbColor = '';

    //存到session中的索引
    private $session_flag = 'captcha_code';

    //参与验证码运算的字符串
    private $captcha_str = '123456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ';



    /**
     * 构造函数
     */
    function __construct( $width = '80' , $height = '20' , $codenum = '4' , $session_flag = 'captcha_code' , $captcha_str = ''){

        @session_start();

        $this->config($width , $height , $codenum , $session_flag , $captcha_str);
    }



    /**
     * 配置验证码参数
     *
     * @param    string    $width           验证码宽度
     * @param    string    $height          验证码高度
     * @param    string    $codenum         验证码长度
     * @param    string    $session_flag    session中标识
     * @param    string    $captcha_str     参与加密的字符串
     */
    function config( $width = '80' , $height = '20' , $codenum = '4' , $session_flag = 'captcha_code' , $captcha_str = '' ){

        $this->width = $width;

        $this->height = $height;

        $this->codenum = $codenum;

        if( !empty($session_flag) ) {
            $this->session_flag = $session_flag;
        }

        if( !empty($captcha_str) ) {
            $this->captcha_str = $captcha_str;
        }
    }




    /**
     * 创建验证码
     */
    function create(){
        //输出头
        $this->outFileHeader();

        //产生验证码
        $this->createCode();

        //产生图片
        $this->createImage();

        //设置干扰像素
        $this->setDisturbColor();

        //往图片上写验证码
        $this->writeCheckCodeToImage();

        imagepng($this->checkimage);

        imagedestroy($this->checkimage);

        $_SESSION[$this->session_flag]=$this->checkcode;
    }




    /**
     * 输出头
     */
    private function outFileHeader(){
        header ("Content-type: image/png");
    }




    /**
     * 产生验证码
     */
    private function createCode(){
        $this->checkcode = strtolower($this->captcha_str);
        /*$code = $this->captcha_str;

        $string = '';

        for($i = 0 ; $i < $this->codenum; $i++) {

            $char = $code{rand(0, strlen($code)-1)};
            $string .= $char;
        }

        $this->checkcode = strtolower($string);*/
    }




    /**
     * 产生验证码图片
     */
    private function createImage(){

        $this->checkimage = @imagecreate($this->width,$this->height);

        $back = imagecolorallocate($this->checkimage,255,255,255);

        $border = imagecolorallocate($this->checkimage,0,0,0);

        //白色底
        imagefilledrectangle($this->checkimage,0,0,$this->width - 1,$this->height - 1,$back);

        //黑色边框
        imagerectangle($this->checkimage,0,0,$this->width - 1,$this->height - 1,$border);
    }




    /**
     * 设置图片干扰像素
     */
    private function setDisturbColor(){
        for ($i = 0 ; $i <= 200 ; $i++ ){

            $this->disturbColor = imagecolorallocate($this->checkimage, rand(0,255), rand(0,255), rand(0,255));

            imagesetpixel($this->checkimage , rand(2,128),rand(2,38) , $this->disturbColor);
        }
    }




    /**
     * 逐个绘制出验证码
     */
    private function writeCheckCodeToImage(){

        for ($i = 0 ; $i< $this->codenum ; $i++ ){
            $bg_color = imagecolorallocate ($this->checkimage, rand(0,255), rand(0,128), rand(0,255));
            $x = floor($this->width/$this->codenum) * $i;
            $y = rand(8, $this->height - 20);
            imagechar($this->checkimage, 5, $x + 5, $y, $this->checkcode[$i], $bg_color);
        }
    }




    /**
     * 析构函数
     */
    function __destruct(){
        unset($this->width,$this->height,$this->codenum,$this->session_flag);
    }
}