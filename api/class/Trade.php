<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/25 0025
 * Time: 16:48
 */

class Trade
{
    static function getTradeList(&$result,$GET){
        if (!Ajax::isLogin($result,$GET)){
            return;
        }
        $uid = $_SESSION[$GET["client"]]["uid"];
        $sql_head = "select * from a_trade_rec where ";
        //sql语句加入筛选条件
        $sql_body = " uid = '$uid'";
        $sql_body.= empty($GET['trade_type'])?"":" and type = '$GET[trade_type]'";
        $sql_body.= empty($GET['payment'])?"":" and payment = '$GET[payment]'";
        //判断日期是否合法
        if (!empty($GET["start_time"]) && !empty($GET["end_time"])){
            $GET["start_time"].=" 00:00:00";
            $GET["end_time"].=" 23:59:59";
            $sql_body.= !(strtotime($GET["start_time"]) && strtotime($GET["end_time"]))?"":" AND create_time between '$GET[start_time]' and '$GET[end_time]'";
        }
        $sql_body.= " order by create_time desc";
        //计算页码
        $sql_count = "select count(*) from a_trade_rec a where ".$sql_body;
        $num = Mysql::query($sql_count,1);
        $rows = empty($GET["rows"])?ROWS_PER_PAGE:(int)$GET["rows"];
        $pages = ceil($num[0]["count(*)"] / $rows);

        //获取范围内数据
        $begin_i = empty($GET["p"]) ? 0 : ($GET["p"]-1) * $rows;
        $sql = $sql_head.$sql_body." limit $begin_i,".$rows;
        $data = Mysql::query($sql,1);

        if (empty($data)){
            $result["msg"] = "没有找到此类交易记录！";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "成功";
        $result["data"]["pages"] = $pages;

        foreach ($data as $row){
            $result["data"]["list"][] = $row;
        }
    }


    static function getReportData(&$result,$GET){
        if (!Ajax::isLogin($result,$GET)){
            return;
        }
        $uid = $_SESSION[$GET["client"]]["uid"];
        $data1 = $data2 = $data3 = $data4 = [];
        for ($i = 4; $i >= 1; $i--) {
            $week2 = $i - 1;
            $sql = "select sum(money) from a_trade_rec where uid = '$uid' and create_time between date_add(now(), interval -$i week) and date_add(now(), interval -$week2 week)";
            if ($data = Mysql::query($sql,1)) {
                $data1[] = empty($data[0]["sum(money)"]) ? 0 : (float)$data[0]["sum(money)"];
            }
            $sql = "select count(*) from a_mytickets where state > 0 and uid = '$uid' and create_time between date_add(now(), interval -$i week) and date_add(now(), interval -$week2 week)";
            if ($data = Mysql::query($sql,1)) {
                $data2[] = empty($data[0]["count(*)"]) ? 0 : (int)$data[0]["count(*)"];
            }
        }
        $term = ["", " between 0 and 200", " between 200 and 500", "  between 500 and 1000", "  between 1000 and 1000000"];
        for ($i = 4; $i >= 1; $i--) {
            $sql = "select count(*) from a_mytickets a,a_tickets b where a.state > 0 and a.uid = '$uid' and a.create_time > date_add(now(), interval -1 month) and a.tid = b.tid and b.price ";
            $sql .= $term[$i];
            if ($data = Mysql::query($sql,1)) {
                $data3[$i] = empty($data[0]["count(*)"]) ? 0 : (int)$data[0]["count(*)"];
            }
        }
        for ($i=30;$i>=1;$i--){
            $j = $i-1;
            $sql = "select count(*) from a_mytickets where state > 0 and uid = '$uid' and create_time between date_add(now(), interval -$i day) and date_add(now(), interval -$j day)";
            if ($data = Mysql::query($sql,1)) {
                $data4[] = empty($data[0]["count(*)"]) ? 0 : (int)$data[0]["count(*)"];
            }
        }
        $result["code"] = 1;
        $result["msg"] = "成功";
        $result["data"] = [
            "data1" => $data1,
            "data2" => $data2,
            "data3" => $data3,
            "data4" => $data4
        ];
    }

}