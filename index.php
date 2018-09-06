<?php
include_once "base.php";
//require_once "app/User.php";
require_once "app/customer/controller/Tickets.php";
require_once "app/customer/controller/Scenic.php";
require_once "app/customer/controller/Business.php";
require_once "app/customer/controller/Cart.php";
require_once "app/customer/controller/Cashier.php";
require_once "app/customer/controller/MyTickets.php";
require_once "app/customer/controller/MyOrders.php";
require_once "app/customer/controller/TradeRec.php";
require_once "app/customer/controller/Coupon.php";
require_once "app/customer/controller/Favor.php";
require_once "app/customer/controller/History.php";
require_once "app/customer/controller/Actions.php";
require_once "app/customer/controller/Template.php";
require_once "app/customer/controller/System.php";
require_once "app/customer/controller/MobileCart.php";
require_once "app/customer/controller/MobileActions.php";
require_once "app/customer/controller/MobileTemplate.php";
require_once "app/customer/controller/MobileSystem.php";

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

if (empty($_SESSION["customer"])){
    $error = <<<LL
    <script>
        alert("请先登录才能进行操作！");
        window.location.href = "login.html";
    </script>
LL;
    exit($error);
}
define("UID",$_SESSION["customer"]["uid"]);
if (Actions::isMobile()){
    new MobileSystem($_GET,$_POST);
}else{
    new System($_GET,$_POST);
}


var_dump($_SESSION);
//print_r(Cart::getTicInfoByCart());