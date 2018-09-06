<?php

class TradeRec
{
    static function getTradeRec($GET){
        $uid = User::getUid();
        $sql = "select * from a_trade_rec where uid = '$uid'";
        $sql.= empty($GET['trade_type'])?"":" and type = '$GET[trade_type]'";
        $sql.= empty($GET['payment'])?"":" and payment = '$GET[payment]'";
        //判断日期是否合法
        if (!empty($GET["start_time"]) && !empty($GET["end_time"])){
            $GET["start_time"].=" 00:00:00";
            $GET["end_time"].=" 23:59:59";
            $sql.= !(strtotime($GET["start_time"]) && strtotime($GET["end_time"]))?"":" AND create_time between '$GET[start_time]' and '$GET[end_time]'";
        }
        $sql .= " order by create_time desc";
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("tradeRec",$data,"没有此类交易记录...");
        return $html;
    }

}