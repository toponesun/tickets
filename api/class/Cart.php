<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20 0020
 * Time: 17:22
 */

class Cart
{
    static function cartCtrl(&$result,$POST){
        if (!Ajax::isLogin($result,$POST)){
            return;
        }
        $client = $POST["client"];
        $uid = $_SESSION[$client]["uid"];
        //传入tid为空时，清空购物车
        if(empty($POST["tid"])){
            $sql = "DELETE FROM a_cart where uid = '$uid'";
            @Mysql::query($sql);
            $result["code"] = 1;
            $result["msg"] = "购物车已清空！";
            return;
        }
        //未传入mark时
        if (!isset($POST["mark"])){
            $result["msg"] = "空的mark，无法进行购物车操作";
            return;
        }
        $tid = $POST["tid"];
        $mark = substr($POST["mark"],0,1);
        $num = substr($POST["mark"],1);

        $sql = "select num from a_cart where uid = '$uid' and tid = '$tid'";
        $cart_num = 0;
        if($data = Mysql::query($sql,1)){
            $cart_num = $data[0]["num"];
        }

        switch ($mark){
            case "~":
                if($num>0){
                    $sql = "UPDATE a_cart SET num = '$num' where uid = '$uid' and tid = '$tid'";
                }else{
                    $sql = "UPDATE a_cart SET num = 1 where uid = '$uid' and tid = '$tid'";
                }
                break;
            case "+":
                if(empty($cart_num)){
                    $sql = "INSERT INTO  a_cart (`uid` , `tid` , `num` , `add_time`)VALUES ('$uid', '$tid', '$num', now())";
                }else{
                    $sql = "UPDATE a_cart SET num = num + '$num' where uid = '$uid' and tid = '$tid'";
                }
                break;
            case "-":
                if($cart_num>$num){
                    $sql = "UPDATE a_cart SET num = num - '$num' where uid = '$uid' and tid = '$tid'";
                }else{
                    $sql = "UPDATE a_cart SET num = 1 where uid = '$uid' and tid = '$tid'";
                }
                break;
            case "0":
                $sql = "DELETE FROM a_cart where uid = '$uid' and tid = '$tid'";
                break;
            case "*":
                if ($tid == "all-check"){
                    $sql = "update a_cart set state = 1 where uid = '$uid';";
                }elseif($tid == "all-uncheck"){
                    $sql = "update a_cart set state = 0 where uid = '$uid';";
                }else{
                    $sql = "update a_cart set state = (state + 1)%2 where uid = '$uid' and tid = '$tid'";
                }
                break;
            default:
                $sql = "";
                break;
        }
        @Mysql::query($sql);
        $result["code"] = 1;
        $result["msg"] = "购物车操作成功";
        return;
    }


    /*获取购物车商品数量*/
    static function getCartNum($client,$state=false){
        $uid = $_SESSION[$client]["uid"];
        if ($state){
            $sql = "select sum(num) as total_num from a_cart where uid = '$uid' and state = 1";
        }else{
            $sql = "select sum(num) as total_num from a_cart where uid = '$uid'";
        }
        $data = Mysql::query($sql,1);
        if (empty($data)){
            return false;
        }else{
            return $data[0]["total_num"];
        }
    }
    /*检查购物车中商品是否有货*/
    static function checkCartStock($client,$state=false){
        $uid = $_SESSION[$client]["uid"];
        if (!self::getCartNum($client)) return true;
        if ($state){
            $sql = "select a.tid from a_cart a,a_tickets b where a.uid = '$uid' and a.state = 1 and a.tid = b.tid and a.num > b.stock";
        }else{
            $sql = "select a.tid from a_cart a,a_tickets b where a.uid = '$uid' and a.tid = b.tid and a.num > b.stock";
        }
        $data = Mysql::query($sql,1);
        if (empty($data)){
            return true;
        }else{
            return false;
        }
    }

