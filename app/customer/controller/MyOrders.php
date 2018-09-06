<?php

class MyOrders
{
    static $my_orders_page;
    static function getMyOrdersInfo($GET)
    {
        $order_state = !isset($GET['order_state']) ? null : $GET['order_state'];
        $visibility = empty($GET['visibility']) ? null : $GET['visibility'];
        $uid = UID;
        $sql_head = "select a.*,b.state_name from a_orders a left join app_order_state b on a.state = b.state_id where ";
        $sql_body = "a.uid = '$uid'";
        //取消状态包含2超时取消,4用户取消两种
        switch ($order_state){
            case "":
                break;
            case 2:
                $sql_body.= " and a.state in (2,4)";
                break;
            default:
                $sql_body.= " and a.state = $order_state";
                break;
        }
        $sql_body.= empty($visibility)?" and a.visibility = 1":" and a.visibility = $visibility";
        //判断日期是否合法
        if (!empty($GET["start_time"]) && !empty($GET["end_time"])){
            $GET["start_time"].=" 00:00:00";
            $GET["end_time"].=" 23:59:59";
            $sql_body.= !(strtotime($GET["start_time"]) && strtotime($GET["end_time"]))?"":" AND createTime >= '$GET[start_time]' AND createTime <= '$GET[end_time]'";
        }

        //按updateTime倒序排列
        $sql_body.=" order by a.create_time DESC";

        //计算页码
        $sql_count = "select count(*) from a_orders a where ".$sql_body;
        $num = Mysql::query($sql_count,1);
        self::$my_orders_page = ceil($num[0]["count(*)"] / ROWS_PER_PAGE);
        //获取范围内数据
        $begin_i = empty($GET["p"]) ? 0 : ($GET["p"]-1) * ROWS_PER_PAGE;
        $sql = $sql_head.$sql_body." limit $begin_i,".ROWS_PER_PAGE;
        $data = Mysql::query($sql,1);

        return $data;
    }

    //确定退单
    static function refundOrders($oid){
        $uid = UID;
        //有订单号开始处理
        $sql = "select * from a_orders where uid = '$uid' and oid = '$oid'";
        if($data = Mysql::query($sql,1)){
            switch ($data[0]["state"]){
                case -1:
                    $sql1 = "update a_orders set state = 4,cancel_time = now() where uid = '$uid' and oid = '$oid'";
                    $sql2 = "update a_mytickets set state = 4 where uid = '$uid' and oid = '$oid'";
                    $res1 = Mysql::query($sql1);
                    $res2 = Mysql::query($sql2);
                    if($res1&&$res2){
                        $content = "取消成功！";
                    }else{
                        $content = "出现了未知错误，请联系客服，并提供此订单号：$oid";
                    }
                    break;
                case 1:
                    if (!self::checkRefund($oid)){
                        return "此订单已无法退款！";
                    }
                    $order_money = $data[0]["price"];
                    $trade_num = date("YmdHis") . $uid;
                    $sql1 = "update a_orders set state = 3,cancel_time = now() where uid = '$uid' and oid = '$oid'";
                    $sql2 = "update a_mytickets set state = 3 where uid = '$uid' and oid = '$oid'";
                    $sql3 = "update user_customer set money = money + '$order_money' where uid = '$uid'";
                    $sql4 = "insert into a_trade_rec(uid,trade_num,oid,create_time,money,payment,type) values('$uid','$trade_num','$oid',now(),'$order_money','余额退款','订单退款')";
                    //$sql5 = "update a_tickets a,a_orders b,a_mytickets c set a.inventory = a.inventory + c.count(*) where b.oid = '$oid' b.oid = c.oid and c.tid = a.tid";
                    $res1 = Mysql::query($sql1);
                    $res2 = Mysql::query($sql2);
                    $res3 = Mysql::query($sql3);
                    $res4 = Mysql::query($sql4);
                    if($res1&&$res2&&$res3&&$res4){
                        $content = "退单成功！";
                    }else{
                        $content = "出现了未知错误，请联系客服，并提供此订单号：$oid";
                    }
                    break;
                case 2:
                    $content = "此订单已取消，请不要重复操作！";
                    break;
                case 3:
                    $content = "此订单已退单，请不要重复操作！";
                    break;
                default:
                    $content = "订单处于未知状态！";
                    break;
            }

        }else{
            $content = "订单号不存在或无权处理此订单！";
        }

        return $content;
    }

    //删除订单
    static function delOrders($oid){
        $uid = UID;
        $sql = "select * from a_orders where uid = '$uid' and oid = '$oid'";
        if($data = Mysql::query($sql,1)){
            $sql = "update a_orders set visibility = 2 where uid = '$uid' and oid = '$oid'";
            @Mysql::query($sql);
            $content = "删除成功！";
        }else{
            $content = "订单号不存在或无权处理此订单！";
        }
        return $content;
    }

    //还原订单
    static function recOrders($oid){
        $uid = UID;
        $sql = "select * from a_orders where uid = '$uid' and oid = '$oid'";
        if($data = Mysql::query($sql,1)){
            $sql = "update a_orders set visibility = 1 where uid = '$uid' and oid = '$oid'";
            $content = Mysql::query($sql)?"订单还原成功！":"还原失败！";
        }else{
            $content = "您似乎没有此订单！";
        }
        return $content;
    }

    //获取订单详细信息
    static function getOrderDetail($GET)
    {
        $uid = UID;
        $oid = empty($GET["oid"])?"":$GET["oid"];
        $sql = "SELECT a.*,c.state_name FROM a_mytickets a left join app_ticket_state c on a.state = c.state_id WHERE a.oid = '$oid' and a.uid = '$uid' and a.father_KEY is null";
        $data = Mysql::query($sql,1);
        return $data;
    }
    //检查订单是否可以退票
    static function checkRefund($oid){
        $sql = "SELECT min(end_time) as minTime,max(state)*min(state) as state from a_mytickets where oid = '$oid'";
        $data = Mysql::query($sql,1);
        $now = date("Y-m-d H:i:s",time());
        if(!empty($data[0]['minTime']) && $data[0]['minTime'] > $now && $data[0]['state'] == 1){
            return 1;
        }else{
            return "";
        }
    }

}