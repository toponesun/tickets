<?php
include_once "../../../base.php";
//require_once "../User.php";
require_once "Tickets.php";
require_once "Scenic.php";
require_once "Business.php";
require_once "Cart.php";
require_once "Cashier.php";
require_once "MyTickets.php";
require_once "MyOrders.php";
require_once "TradeRec.php";
require_once "Coupon.php";
require_once "Favor.php";
require_once "History.php";
require_once "Actions.php";
require_once "Template.php";
require_once "System.php";
require_once "MobileCart.php";
require_once "MobileActions.php";
require_once "MobileTemplate.php";
require_once "MobileSystem.php";
define("UID",$_SESSION["customer"]["uid"]);
$data = empty($_POST)?[]:$_POST;
$ajax = new Ajax($data);

class Ajax
{
    public $data,$json,
        $oid,$tid,
        $act,$action_key,
        $ticket_key;

    function __construct($data){
        $this->data = $data;
        foreach ($data as $key=>$value){
            $this->$key = $value;
        }
        if(!empty($data["data"])){
            foreach ($data["data"] as $key=>$value){
                $this->$key = $value;
            }
        }
        echo $this->switchAct();
    }

    function switchAct(){
        $p = empty($this->data['p']) ? 1 : $this->data['p'];
        switch ($this->action_key){

            //刷新订单页面
            case "refOrders":
                $result = MobileActions::select_area(json_decode($this->json));
                $orders_data = MyOrders::getMyOrdersInfo(json_decode($this->json));
                $result .= MobileTemplate::getTemp("myOrders",$orders_data,$p,"没有此类订单...");
                $result .= Actions::getPageHtml($p, MyOrders::$my_orders_page, "", $this->data);
                break;
            case "refOrdersPC":
                $orders_data = MyOrders::getMyOrdersInfo($this->data);
                $result = Template::getTemp("myOrders",$orders_data,$p,"没有此类订单...");
                $result .= Actions::getPageHtml($p, MyOrders::$my_orders_page, "", $this->data);
                break;
            //刷新票券使用情况
            case "chkTic":
                $result = MyTickets::getNextTicketKey($this->ticket_key);
                break;
            //购物车控制器
            case "cartControl":
                Cart::cartControl($this->tid,$this->act);
                $result["html"] = Cart::getCartInfo();
                $result["num"] = Cart::getCartNum();
                $result["num"] = ($result["num"]>99)?"99+":$result["num"];
                $result = json_encode($result);
                break;
            //购物车控制器-m
            case "m_cartControl":
                Cart::cartControl($this->tid,$this->act);
                $result = MobileCart::getCartInfo();
                break;
            case "addToCart":
                Cart::cartControl($this->tid,"+1");
                $result = Cart::getCartNum();
                break;
            //订单控制
            case "orderCtrl":
                $result = $this->orderCtrl();
                break;
            //请求生成订单
            case "createOrder":
                $result = Cashier::createOrderByCart();
                break;
            //支付订单
            case "payOrder":
                $result = Cashier::payOrder($this->oid);
                break;
            //收藏商品
            case "addFavor":
                $result = Favor::ctrlFavor($this->data);
                break;
            //浏览记录控制
            case "hisCtrl":
                $history_data = History::hisCtrl();
                $result = MobileTemplate::getTemp('Ticket',$history_data);
                break;
            //检查订单是否可以退票
            case "checkRefund":
                $result = MyOrders::checkRefund($this->oid);
                break;

            //系统测试验票功能
            case "ticketValid":
                $result = Actions::ticketValid($this->data);
                break;

            case "createOrderByJson":
                $result = Cart::getSaleInfoByJson($this->data["goods_json"]);
                $result = json_encode($result);
                break;


            //PC端ajax
            case "getTicketsDetail":
                $result = Tickets::getTicketsDetail($this->data);
                break;
            case "getOrderDetail":
                $detail_data = MyOrders::getOrderDetail($this->data);
                $result = MobileTemplate::getTemp("orderDetail",$detail_data,1,"此订单下无票券！");
                break;
            case "getMyTicDetail":
                $detail_data = MyTickets::getTicketDetail($this->data);
                $result = MobileTemplate::getTemp("ticDetail",$detail_data,1,"未找到此票券！");
                break;

            case "upShowMenu":
                $uid = UID;
                $result = Mysql::query("update user_customer set show_menu = (show_menu + 1)%2 where uid = '$uid'");
                break;
            case "upShowCart":
                $uid = UID;
                $result = Mysql::query("update user_customer set show_cart = (show_cart + 1)%2 where uid = '$uid'");
                break;
            default:
                $result = false;
                break;
        }
        return $result;
    }


    function orderCtrl(){
        switch($this->act){
            //取消订单或退票
            case 1:
                $result = MyOrders::refundOrders($this->oid);
                break;
            //删除订单
            case 2:
                $result = MyOrders::delOrders($this->oid);
                break;
            //还原订单
            case 3:
                $result = MyOrders::recOrders($this->oid);
                break;
            default:
                $result = "未定义的订单操作";
                break;
        }
        return $result;
    }

}

