<?php

class Cart
{
    static function cartControl($tid,$act){
        $uid = UID;
        if(empty($tid)){
            $sql = "DELETE FROM a_cart where uid = '$uid'";
            @Mysql::query($sql);
            return "";
        }
        $sql = "select num from a_cart where uid = '$uid' and tid = '$tid'";
        $cart_num = 0;
        if($data = Mysql::query($sql,1)){
            $cart_num = $data[0]["num"];
        }
        $mark = substr($act,0,1);
        $num = substr($act,1);
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
                    $sql = "update a_cart set state = 1 where uid = '".UID."';";
                }elseif($tid == "all-uncheck"){
                    $sql = "update a_cart set state = 0 where uid = '".UID."';";
                }else{
                    $sql = "update a_cart set state = (state + 1)%2 where uid = '".UID."' and tid = '$tid'";
                }
                break;
            default:
                $sql = "";
                break;
        }
        @Mysql::query($sql);
        return "";
    }

    //获取购物车数量
    static function getCartNum($state=""){
        $json = self::cartToJson($state);
        return self::getCartNumByJson($json);
    }

    static function getCartNumByJson($json){
        $arr = json_decode($json);
        $total = 0;
        if (empty($arr)){
            return $total;
        }
        foreach ($arr as $tid => $num){
            $total += $num;
        }
        return $total;
    }

    //检查购物车商品库存是否充足
    static function checkCartStock($state=""){
        $json = self::cartToJson($state);
        return self::checkStockByJson($json);
    }
    //购物车信息转json
    static function cartToJson($state=""){
        if ($state){
            $sql = "select tid,num from a_cart where uid = '".UID."' and state = 1";
        }else{
            $sql = "select tid,num from a_cart where uid = '".UID."'";
        }
        $data = Mysql::query($sql,1);
        if (empty($data)) return false;
        $cart = [];
        foreach ($data as $row){
            $cart[$row["tid"]] = $row["num"];
        }
        return json_encode($cart);
    }
    //json检查库存
    static function checkStockByJson($json){
        $cart_arr = json_decode($json,1);
        if (!is_array($cart_arr)) return true;
        $tid_term = Actions::jsonToTerm(json_encode(array_keys($cart_arr)));
        $sql = "select tid,stock from a_tickets where tid in $tid_term";
        $data = Mysql::query($sql,1);//库存数据
        if (empty($data)){
            return false;
        }else{
            foreach ($data as $row){
                $tic_stock[$row["tid"]] = $row["stock"];
            }//键值对库存信息数组
            foreach ($cart_arr as $cart_tid => $num){
                if (empty($tic_stock[$cart_tid])){
                    return false;
                }else{
                    if ($tic_stock[$cart_tid] < $num){
                        return false;
                    }
                }
            }
        }
        return true;
    }

    //获取个人购物车信息
    static function getCartList()
    {
        $uid = UID;
        $sql_sale = "select sum(b.price),count(*),b.sale_id,c.sale_name,c.sale_type,c.term_price,c.term_num,c.save_percent,c.save_money from a_cart a,a_tickets b left join a_sale c on b.sale_id = c.sale_id where a.uid = '$uid' and a.tid = b.tid group by b.sale_id";
        $data_sale = Mysql::query($sql_sale,1);
        $cart_html = "";
        foreach ($data_sale as $sale) {
            if (empty($sale["sale_id"])){
                $sale["sale_name"] = "不参与优惠";
                $sale["sale_type"] = "无优惠";
                $sale["term_price"] = 0;
                $sale["term_num"] = 0;
                $sale["save_percent"] = 1;
                $sale["save_money"] = 0;
            }

            $sql = "select a.num,b.*,a.state from a_cart a,a_tickets b where a.uid = '$uid' and a.tid = b.tid and b.sale_id = '$sale[sale_id]'";
            $data = Mysql::query($sql,1);
            $cart_html.= Template::getTemp("cartList",$data,1,"");
        }
        $sale_info = self::getSaleInfo();
        $cart_html = <<<LL
                    <form id="goods_json_form" action="?file=confirm" method="post" hidden> 
                        <input type="hidden" id="goods_json" name="goods_json" value=""/>
                    </form>
                    <table class="my-table">
                        <tr>
                            <th width="70px">选择</th>
                            <th>预览</th>
                            <th width="30%">票名</th>
                            <th>类型</th>
                            <th width="210px">有效时间</th>
                            <th>次数</th>
                            <th>种类</th>
                            <th>城市</th>
                            <th>单价/元</th>
                            <th width="80px">数量</th>
                            <th>操作</th>
                        </tr>
                        $cart_html
                        <tr>
                            <td>
                                <button class="btn btn-sm btn-info">全选</button>
                            </td>
                            <td colspan="7"></td>
                            <td>合计：$sale_info[final_price]</td>
                            <td></td>
                            <td>
                                <button class="btn btn-primary" onclick="postCart();">
                                    结算
                                </button>
                            </td>
                        </tr>
                    </table>
LL;
        return $cart_html;
    }

    //获取个人购物车信息
    static function getCartInfo()
    {
        $uid = UID;
        $sql_sale = "select sum(b.price),count(*),b.sale_id,c.sale_name,c.sale_type,c.term_price,c.term_num,c.save_percent,c.save_money from a_cart a,a_tickets b left join a_sale c on b.sale_id = c.sale_id where a.uid = '$uid' and a.tid = b.tid group by b.sale_id";
        $cart_html = <<<LL

            <div class="cart" style="text-align: center">
                <div title="收起购物车" onclick="cartTgl()" style="cursor:pointer;padding:5px;height: 100%;float: left;background-color: #35B2C6">
                    <img src="image/menu-out.png" style="vertical-align: top" height="100%"/>
                </div>
                <div title="购物车" onclick="go('?file=cart')" style="cursor:pointer;height: 100%;float: left;padding: 5px 10px">
                    <img src="image/n-ticket.png" style="vertical-align: top" height="100%"/>
                </div>
                <div title="购物车" onclick="go('?file=cart')" style="cursor:pointer;height: 100%;float: left;padding: 5px 0;line-height: 30px">
                    购物车
                </div>
            </div>
            <div class="cart-list">
LL;
        $all_price = 0;
        $all_num = 0;
        $data_sale = Mysql::query($sql_sale,1);
        foreach ($data_sale as $sale) {
            if (empty($sale["sale_id"])){
                $sale["sale_name"] = "不参与优惠";
                $sale["sale_type"] = "无优惠";
                $sale["term_price"] = 0;
                $sale["term_num"] = 0;
                $sale["save_percent"] = 1;
                $sale["save_money"] = 0;
            }
            $cart_html .= <<<LL

                <div class="sale-name">$sale[sale_type]：$sale[sale_name]</div>
                <div class="goods-sale">
LL;
            $sql = "select b.title,a.num,a.state,b.price,a.tid,b.type,b.tic_type,b.city,b.begin_time,b.end_time,b.stock from a_cart a,a_tickets b where a.uid = '$uid' and a.tid = b.tid and b.sale_id = '$sale[sale_id]'";
            if ($data = Mysql::query($sql,1)) {
                $total_num = 0;
                $total_pay = 0;
                foreach ($data as $row) {
                    $total = $row['num'] * $row['price'];
                    $total_num = $total_num + $row['num'];
                    $total_pay = $total_pay + $total;
                }
                $cart_html .= Template::getTemp("cart",$data);

                if($total_pay>$sale["term_price"]&&$total_num>=$sale["term_num"]){
                    $total_pay_on_sale = $total_pay * $sale["save_percent"] - $sale["save_money"];
                }else{
                    $total_pay_on_sale = $total_pay;
                }
                $all_price += $total_pay_on_sale;
                $all_num += $total_num;
                $cart_html .= <<<LL
                     <div style="text-align: right">
                         <label style="color: #FFF;font-size:16px;line-height: 12px"><del>￥$total_pay</del></label>
                         <label style="color: #FFF;font-size:18px;line-height: 12px">￥$total_pay_on_sale</label>
                     </div>
                        </div>
LL;
            }
        }
        $go_cash = empty($data)?'onclick="warn(\'购物车空空如也，<br/>无法为您结算！\');"':'onclick="go(\'?file=confirm\')"';
        $sale_info = self::getSaleInfo();
        $check_sql = "select state from a_cart where uid = '".UID."' and state = 0";
        $check_data = Mysql::query($check_sql,1);
        $checked = empty($check_data)?"checked":"";
        $check_all = empty($checked)?"all-check":"all-uncheck";
        $cart_html.=<<<LL
         </div>
        <div class="check-all">
            <label for="check-all">
                <input id="check-all" onchange="changeGoodsByNum('$check_all','*')" type="checkbox" $checked/>
                <span></span>全选
            </label>
        </div>
        <div class="clear-cart" onclick="ask('是否确认清空购物车？','changeGoodsByNum(0,0)')"> 清空购物车</div>
        <div class="pay" $go_cash>
            ￥{$sale_info["final_price"]}元<br>去结算（{$sale_info["total_num"]}张）
        </div>
    </div>
LL;
        return $cart_html;
    }

    //获取用户购物车内商品的折扣信息
    static function getSaleInfo(){
        $uid = UID;
        $arr = [
            "total_price"=>0,
            "total_num"=>0,
            "final_price"=>0,
            "save_money"=>0,
            "final_discount"=>1
        ];
        $sql = "select sum(b.price*a.num) as price,sum(a.num) as num,count(*),b.sale_id,c.sale_type,c.term_price,c.term_num,c.save_percent,c.save_money from a_cart a,a_tickets b left join a_sale c on b.sale_id = c.sale_id where a.uid = '$uid' and a.state = 1 and a.tid = b.tid group by b.sale_id";
        $data = Mysql::query($sql,1);
        foreach ($data as $row) {
            if (empty($row["sale_id"])){
                $row["term_price"] = 0;
                $row["term_num"] = 0;
                $row["save_percent"] = 1;
                $row["save_money"] = 0;
            }
            $arr["total_price"] += $row["price"];
            $arr["total_num"] += $row["num"];
            if ($row["num"] >= $row["term_num"]&&$row["price"]>=$row["term_price"]){
                $arr["final_price"] += $row["price"]*$row["save_percent"]-$row["save_money"];
            }else{
                $arr["final_price"] += $row["price"];
            }
        }
        $arr["save_money"] = $arr["total_price"] - $arr["final_price"];
        $total_price = empty($arr["total_price"])?1:$arr["total_price"];
        $arr["final_discount"] = $arr["final_price"] / $total_price;
        return $arr;
    }

    static function getSaleInfoByJson($json){
        //$json = self::cartToJson();
        $arr = json_decode($json,1);
        $result = ["total_price"=>0,
            "total_num"=>0,
            "final_price"=>0,
            "save_money"=>0];
        if (!is_array($arr)) return $result;
        $tid_term = Actions::jsonToTerm(json_encode(array_keys($arr)));

        $sql_sale = "select a.tid,a.price,a.sale_id,b.term_price,b.term_num,b.save_percent,b.save_money from a_tickets a left join a_sale b on a.sale_id = b.sale_id where a.tid in $tid_term group by a.tid";
        $data_sale = Mysql::query($sql_sale,1);
        $goods_arr = [];
        foreach ($data_sale as $row){
            $tid = $row["tid"];
            //$row["sale_id"] =  empty($row["sale_id"])?"":$row["sale_id"];
            $goods_arr[$row["sale_id"]]["num"] = empty($goods_arr[$row["sale_id"]]["num"])?0:$goods_arr[$row["sale_id"]]["num"];
            $goods_arr[$row["sale_id"]]["price"] = empty($goods_arr[$row["sale_id"]]["price"])?0:$goods_arr[$row["sale_id"]]["price"];
            $goods_arr[$row["sale_id"]]["num"] += $arr[$tid];
            $goods_arr[$row["sale_id"]]["price"] += $row["price"] * $arr[$tid];
            $goods_arr[$row["sale_id"]]["term_price"] = $row["term_price"];
            $goods_arr[$row["sale_id"]]["term_num"] = $row["term_num"];
            $goods_arr[$row["sale_id"]]["save_percent"] = $row["save_percent"];
            $goods_arr[$row["sale_id"]]["save_money"] = $row["save_money"];

        }


        foreach ($goods_arr as $sale_id => $sale_info) {
            if (empty($sale_id)){
                $sale_info["term_price"] = 0;
                $sale_info["term_num"] = 0;
                $sale_info["save_percent"] = 1;
                $sale_info["save_money"] = 0;
            }
            $result["total_price"] += $sale_info["price"];
            $result["total_num"] += $sale_info["num"];


            if ($sale_info["price"] >= $sale_info["term_price"] && $sale_info["num"] >= $sale_info["term_num"]){
                $result["final_price"] += $sale_info["price"] * $sale_info["save_percent"] - $sale_info["save_money"];
            }else{
                $result["final_price"] += $sale_info["price"];
            }
        }
        $result["save_money"] = $result["total_price"] - $result["final_price"];

        return $result;

    }

    static function getSaleInfoByCart(){
        //获得已选中的购物车票券和折扣信息***
        $uid = UID;
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
        return $sale_arr;
    }

    static function getTicInfoByCart(){
        $cart_sql = <<<LL
select a.num,b.* 
from a_cart a,a_tickets b
where a.state = 1 and a.tid = b.tid
LL;
        $cart_data = Mysql::query($cart_sql,1);
        $cart_arr = [];
        foreach ($cart_data as $row){
            if (empty($sale_arr[$row['sale_id']]["real_discount"])){
                $real_pay = $row["price"];
            }else{
                $real_pay = $row["price"] * $sale_arr[$row['sale_id']]["real_discount"];
                $real_pay = number_format($real_pay,2);
            }
            $cart_arr[$row['tid']] = [
                "price"=>$row["price"],
                "real_pay"=>$real_pay
            ];
        }
        return $cart_arr;
    }

    static function getTicInfoByJson($json){

    }

}