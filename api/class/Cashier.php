<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/25 0025
 * Time: 15:20
 */

class Cashier
{
    static function getConfirmList(&$result,$GET){
        if (!Ajax::isLogin($result,$GET)){
            return;
        }
        $client = $GET["client"];
        $uid = $_SESSION[$client]["uid"];
        $sql = "select a.num,b.* from a_cart a,a_tickets b where a.uid = '$uid' and a.state = 1 and a.tid = b.tid group by tid";
        $data = Mysql::query($sql,1);
        if (empty($data)){
            $result["msg"] = "购物车为空不允许结算，请先选中商品！";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "成功";
        $result["data"]["pay"] = Cart::getSaleInfoByCart($client);
        foreach ($data as $row){
            $result["data"]["list"][] = $row;
        }
    }




    static function createOrder(&$result,$POST){
        if (!Ajax::isLogin($result,$POST)){
            return;
        }
        $client = $POST["client"];
        $uid = $_SESSION[$client]["uid"];
        $buy_way = empty($POST["buy_way"])?"":$POST["buy_way"];

        //传入cart为空时，使用云端购物车数据生成订单
        if(empty($POST["cart"])){
            self::createOrderByCart($result,$client);
            return;
        }
        //传入cart不为空时，使用cart票券数组生成订单
        //cart数组格式[tid1=>num1,tid2=>num2,....]



    }





    //移植，根据用户购物车提交订单
    static function createOrderByCart(&$result,$client){
        $uid = $_SESSION[$client]["uid"];
        if (!Cart::getCartNum($client,true)){
            $result["msg"] = "购物车为空无法结算，请先选择要结算的商品！";
            return;
        }
        //1.检查库存是否充足
        if (!Cart::checkCartStock($client,true)){
            $result["msg"] = "选择的商品库存不足请重试！";
            return;
        };


        //2.更新库存,商品库存-购物车数量***可能存在一种票存多次的问题
        $sql = "update a_cart a,a_tickets b SET b.stock = b.stock - a.num where a.uid = '$uid' and a.state = 1 and a.tid = b.tid";
        Mysql::query($sql);

        //生成订单号
        $oid = date("YmdHis").substr($uid, -10);
        //获得已选中的购物车票券和折扣信息***
        $sale_arr = Cart::getSaleInfoByCart($client);

        $cart_sql = <<<LL
select a.num,b.* 
from a_cart a,a_tickets b
where a.state = 1 and a.tid = b.tid and a.uid = '$uid'
LL;
        $cart_data = Mysql::query($cart_sql,1);
        $my_tic_data = [];
        foreach ($cart_data as $row){
            if (empty($sale_arr[$row['sale_id']]["real_discount"])){
                $row["pay"] = $row["price"];
            }else{
                $real_pay = $row["price"] * $sale_arr[$row['sale_id']]["real_discount"];
                $row["pay"] = number_format($real_pay,2);
            }
            $my_tic_data[] = $row;
        }

        //3.每张票存入我的票券并计算价格，状态不可用
        $insert = "";
        foreach ($my_tic_data as $row){
            $pay = $row["pay"];
            for ($i = 0; $i < $row["num"]; $i++) {
                //套票和常规票区别对待
                if ($row["tic_type"] == 3){
                    //套票父级券码命名规则
                    $fatherKey = "93".Sys::rndKey(12);
                    $insert .= ",('$uid','$row[tid]','$oid','$row[pid]','$row[bid]','$row[title]','$row[price]','$pay','$row[tic_type]','$fatherKey',null,'$row[type]',null,null,now(),'$row[begin_time]','$row[end_time]','-1')";
                    //对套餐下子tid生成票券
                    $sql_child = "select a.father_tid,a.child_price,b.* from a_tickets_child a,a_tickets b where a.father_tid = '$row[tid]' and a.child_tid = b.tid";
                    $data_child = Mysql::query($sql_child,1);
                    foreach ($data_child as $child){
                        //套票子级券码命名规则
                        $child_sale_id = empty($row["sale_id"])?0:$row["sale_id"];
                        $child_pay = $child["child_price"] * $sale_arr[$child_sale_id]["real_discount"];
                        $child_pay = number_format($child_pay,2);
                        if ($child["tic_type"] == 1){
                            $Key = "91".Sys::rndKey(12);
                            $insert .= ",('$uid', '$child[tid]','$oid','$child[pid]','$child[bid]','$child[title]','$child[child_price]','$child_pay','$child[tic_type]','$Key','$fatherKey','$child[type]','$child[times]','$child[times]',now(),'$child[begin_time]','$child[end_time]','-1')";
                        }elseif ($child["tic_type"] == 2){
                            $Key = "92".Sys::rndKey(12);
                            $insert .= ",('$uid', '$child[tid]','$oid','$child[pid]','$child[bid]','$child[title]','$child[child_price]','$child_pay','$child[tic_type]','$Key','$fatherKey','$child[type]',null,null,now(),now(),date_add(now(),interval $child[valid_days] day),'-1')";
                        }
                    }
                    //计时票
                }elseif ($row["tic_type"] == 2){
                    //计时票券码命名规则
                    $Key = "92".Sys::rndKey(12);
                    $insert .= ",('$uid', '$row[tid]','$oid','$row[pid]','$row[bid]','$row[title]','$row[price]','$pay','$row[tic_type]','$Key',null,'$row[type]',null,null,now(),now(),date_add(now(),interval $row[valid_days] day),'-1')";
                    //常规票
                }else{
                    //常规票券码命名规则
                    $Key = "91".Sys::rndKey(12);
                    $insert .= ",('$uid', '$row[tid]','$oid','$row[pid]','$row[bid]','$row[title]','$row[price]','$pay','$row[tic_type]','$Key',null,'$row[type]','$row[times]','$row[times]',now(),'$row[begin_time]','$row[end_time]','-1')";
                }
            }
        }
        $insert = substr($insert,1);
        $insert_sql = "insert into a_mytickets(uid,tid,oid,pid,bid,title,price,pay,tic_type,ticket_KEY,father_KEY,type,orig_times,times,create_time,begin_time,end_time,state) VALUES$insert";
        Mysql::query($insert_sql);

        //获得选中购物车价格和优惠
        $sale_info = Cart::getSaleInfoByCart($client);

        //4.生成订单，插入票券，从购物车删除选中
        $sql_order = "INSERT INTO a_orders (uid,oid,orig_price,price,num,buy_way,create_time,state) VALUES ('$uid', '$oid','{$sale_info['final']['orig_price']}','{$sale_info['final']['sale_price']}','{$sale_info['final']['num']}','$client', now(), '-1')";
        $sql_cart_remove = "DELETE FROM a_cart WHERE uid = '$uid' and state = 1";
        Mysql::query($sql_order);
        Mysql::query($sql_cart_remove);

        $result["code"] = 1;
        $result["msg"] = "订单提交成功，请及时支付！";
        $result["data"] = ["oid"=>$oid];
        return;
    }


    //用余额支付订单
    static function payOrder(&$result,$POST){
        if (!Ajax::isLogin($result,$POST)){
            return;
        }
        if (empty($POST["oid"])){
            $result["msg"] = "必须传入oid";
            return;
        }
        $uid = $_SESSION[$POST["client"]]["uid"];
        $oid = $POST["oid"];
        switch ($POST["client"]){
            case "customer":
                $user_tb = "user_customer";
                break;
            case "conductor":
                $user_tb = "user_conductor";
                break;
            default:
                $result["msg"] = "端口名不合法";
                return;
        }

        $sql = "select a.money,b.price,b.state from $user_tb a,a_orders b where a.uid = '$uid' and b.oid = '$oid'";
        if ($data = Mysql::query($sql,1)) {
            switch ($data[0]["state"]){
                case 1:
                    $result["msg"] = "订单已支付，请勿重复操作！";
                    return;
                    break;
                case -1:
                    if ($data[0]["money"] >= $data[0]["price"]) {
                        $trade_num = date("YmdHis") . $uid;
                        @Mysql::query("update $user_tb set money = money - {$data[0]['price']} where uid = '$uid'");
                        @Mysql::query("update a_orders set state = 1,pay_time = now() where oid = '$oid'");
                        @Mysql::query("update a_mytickets set state = 1 where oid = '$oid'");
                        //@Mysql::query("update a_order_ocpy set state = 1 where oid = '$oid'");
                        @Mysql::query("insert into a_trade_rec(uid,trade_num,oid,create_time,money,payment,type) values('$uid','$trade_num','$oid',now(),'{$data[0]['price']}','余额支付','订单支付')");

                        //钱-钱
                        //订单状态完成，券码生效
                        //返回支付成功
                        $result["code"] = 1;
                        $result["msg"] = "支付成功！";
                        return;
                    } else {
                        $result["msg"] = "支付失败，余额不足！";
                        return;
                    }
                    break;
                default:
                    $result["msg"] = "此订单不在未支付状态，无法完成支付！";
                    return;
                    break;
            }
        }else{
            $result["msg"] = "没有找到订单号 $oid 的数据";
            return;
        }
    }

}