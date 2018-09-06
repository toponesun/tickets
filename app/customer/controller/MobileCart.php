<?php

class MobileCart extends Cart
{
    //获取个人购物车信息
    static function getCartInfo()
    {
        $uid = UID;
        $sql_sale = "select sum(b.price),count(*),b.sale_id,c.sale_name,c.sale_type,c.term_price,c.term_num,c.save_percent,c.save_money from a_cart a,a_tickets b left join a_sale c on b.sale_id = c.sale_id where a.uid = '$uid' and a.tid = b.tid group by b.sale_id";
        $cart_html = "";
        $all_price = 0;
        $all_num = 0;
        $data_sale = Mysql::query($sql_sale,1);
        foreach ($data_sale as $sale){
            if (empty($sale["sale_id"])){
                $sale["sale_name"] = "不参与优惠";
                $sale["sale_type"] = "无优惠";
                $sale["term_price"] = 0;
                $sale["term_num"] = 0;
                $sale["save_percent"] = 1;
                $sale["save_money"] = 0;
            }
            $sql = "select a.num,b.*,a.state from a_cart a left join a_tickets b on a.tid = b.tid where a.uid = '$uid' and a.tid = b.tid and b.sale_id = '$sale[sale_id]'";
            $cart_html .= <<<LL
        
                <div class="card-on-sale" >
                    <div class="card" style="font-size: 14px;line-height: 16px">$sale[sale_type]：$sale[sale_name]</div>
LL;
            if ($data = Mysql::query($sql,1)) {

                $total_num = 0;
                $total_pay = 0;
                foreach ($data as $card){
                    $total = $card['num'] * $card['price'];
                    $total_num = $total_num + $card['num'];
                    $total_pay = $total_pay + $total;
                }

                if($total_pay>=$sale["term_price"]&&$total_num>=$sale["term_num"]){
                    $total_pay_in_sale = $total_pay * $sale["save_percent"] - $sale["save_money"];
                }else{
                    $total_pay_in_sale = $total_pay;
                }
                $all_price += $total_pay_in_sale;
                $all_num += $total_num;
                $cart_html .= MobileTemplate::getTemp("cart",$data);
                $cart_html .= <<<LL
                     <div style="text-align: right;margin-right: 5%">
                         <label style="color: #363636;font-size:16px;line-height: 18px"><del>￥$total_pay </del></label>
                         <label style="color: #F00;font-size:16px;line-height: 18px">￥$total_pay_in_sale </label>
                     </div>
                </div>
LL;
            }
        }
        $go_cash = empty($data)?'onclick="warn(\'购物车空空如也，<br/>无法为您结算！\');"':'onclick="go(\'?file=confirm\')"';
        $html = MobileTemplate::addTailHtml($cart_html,"购物车空空如也...");
        if(empty($cart_html))
            $html = <<<LL

<div style="position:absolute;font-family:'微软雅黑',sans-serif;bottom:180px;left: 0;width: 100%;text-align: center;color:#8c8c8c">
<img src="image/empty.png" width="120px"/><br/>
<span style="font-size: 22px;font-weight: normal;line-height: 42px;color: #555">购物车为空</span><br/>
生活不止眼前的苟且<br/>
还有诗和远方
</div>
LL;
        $sale_info = self::getSaleInfo();
        $check_sql = "select state from a_cart where uid = '".UID."' and state = 0";
        $check_date = Mysql::query($check_sql,1);
        $checked = empty($check_date)?"checked":"";
        $check_all = empty($checked)?"all-check":"all-uncheck";

        $html = <<<LL
        
        <div class="card-list" style="padding: 0 0 115px 0;"> 
            $html
        </div>
        <div class="cart-price">
            <label for="check-all">
                <input id="check-all" onchange="changeGoodsByNum('$check_all','*')" type="checkbox" $checked/>
                <span></span>全选
            </label>
             共<span style="font-size:20px;color: red">￥$sale_info[final_price]</span>元
        </div>
        <div class="cart-gocash" $go_cash>
            去结算($sale_info[total_num])
        </div>
LL;
        return $html;

    }

}