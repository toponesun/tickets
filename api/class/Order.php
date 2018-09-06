<?php

class Order
{
    /*获取订单列表*/
    static function getOrderList(&$result,$GET){
        if (!Ajax::isLogin($result,$GET)){
            return;
        }
        $client = $GET["client"];
        $uid = $_SESSION[$client]["uid"];
        $sql_body = " a.uid = '$uid'";
        $order_state = !isset($GET['order_state']) ? null : $GET['order_state'];
        $visibility = empty($GET['visibility']) ? null : $GET['visibility'];
        $sql_head = "select a.*,b.state_name from a_orders a left join app_order_state b on a.state = b.state_id where ";

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
            $GET["start_time"] .= " 00:00:00";
            $GET["end_time"] .= " 23:59:59";
            $sql_body .= !(strtotime($GET["start_time"]) && strtotime($GET["end_time"]))?"":" AND createTime >= '$GET[start_time]' AND createTime <= '$GET[end_time]'";
        }
        //按updateTime倒序排列
        $sql_body.=" order by a.create_time DESC";
        //计算页码
        $sql_count = "select count(*) from a_orders a where ".$sql_body;
        $num = Mysql::query($sql_count,1);
        $rows = empty($GET["rows"])?ROWS_PER_PAGE:(int)$GET["rows"];
        $pages = ceil($num[0]["count(*)"] / $rows);
        //获取范围内数据
        $begin_i = empty($GET["p"]) ? 0 : ($GET["p"]-1) * $rows;
        $sql = $sql_head.$sql_body." limit $begin_i,".$rows;
        $data = Mysql::query($sql,1);

        if (empty($data)){
            $result["msg"] = "没有找到此类订单！";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "成功";
        $result["data"]["pages"] = $pages;
        $result["data"]["order_live_time"] = ORDER_LIVE_TIME;
        foreach ($data as $row){
            $result["data"]["list"][] = $row;
        }
    }

    //获取订单详情
    static function getOrderDetail(&$result,$GET){
        if (!Ajax::isLogin($result,$GET)){
            return;
        }
        if (empty($GET["oid"])){
            $result["msg"] = "必须传入订单号！";
            return;
        }
        $uid = $_SESSION[$GET["client"]]["uid"];
        $oid = $GET["oid"];
        $sql = "select * from a_orders where oid = '$oid' and uid = '$uid'";
        $data = Mysql::query($sql,1);
        if (empty($data)){
            $result["msg"] = "未找到此订单信息！";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "成功！";
        $result["data"] = $data[0];
        $result["data"]["order_live_time"] = ORDER_LIVE_TIME;
    }


    //删除订单
    static function delOrder(&$result,$POST){
        if (!Ajax::isLogin($result,$POST)){
            return;
        }
        if (empty($POST["oid"])){
            $result["msg"] = "必须传入订单号！";
            return;
        }
        $uid = $_SESSION[$POST["client"]]["uid"];
        $oid = $POST["oid"];
        $sql = "select * from a_orders where uid = '$uid' and oid = '$oid'";
        if($data = Mysql::query($sql,1)){
            if($data[0]["state"] < 2){
                $result["msg"] = "这个订单还有用，还是不要删了吧！";
                return;
            }
            $sql = "update a_orders set visibility = 2 where uid = '$uid' and oid = '$oid'";
            if (Mysql::query($sql)){
                $result["code"] = 1;
                $result["msg"] = "删除成功！";
            }else{
                $result["msg"] = "删除失败，系统错误";
            }
            return;
        }else{
            $result["msg"] = "订单号不存在或无权处理此订单！";
            return;
        }
    }


    //确定退单
    static function refundOrder(&$result,$POST){
        if (!Ajax::isLogin($result,$POST)){
            return;
        }
        if (empty($POST["oid"])){
            $result["msg"] = "必须传入订单号！";
            return;
        }
        $uid = $_SESSION[$POST["client"]]["uid"];
        $oid = $POST["oid"];
        //有订单号开始处理
        $sql = "select * from a_orders where uid = '$uid' and oid = '$oid'";
        if($data = Mysql::query($sql,1)){
            switch ($data[0]["state"]){
                case -1:
                    $sql1 = "update a_orders set state = 4,cancel_time = now() where uid = '$uid' and oid = '$oid'";
                    $sql2 = "update a_mytickets set state = -2 where uid = '$uid' and oid = '$oid'";
                    $res1 = Mysql::query($sql1);
                    $res2 = Mysql::query($sql2);
                    if($res1&&$res2){
                        $result["code"] = 1;
                        $result["msg"] = "取消成功！";
                        return;
                    }else{
                        $result["msg"] = "出现了未知错误，订单号：$oid ";
                        return;
                    }
                    break;
                case 1:
                    if (!self::checkRefund($oid)){
                        $result["msg"] = "订单不满足退款条件";
                        return;
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
                        $result["code"] = 1;
                        $result["msg"] = "退单成功！";
                    }else{
                        $result["msg"] = "出现了未知错误，订单号：$oid ";
                    }
                    return;
                    break;
                case 2:
                    $result["msg"] = "此订单已取消，请不要重复操作！";
                    return;
                    break;
                case 3:
                    $result["msg"] = "此订单已退单，请不要重复操作！";
                    return;
                    break;
                default:
                    $result["msg"] = "订单处于未知状态！";
                    return;
                    break;
            }

        }else{
            $result["msg"] = "订单号不存在或无权处理此订单！";
            return;
        }
    }


    //检查订单是否可以退票
    static function checkRefund($oid){
        $sql = "SELECT min(end_time) as minTime,max(state)*min(state) as state from a_mytickets where oid = '$oid'";
        $data = Mysql::query($sql,1);
        $now = date("Y-m-d H:i:s",time());
        if(!empty($data[0]['minTime']) && $data[0]['minTime'] > $now && $data[0]['state'] == 1){
            return true;
        }else{
            return false;
        }
    }




}