<?php

class ReportForm
{
    static function getOrderForm($GET){
        $date_range = empty($GET["date_range"])?"":$GET["date_range"];
        $date_range = explode(" - ",$date_range);
        if (empty($date_range[1])){
            $begin_time = date("Y/m/01");
            $end_time = date("Y/m/t");
        }else{
            $begin_time = date("Y/m/d",strtotime($date_range[0]));
            $end_time = date("Y/m/d",strtotime($date_range[1]));
        }
        $sql = <<<LL
        select a.day as date,
        '$begin_time' as begin_time,
        '$end_time' as end_time,
        count(b.oid) as order_num,
        count(b.state = 1 or null) as success_num,
        count(b.state = -1 or null) as paying_num,
        count(b.state in (2,4) or null) as cancel_num,
        count(b.state = 3 or null) as refund_num,
        ifnull(sum(orig_price),0) as total_orig_price,
        ifnull(sum(price),0) as total_price,
        count(buy_way = "pc" or null) as pc_buy,
        count(buy_way = "mobile" or null) as mobile_buy,
        count(buy_way = "conductor" or null) as conductor_buy
        from z_days a left join a_orders b on a.day = DATE_FORMAT(b.create_time, '%Y-%m-%d')
        where a.day between '$begin_time' and '$end_time' 
        group by a.day
LL;
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("order-form",$data);
        return $html;
    }

    static function getTicketForm($GET){
        $date_range = empty($GET["date_range"])?"":$GET["date_range"];
        $date_range = explode(" - ",$date_range);
        if (empty($date_range[1])){
            $begin_time = date("Y/m/01");
            $end_time = date("Y/m/t");
        }else{
            $begin_time = date("Y/m/d",strtotime($date_range[0]));
            $end_time = date("Y/m/d",strtotime($date_range[1]));
        }

        $on = "";
        if (!empty($GET["pid"])){
            $on.= " and b.pid = '$GET[pid]'";
        }
        if (!empty($GET["bid"])){
            $on.= " and b.bid = '$GET[bid]'";
        }
/*        count(b.type = "成人票" or null) as adult_num,
        count(b.type = "学生票" or null) as student_num,
        count(b.type = "老人票" or null) as elder_num,
        count(b.type = "儿童票" or null) as child_num,*/

        $sql = <<<LL
        select a.day as date,
        '$begin_time' as begin_time,
        '$end_time' as end_time,
        count(b.ticket_KEY) as ticket_num,
        count(b.state = 1 or null) as notuse_num,
        count(b.state = 2 or null) as used_num,
        count(b.state = 3 or null) as refund_num,
        count(b.state = 5 or null) as outdate_num,
        count(b.tic_type = 1 or null) as normal_tic_num,
        count(b.tic_type = 2 or null) as time_tic_num,
        count(b.tic_type = 3 or null) as group_tic_num,
        count(c.buy_way = "pc" or null) as pc_buy,
        count(c.buy_way = "mobile" or null) as mobile_buy,
        count(c.buy_way = "conductor" or null) as conductor_buy
        from z_days a left join a_mytickets b 
        on a.day = DATE_FORMAT(b.create_time, '%Y-%m-%d')
        $on
        left join a_orders c on b.oid = c.oid 
        where a.day between '$begin_time' and '$end_time' 
        group by a.day
LL;
        echo $sql;
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("ticket-form",$data);
        return $html;
    }

    static function getTradeForm($GET){
        $date_range = empty($GET["date_range"])?"":$GET["date_range"];
        $date_range = explode(" - ",$date_range);
        if (empty($date_range[1])){
            $begin_time = date("Y/m/01");
            $end_time = date("Y/m/t");
        }else{
            $begin_time = date("Y/m/d",strtotime($date_range[0]));
            $end_time = date("Y/m/d",strtotime($date_range[1]));
        }
        $sql = <<<LL
        select a.day as date,
        '$begin_time' as begin_time,
        '$end_time' as end_time,
        count(b.type = "订单支付" or null) as total_pay_num,
        sum(case b.type when "订单支付" then b.money else 0 end) as total_pay_money,
        count(b.type = "订单退款" or null) as total_ref_num,
        sum(case b.type when "订单退款" then b.money else 0 end) as total_ref_money,
        count(b.payment = "余额支付" or null) as balance_pay_num,
        sum(case b.payment when "余额支付" then b.money else 0 end) as balance_pay_money,
        count(b.payment = "微信支付" or null) as wechat_pay_num,
        sum(case b.payment when "微信支付" then b.money else 0 end) as wechat_pay_money,
        count(b.payment = "支付宝" or null) as ali_pay_num,
        sum(case b.payment when "支付宝" then b.money else 0 end) as ali_pay_money
        from z_days a left join a_trade_rec b 
        on a.day = DATE_FORMAT(b.create_time, '%Y-%m-%d')
        where a.day between '$begin_time' and '$end_time' 
        group by a.day
LL;
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("trade-form",$data);
        return $html;
    }


    static function getRecordForm($GET){
        $date_range = empty($GET["date_range"])?"":$GET["date_range"];
        $date_range = explode(" - ",$date_range);
        if (empty($date_range[1])){
            $begin_time = date("Y/m/01");
            $end_time = date("Y/m/01",strtotime(date("Y/m/d")." +1 month"));
            $to_time = date("Y/m/t");
        }else{
            $begin_time = date("Y/m/d",strtotime($date_range[0]));
            $end_time = date("Y/m/d",strtotime($date_range[1]." +1 day"));
            $to_time = date("Y/m/d",strtotime($date_range[1]));
        }
        $where = "";
        if (!empty($GET["pid"])){
            $where.= " and a.pid = '$GET[pid]'";
        }
        if (!empty($GET["bid"])){
            $where.= " and a.bid = '$GET[bid]'";
        }
        if (!empty($GET["state_id"])){
            $where.= " and a.state = '$GET[state_id]'";
        }
        $sql = <<<LL
        select a.create_time,
        a.title,a.price,a.pay,(-1 * (a.price - a.pay)) as save_money,
        (case when a.father_KEY is null then "" else "[子票券]" end) as is_child,
        c.buy_way,
        (case when b.price is null then "-" else b.price end) as now_price,
        d.state_name,
        '$begin_time' as begin_time,
        '$to_time' as end_time
        from a_mytickets a 
        left join a_tickets b on a.tid = b.tid 
        left join a_orders c on a.oid = c.oid 
        left join app_ticket_state d on a.state = d.state_id 
        where a.create_time between '$begin_time' and '$end_time'
        and a.tic_type != 3
        $where
        order by a.create_time desc
LL;
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("record-form",$data);
        return $html;
    }

}