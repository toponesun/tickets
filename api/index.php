<?php
require_once "../base.php";
require_once "Ajax.php";
require_once "Post.php";
require_once "Get.php";
require_once "class/Sys.php";
require_once "class/User.php";
require_once "class/Ticket.php";
require_once "class/Scenic.php";
require_once "class/Cart.php";
require_once "class/Cashier.php";
require_once "class/Order.php";
require_once "class/MyTic.php";
require_once "class/Trade.php";

/*echo "<pre>";
print_r(Cart::getSaleInfoByCart());*/


if (!empty($_POST)){
    $request = new Post($_POST);
    $result = $request->result;
    exit($result);
}elseif (!empty($_GET)){
    $request = new GET($_GET);
    $result = $request->result;
    exit($result);
}else{
    $result = ["code"=>0,"msg"=>"","data"=>[]];
    exit(json_encode($result));
}