<?php

class Cashier
{
    //购物车获取订单结算页面
    static function getConfirmByCart()
    {
        $sql = "select a.num,b.* from a_cart a,a_tickets b where a.uid = '".UID."' and a.state = 1 and a.tid = b.tid group by tid";
        $data = Mysql::query($sql,1);
        return $data;
    }

    //快速下单
    static function getBuy($GET)
    {
        if (empty($GET["tid"])) return false;
        $tid = $GET["tid"];
        $num = 1;
        if (!empty($GET["num"])){
            $num = (int)$GET["num"];
        }
        $num = is_int($num)?$num:1;
        $sql = "select '$num' as num,b.* from a_tickets b where b.tid = '$tid'";
        $data = Mysql::query($sql,1);
        return $data;
    }

    //json获取订单结算页面
    static function getConfirmByJson($json)
    {
        $cart_arr = json_decode($json,1);
        foreach ($cart_arr as $key => $val){
            $tid = trim($key);
            $num = (int)$val;
        }
        if (empty($tid)) return false;
        if (empty($num)) $num = 1;
        $sql = "select $num as num,b.* from a_tickets b where b.tid = '$tid'";
        $data = Mysql::query($sql,1);
        return $data;
    }

    //有订单号，查找订单信息，返回支付页面html
    static function getCashier($GET)
    {
        if (empty($GET["oid"])) return false;
        $oid = $GET["oid"];
        $sql = "select * from a_orders where uid = '".UID."' and oid = '$oid'";
        $data = Mysql::query($sql, 1);
        if (empty($data)) return false;
        if ($data[0]["state"] != "-1") return false;
        return $data;
    }

