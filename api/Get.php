<?php
class Get extends Ajax
{
    function __construct($GET)
    {
        parent::__construct($GET);
        $this->act = empty($GET["act"])?"":$GET["act"];
        $this->switchAct($GET);

        $this->result = $this->arrToJson();
    }

    function switchAct($GET){
        switch ($this->act){
            case "sys_set":
                Sys::getSysSet($this->arr);
                break;
            case "user_info":
                User::getUserInfo($this->arr,$GET);
                break;
            case "logout":
                User::logout($this->arr,$GET);
                break;
            //可选参数：p页数,limit每页行数,stock是否只显示有库存,title标题类似,type票券种类,scenic景点,times次数,price价格,date_range时间范围,state票券状态。。。
            case "ticket_list":
                Ticket::getTicketList($this->arr,$GET);
                break;
            //票券商品查找条件
            case "ticket_term":
                Ticket::getTicketTerm($this->arr,$GET);
                break;
            //XXX可选参数：p页数,limit每页行数,stock是否只显示有库存,title标题类似,type票券种类,scenic景点,times次数,price价格,date_range时间范围,state票券状态。。。
            case "scenic_list":
                Scenic::getScenicList($this->arr,$GET);
                break;
            case "cart_list":
                Cart::getCartList($this->arr,$GET);
                break;
            case "order_list":
                Order::getOrderList($this->arr,$GET);
                break;
            case "my_tic_list":
                MyTic::getMyTicList($this->arr,$GET);
                break;
            case "trade_list":
                Trade::getTradeList($this->arr,$GET);
                break;
            case "confirm_list":
                Cashier::getConfirmList($this->arr,$GET);
                break;
            case "ticket_detail":
                Ticket::getTicketDetail($this->arr,$GET);
                break;
            case "order_detail":
                Order::getOrderDetail($this->arr,$GET);
                break;

            case "my_tic_detail":
                MyTic::getMyTicDetail($this->arr,$GET);
                break;

            case "report_data":
                Trade::getReportData($this->arr,$GET);
                break;




            case "test":


                $this->arr["data"] = Cart::getSaleInfoByCart();
                break;


            default:
                $this->arr["code"] = 0;
                $this->arr["msg"] = "接口不存在";
                break;
        }
    }


}