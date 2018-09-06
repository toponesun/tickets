<?php
class Post extends Ajax
{
    function __construct($POST)
    {
        parent::__construct($POST);
        $this->act = empty($POST["act"])?"":$POST["act"];
        $this->switchAct($POST);
        $this->result = $this->arrToJson();
    }

    function switchAct($POST){
        switch ($this->act){
            case "login":
                User::Login($this->arr,$POST);
                break;
            case "cart_ctrl":
                Cart::cartCtrl($this->arr,$POST);
                break;
            case "create_order":
                Cashier::createOrder($this->arr,$POST);
                break;
            case "refund_order":
                Order::refundOrder($this->arr,$POST);
                break;
            case "del_order":
                Order::delOrder($this->arr,$POST);
                break;
            case "pay_order":
                Cashier::payOrder($this->arr,$POST);
                break;

            case "invalid_tic":
                MyTic::invalidTic($this->arr,$POST);
                break;



            default:
                break;
        }
    }
}