    static function createOrderByCart($buy_way=""){
        if (!Cart::getCartNum(1)) return false;
        //1.检查库存是否充足
        if (!Cart::checkCartStock()) return false;
        //2.更新库存,商品库存-购物车数量***可能存在一种票存多次的问题
        $sql = "update a_cart a,a_tickets b SET b.stock = b.stock - a.num where a.uid = '".UID."' and a.state = 1 and a.tid = b.tid";
        Mysql::query($sql);

        //生成订单号
        $oid = date("YmdHis").substr(UID, -10);
        //获得已选中的购物车票券和折扣信息***
        $sale_arr = Cart::getSaleInfoByCart();

        $cart_sql = <<<LL
select a.num,b.* 
from a_cart a,a_tickets b
where a.state = 1 and a.tid = b.tid
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
                    $fatherKey = "30".Actions::randKey(12);
                    $insert .= ",('".UID."','$row[tid]','$oid','$row[pid]','$row[bid]','$row[title]','$row[price]','$pay','$row[tic_type]','$fatherKey',null,'$row[type]',null,null,now(),null,null,'-1')";
                    //对套餐下子tid生成票券
                    $sql_child = "select a.father_tid,a.child_price,b.* from a_tickets_child a,a_tickets b where a.father_tid = '$row[tid]' and a.child_tid = b.tid";
                    $data_child = Mysql::query($sql_child,1);
                    foreach ($data_child as $child){
                        //套票子级券码命名规则
                        $child_sale_id = empty($row["sale_id"])?0:$row["sale_id"];
                        $child_pay = $child["child_price"] * $sale_arr[$child_sale_id]["real_discount"];
                        $child_pay = number_format($child_pay,2);
                        if ($child["tic_type"] == 1){
                            $Key = "11".Actions::randKey(12);
                            $insert .= ",('".UID."', '$child[tid]','$oid','$child[pid]','$child[bid]','$child[title]','$child[child_price]','$child_pay','$child[tic_type]','$Key','$fatherKey','$child[type]','$child[times]','$child[times]',now(),'$child[begin_time]','$child[end_time]','-1')";
                        }elseif ($child["tic_type"] == 2){
                            $Key = "21".Actions::randKey(12);
                            $insert .= ",('".UID."', '$child[tid]','$oid','$child[pid]','$child[bid]','$child[title]','$child[child_price]','$child_pay','$child[tic_type]','$Key','$fatherKey','$child[type]',null,null,now(),now(),date_add(now(),interval $child[valid_days] day),'-1')";
                        }
                    }
                    //计时票
                }elseif ($row["tic_type"] == 2){
                    //计时票券码命名规则
                    $Key = "20".Actions::randKey(12);
                    $insert .= ",('".UID."', '$row[tid]','$oid','$row[pid]','$row[bid]','$row[title]','$row[price]','$pay','$row[tic_type]','$Key',null,'$row[type]',null,null,now(),now(),date_add(now(),interval $row[valid_days] day),'-1')";
                    //常规票
                }else{
                    //常规票券码命名规则
                    $Key = "10".Actions::randKey(12);
                    $insert .= ",('".UID."', '$row[tid]','$oid','$row[pid]','$row[bid]','$row[title]','$row[price]','$pay','$row[tic_type]','$Key',null,'$row[type]','$row[times]','$row[times]',now(),'$row[begin_time]','$row[end_time]','-1')";
                }
            }
        }
        $insert = substr($insert,1);
        $insert_sql = "insert into a_mytickets(uid,tid,oid,pid,bid,title,price,pay,tic_type,ticket_KEY,father_KEY,type,orig_times,times,create_time,begin_time,end_time,state) VALUES$insert";
        Mysql::query($insert_sql);

        //获得选中购物车价格和优惠
        $sale_info = Cart::getSaleInfo();

        //4.生成订单，插入票券，从购物车删除选中
        $sql_order = "INSERT INTO a_orders (uid,oid,orig_price,price,num,buy_way,create_time,state) VALUES ('".UID."', '$oid','$sale_info[total_price]','$sale_info[final_price]','$sale_info[total_num]','$buy_way', now(), '-1')";
        $sql_cart_remove = "DELETE FROM a_cart WHERE uid = '".UID."' and state = 1";
        Mysql::query($sql_order);
        Mysql::query($sql_cart_remove);
        return $oid;
    }


    static function createOrderByJson($json,$buy_way=""){

        if (!Cart::getCartNumByJson($json)) return false;
        //1.检查库存是否充足
        if (!Cart::checkStockByJson($json)) return false;

        $cart_arr = json_decode($json,1);
        //2.更新库存,商品库存-购物车数量***可能存在一种票存多次的问题
        foreach ($cart_arr as $tid=>$num){
            $sql = "update a_tickets SET stock = stock - $num where tid = '$tid'";
            Mysql::query($sql);
        }


        //生成订单号
        $oid = date("YmdHis").substr(UID, -10);
        //获得已选中的购物车票券和折扣信息***
        $sale_arr = Cart::getSaleInfoByCart();

        $cart_sql = <<<LL
select a.num,b.* 
from a_cart a,a_tickets b
where a.state = 1 and a.tid = b.tid
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
                    $fatherKey = "30".Actions::randKey(12);
                    $insert .= ",('".UID."','$row[tid]','$oid','$row[pid]','$row[bid]','$row[title]','$row[price]','$pay','$row[tic_type]','$fatherKey',null,'$row[type]',null,null,now(),null,null,'-1')";
                    //对套餐下子tid生成票券
                    $sql_child = "select a.father_tid,a.child_price,b.* from a_tickets_child a,a_tickets b where a.father_tid = '$row[tid]' and a.child_tid = b.tid";
                    $data_child = Mysql::query($sql_child,1);
                    foreach ($data_child as $child){
                        //套票子级券码命名规则
                        $child_sale_id = empty($row["sale_id"])?0:$row["sale_id"];
                        $child_pay = $child["child_price"] * $sale_arr[$child_sale_id]["real_discount"];
                        $child_pay = number_format($child_pay,2);
                        if ($child["tic_type"] == 1){
                            $Key = "11".Actions::randKey(12);
                            $insert .= ",('".UID."', '$child[tid]','$oid','$child[pid]','$child[bid]','$child[title]','$child[child_price]','$child_pay','$child[tic_type]','$Key','$fatherKey','$child[type]','$child[times]','$child[times]',now(),'$child[begin_time]','$child[end_time]','-1')";
                        }elseif ($child["tic_type"] == 2){
                            $Key = "21".Actions::randKey(12);
                            $insert .= ",('".UID."', '$child[tid]','$oid','$child[pid]','$child[bid]','$child[title]','$child[child_price]','$child_pay','$child[tic_type]','$Key','$fatherKey','$child[type]',null,null,now(),now(),date_add(now(),interval $child[valid_days] day),'-1')";
                        }
                    }
                    //计时票
                }elseif ($row["tic_type"] == 2){
                    //计时票券码命名规则
                    $Key = "20".Actions::randKey(12);
                    $insert .= ",('".UID."', '$row[tid]','$oid','$row[pid]','$row[bid]','$row[title]','$row[price]','$pay','$row[tic_type]','$Key',null,'$row[type]',null,null,now(),now(),date_add(now(),interval $row[valid_days] day),'-1')";
                    //常规票
                }else{
                    //常规票券码命名规则
                    $Key = "10".Actions::randKey(12);
                    $insert .= ",('".UID."', '$row[tid]','$oid','$row[pid]','$row[bid]','$row[title]','$row[price]','$pay','$row[tic_type]','$Key',null,'$row[type]','$row[times]','$row[times]',now(),'$row[begin_time]','$row[end_time]','-1')";
                }
            }
        }
        $insert = substr($insert,1);
        $insert_sql = "insert into a_mytickets(uid,tid,oid,pid,bid,title,price,pay,tic_type,ticket_KEY,father_KEY,type,orig_times,times,create_time,begin_time,end_time,state) VALUES$insert";
        Mysql::query($insert_sql);

        //获得选中购物车价格和优惠
        $sale_info = Cart::getSaleInfo();

        //4.生成订单，插入票券，从购物车删除选中
        $sql_order = "INSERT INTO a_orders (uid,oid,orig_price,price,num,buy_way,create_time,state) VALUES ('".UID."', '$oid','$sale_info[total_price]','$sale_info[final_price]','$sale_info[total_num]','$buy_way', now(), '-1')";
        $sql_cart_remove = "DELETE FROM a_cart WHERE uid = '".UID."' and state = 1";
        Mysql::query($sql_order);
        Mysql::query($sql_cart_remove);
        return $oid;
    }



    //支付订单
    static function payOrder($oid)
    {
        $uid = UID;
        $sql = "select a.money,b.price,b.state from user_customer a,a_orders b where a.uid = '$uid' and b.oid = '$oid'";
        if ($data = Mysql::query($sql,1)) {
            switch ($data[0]["state"]){
                case 1:
                    $content = "订单已支付，请勿重复操作！";
                    break;
                case -1:
                    if ($data[0]["money"] >= $data[0]["price"]) {
                        $trade_num = date("YmdHis") . $uid;
                        @Mysql::query("update user_customer set money = money - {$data[0]['price']} where uid = '$uid'");
                        @Mysql::query("update a_orders set state = 1,pay_time = now() where oid = '$oid'");
                        @Mysql::query("update a_mytickets set state = 1 where oid = '$oid'");
                        //@Mysql::query("update a_order_ocpy set state = 1 where oid = '$oid'");
                        @Mysql::query("insert into a_trade_rec(uid,trade_num,oid,create_time,money,payment,type) values('$uid','$trade_num','$oid',now(),'{$data[0]['price']}','余额支付','订单支付')");

                        //钱-钱
                        //订单状态完成，券码生效
                        //返回支付成功
                        $content = "支付成功！";
                    } else {
                        $content = "支付失败，余额不足！";
                    }
                    break;
                default:
                    $content = "此订单不在未支付状态，无法完成支付！";
                    break;
            }
        }else{
            $content = "没有找到订单号 $oid 的数据！";
        }
        return $content;
    }


    static function createMyTickets(){
        $sql_oid = "select oid from a_order_ocpy where state = 1 group by oid;";
        $data_oid = Mysql::query($sql_oid);
        $oid_arr = [];
        foreach ($data_oid as $row){
            $oid_arr[] = $row["oid"];
        }
        $sql_term = Actions::jsonToTerm(json_encode($oid_arr));
        $sql_ocpy = "select a.oid,a.num,b.*,c.order_orig_price,c.order_price from a_order_ocpy a,a_tickets b,a_orders c where a.state = 1 and a.tid = b.tid and a.oid = c.oid";
        $data_ocpy = Mysql::query($sql_ocpy,1);
        Mysql::query("update a_order_ocpy set state = 2 where oid in $sql_term");
        if (empty($data_ocpy)) return false;
        $insert_key = "";
        foreach ($data_ocpy as $row){
            $order_price = empty($row["order_price"])?0:$row["order_price"];
            $order_orig_price = empty($row["order_orig_price"])?1:$row["order_orig_price"];
            $discount = $order_price / $order_orig_price;

            switch ($row["tic_type"]){
                case 1:
                    for ($i = 0; $i < $row["num"]; $i++) {
                        $Key = Actions::randKey(12);

                        $insert_key .= ",('".UID."', '$row[tid]','$row[oid]','$row[pid]','$row[bid]','$row[title]','$row[price]','$row[tic_type]','$Key',null,'$row[type]','$row[times]','$row[times]',now(),'$row[begin_time]','$row[end_time]',1)";
                    }
                    break;
                case 2:
                    for ($i = 0; $i < $row["num"]; $i++) {
                        $Key = Actions::randKey(12);
                        $insert_key .= ",('".UID."', '$row[tid]','$row[oid]','$row[pid]','$row[bid]','$row[title]','$row[price]','$row[tic_type]','$Key',null,'$row[type]',null,null,now(),now(),date_add(now(),interval $row[valid_days] day),1)";
                    }
                    break;
                case 3:
                    for ($i = 0; $i < $row["num"]; $i++) {
                        //套票父级券码命名规则
                        $fatherKey = Actions::randKey(12);
                        $insert_key .= ",('".UID."','$row[tid]','$row[oid]','$row[pid]','$row[bid]','$row[title]','$row[price]','$row[tic_type]','$fatherKey',null,'$row[type]',null,null,now(),null,null,1)";
                        //对套餐下子tid生成票券
                        $sql_child_term = Actions::jsonToTerm($row["group_arr"]);
                        $sql_child = "select * from a_tickets where tid in $sql_child_term";
                        $data_child = Mysql::query($sql_child,1);
                        foreach ($data_child as $child){
                            //套票子级券码命名规则
                            $Key = Actions::randKey(12);
                            if ($child["tic_type"] == 1){
                                $insert_key .= ",('".UID."', '$child[tid]','$row[oid]','$child[pid]','$child[bid]','$child[title]',0,'$child[tic_type]','$Key','$fatherKey','$child[type]','$child[times]','$child[times]',now(),'$child[begin_time]','$child[end_time]',1)";
                            }elseif ($child["tic_type"] == 2){
                                $insert_key .= ",('".UID."', '$child[tid]','$row[oid]','$child[pid]','$child[bid]','$child[title]',0,'$child[tic_type]','$Key','$fatherKey','$child[type]',null,null,now(),now(),date_add(now(),interval $child[valid_days] day),1)";
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        $insert_key = substr($insert_key,1);
        $insert_sql = "insert into a_mytickets(uid,tid,oid,pid,bid,title,price,tic_type,ticket_KEY,father_KEY,type,orig_times,times,create_time,begin_time,end_time,state) VALUES".$insert_key;
        return Mysql::query($insert_sql);
    }

    //根据订单号退单
    static function refund($GET)
    {
        if (empty($GET["oid"])) return false;
        $oid = $GET["oid"];
        if (!MyOrders::checkRefund($oid)) return false;

        $uid = User::getUid();
        //查询退票信息
        $sql = "select a.*,b.city,b.price,c.state_name from a_mytickets a left join a_tickets b on a.tid = b.tid left join app_ticket_state c on c.state_id = a.state where a.oid = '$oid' and a.uid = '$uid' and a.father_KEY is null";
        $data = Mysql::query($sql,1);
        return $data;
    }
}