    /*获取购物车列表*/
    static function getCartList(&$result,$GET){
        if (!Ajax::isLogin($result,$GET)){
            return;
        }
        $client = $GET["client"];
        $uid = $_SESSION[$client]["uid"];
        $result["data"]["total"] = self::getSaleInfoByCart($client);
        $sql_sale = "select sum(b.price),count(*),b.sale_id,c.sale_name,c.sale_type,c.term_price,c.term_num,c.save_percent,c.save_money from a_cart a,a_tickets b left join a_sale c on b.sale_id = c.sale_id where a.uid = '$uid' and a.tid = b.tid group by b.sale_id";
        $data_sale = Mysql::query($sql_sale,1);
        if (empty($data_sale)){
            $result["code"] = 1;
            $result["msg"] = "购物车为空！";
            return;
        }
        $all_price = 0;
        $all_num = 0;
        foreach ($data_sale as $sale) {
            $sale_id = $sale["sale_id"];
            if (empty($sale["sale_id"])){
                $sale_id = "normal";
                $sale["sale_name"] = "不参与优惠";
                $sale["sale_type"] = "无优惠";
                $sale["term_price"] = 0;
                $sale["term_num"] = 0;
                $sale["save_percent"] = 1;
                $sale["save_money"] = 0;
            }

            $sql = "select b.title,a.num,a.state,b.price,a.tid,b.type,b.tic_type,b.city,b.begin_time,b.end_time,b.stock from a_cart a,a_tickets b where a.uid = '$uid' and a.tid = b.tid and b.sale_id = '$sale[sale_id]'";
            if ($data = Mysql::query($sql,1)) {
                $result["code"] = 1;
                $result["msg"] = "成功";
                $result["data"]["cart"][$sale_id] = $data;

                $total_num = 0;
                $total_pay = 0;
                foreach ($data as $row) {
                    $total = $row['num'] * $row['price'];
                    $total_num = $total_num + $row['num'];
                    $total_pay = $total_pay + $total;
                }

                if($total_pay>$sale["term_price"]&&$total_num>=$sale["term_num"]){
                    $total_pay_on_sale = $total_pay * $sale["save_percent"] - $sale["save_money"];
                }else{
                    $total_pay_on_sale = $total_pay;
                }
                $all_price += $total_pay_on_sale;
                $all_num += $total_num;
            }
        }

    }

    static function test(){
        $sql = "select tid,num from a_cart";
        $data = Mysql::query($sql,1);//购物车票券数组
        $post_json = [];
        foreach ($data as $row){
            $post_json[$row["tid"]] = $row["num"];
        }





        $tids = array_keys($post_json);
        $tids_term = Sys::arrToTerm($tids);
        $sql = "select b.* from a_tickets a left join a_sale b on a.sale_id = b.sale_id where a.tid in $tids_term group by sale_id";
        $sale_data = Mysql::query($sql,1);//sale优惠信息

        $sql = "select tid,sale_id from a_tickets where tid in $tids_term";
        $tic_data = Mysql::query($sql,1);//传入的票券详细信息
        $cart_info_json = [];


        foreach ($sale_data as $sale){
            foreach ($tic_data as $row){
                $row["num"] = $post_json[$row["tid"]];
                $cart_info_json[] = $row;
            }
        }




        return $cart_info_json;
    }

    static function getSaleInfoByCart($client){
        $uid = $_SESSION[$client]["uid"];
        //获取票券总张数
        $cart_sql = "select (case when sum(num) is null then 0 else sum(num) end) as num from a_cart where uid = '$uid' and state = 1";
        $cart_data = Mysql::query($cart_sql,1);
        $total_num = $cart_data[0]["num"];

        //获得已选中的购物车票券和折扣信息***

        $sale_sql = <<<LL
select c.sale_id,sum(a.num) as orig_num,sum(b.price * a.num) as orig_price,
c.term_price,
c.term_num,
c.save_percent,
c.save_money 
from a_cart a,a_tickets b 
left join a_sale c on b.sale_id = c.sale_id 
where a.uid = '$uid' and a.state = 1 and a.tid = b.tid
group by c.sale_id
LL;
        $sale_data = Mysql::query($sale_sql,1);
        $sale_arr = [
            "final"=>[
                "orig_price"=>0,
                "sale_price"=>0
            ]
        ];
        //遍历获取到的价格信息，计算每个折扣下的原价和折扣价，并求和
        foreach ($sale_data as $row){
            if (empty($row['sale_id'])){
                $sale_arr[0] = [
                    "orig_price"=>$row['orig_price'],
                    "sale_price"=>$row['orig_price'],
                    "real_discount"=>1
                ];
                $sale_arr["final"]["orig_price"] += $row['orig_price'];
                $sale_arr["final"]["sale_price"] += $row['orig_price'];
                continue;
            }
            if (($row["orig_price"]>=$row["term_price"]) and ($row["orig_num"]>=$row["term_num"])){
                $sale_price = $row['orig_price'] * $row["save_percent"] - $row["save_money"];
            }else{
                $sale_price = $row['orig_price'];
            }
            $real_discount = $sale_price / (empty($row['orig_price'])?1:$row['orig_price']);
            $sale_arr[$row['sale_id']] = [
                "orig_price"=>$row['orig_price'],
                "sale_price"=>$sale_price,
                "real_discount"=>$real_discount
            ];
            $sale_arr["final"]["orig_price"] += $row['orig_price'];
            $sale_arr["final"]["sale_price"] += $sale_price;
        }
        $sale_arr["final"]["num"] = $total_num;
        $sale_arr["final"]["save_money"] = $sale_arr["final"]["orig_price"] - $sale_arr["final"]["sale_price"];
        $sale_arr["final"]["final_discount"] = empty($sale_arr["final"]["sale_price"])?1:($sale_arr["final"]["sale_price"] / $sale_arr["final"]["orig_price"]);
        return $sale_arr;
    }



}