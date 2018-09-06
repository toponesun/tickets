<?php
class Ajax
{
    public $act,$arr,$result;
    function __construct($Ajax){
        //加载网站配置
        //$data = Mysql::query("select * from sys_web_set",1);
        if (!empty($data)){
            define("TITLE_SUFFIX", $data[0]["title_suffix"]);//网站标题后缀
            define("SESSION_LIVE_TIME",$data[0]["session_live_time"]);//session存活时间
            define("ORDER_LIVE_TIME", $data[0]["order_live_time"]);//订单失效时间(秒)
            define("RND_KEY_LIVE_TIME", $data[0]["rnd_key_live_time"]);//随机码失效时间(秒)
            define("ROWS_PER_PAGE", $data[0]["rows_per_page"]);//每页显示数据条数
            define("FOOTER_TEXT", $data[0]["footer"]);//网页底部文字
            define("VALID_INTERVAL", $data[0]["valid_interval"]);//验票间隔
            define("NEED_OUT_VALID", $data[0]["need_out_valid"]);//需要验出站
        }else{
            define("TITLE_SUFFIX", "_票务系统");
            define("SESSION_LIVE_TIME",3600);//session存活时间
            define("ORDER_LIVE_TIME", 3600);
            define("RND_KEY_LIVE_TIME", 60);
            define("ROWS_PER_PAGE", 5);
            define("FOOTER_TEXT", "票务系统");
            define("VALID_INTERVAL", 0);//验票间隔
            define("NEED_OUT_VALID", 0);//需要验出站
        }
        //初始化返回数组
        $this->arr = ["code"=>0,"msg"=>"初始返回值，请求未找到,time:".time(),"data"=>[]];
        $this->arr["request"] = $Ajax;//仅供测试使用
    }

    //需要判断是否登录
    static function isLogin(&$result,$Request){
        //先判断是否传入端口名
        if (empty($Request["client"])){
            $result["code"] = 0;
            $result["msg"] = "必须传入有效的端口名！";
            return false;
        }
        //再判断此端口名下是否已登录用户
        if (empty($_SESSION[$Request["client"]]["uid"])){
            $result["code"] = 0;
            $result["msg"] = "尚未登录不允许此操作！";
            return false;
        }
        return true;
    }


    //数组转化成可识别的json
    protected function arrToJson(){
        //$this->arrayRecursive($this->arr, 'urlencode', true);
        $result = urldecode(json_encode($this->arr));
        return $result;
    }

    //urldecode
    protected function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 100) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }

}