<?php

class TradeRec
{
    static $trade_rec_page;
    static function getTradeRec($GET){
        $uid = UID;
        $sql_head = "select * from a_trade_rec where uid = '$uid'";
        $sql_body = empty($GET['trade_type'])?"":" and type = '$GET[trade_type]'";
        $sql_body.= empty($GET['payment'])?"":" and payment = '$GET[payment]'";
        //判断日期是否合法
        if (!empty($GET["start_time"]) && !empty($GET["end_time"])){
            $GET["start_time"].=" 00:00:00";
            $GET["end_time"].=" 23:59:59";
            $sql_body.= !(strtotime($GET["start_time"]) && strtotime($GET["end_time"]))?"":" AND create_time between '$GET[start_time]' and '$GET[end_time]'";
        }
        $sql_body.= " order by create_time desc";

        //计算页码
        $sql_count = "select count(*) from a_trade_rec where uid = '$uid'".$sql_body;
        $num = Mysql::query($sql_count,1);
        self::$trade_rec_page = ceil($num[0]["count(*)"] / ROWS_PER_PAGE);
        //获取范围内数据
        $begin_i = empty($GET["p"]) ? 0 : ($GET["p"]-1) * ROWS_PER_PAGE;
        $sql = $sql_head.$sql_body." limit $begin_i,".ROWS_PER_PAGE;
        $data = Mysql::query($sql,1);

        return $data;
    }

}