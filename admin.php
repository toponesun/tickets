<?php
include_once "base.php";
//require_once "app/User.php";
require_once "app/admin/controller/Tickets.php";
require_once "app/admin/controller/Scenic.php";
require_once "app/admin/controller/Business.php";
require_once "app/admin/controller/Sale.php";
require_once "app/admin/controller/Device.php";
require_once "app/admin/controller/File.php";
require_once "app/admin/controller/WebSet.php";
require_once "app/admin/controller/Actions.php";
require_once "app/admin/controller/Template.php";
require_once "app/admin/controller/ReportForm.php";
require_once "app/admin/controller/System.php";

//加载网站配置
$data = Mysql::query("select * from sys_web_set",1);
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

if (empty($_SESSION["admin"])){
    $error = <<<LL
    <script>
        alert("请先登录才能进行操作！");
        window.location.href = "login.html";
    </script>
LL;
    exit($error);
}

new System($_GET,$_POST);
var_dump($_SESSION);