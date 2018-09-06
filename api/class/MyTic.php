<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/25 0025
 * Time: 16:48
 */

class MyTic
{
    static function getMyTicList(&$result,$GET){
        if (!Ajax::isLogin($result,$GET)){
            return;
        }
        $client = $GET["client"];
        $uid = $_SESSION[$client]["uid"];
        $sql_head = "select a.*,c.state_name from a_orders b,a_mytickets a left join app_ticket_state c on a.state = c.state_id where ";
        //sql语句加入筛选条件
        $sql_body = " a.uid = '$uid'";
        $sql_body.= " and a.father_KEY is null and a.oid = b.oid";
        $sql_body.= empty($GET["title"])?"":" AND a.title LIKE '%$GET[title]%'";
        $sql_body.= empty($GET["type"])?"":" AND a.type = '$GET[type]'";
        $sql_body.= empty($GET["city"])?"":" AND a.city = '$GET[city]'";
        $sql_body.= empty($GET["oid"])?"":" AND a.oid = '$GET[oid]'";
        $sql_body.= empty($GET['ticket_state'])?" and a.state > 0":" and a.state = $GET[ticket_state]";
        //分离次数
        $times = empty($GET["times"])?"":explode("-",$GET["times"],2);
        $sql_body.= empty($times)?"":" AND a.times between '$times[0]' AND '$times[1]'";
        //分离价格
        $price = empty($GET["price"])?"":explode("-",$GET["price"],2);
        $sql_body.= empty($price)?"":" AND b.price between '$price[0]' AND '$price[1]'";
        //判断日期是否合法
        if (!empty($GET["start_time"]) && !empty($GET["end_time"])){
            $GET["start_time"].=" 00:00:00";
            $GET["end_time"].=" 23:59:59";
            $sql_body .= !(strtotime($GET["start_time"]) && strtotime($GET["end_time"]))?"":" AND create_time between '$GET[start_time]' AND '$GET[end_time]'";
        }
        //按获取时间倒序排列
        $sql_body .= " order by a.create_time DESC";
        //计算页码
        $sql_count = "select count(*) from a_mytickets a,a_orders b where ".$sql_body;
        $num = Mysql::query($sql_count,1);
        $rows = empty($GET["rows"])?ROWS_PER_PAGE:(int)$GET["rows"];
        $pages = ceil($num[0]["count(*)"] / $rows);

        //获取范围内数据
        $begin_i = empty($GET["p"]) ? 0 : ($GET["p"]-1) * $rows;
        $sql = $sql_head.$sql_body." limit $begin_i,".$rows;
        $data = Mysql::query($sql,1);

        if (empty($data)){
            $result["msg"] = "没有找到此类票券！";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "成功";
        $result["data"]["pages"] = $pages;

        foreach ($data as $row){
            if ($row["tic_type"] == 1){
                $row["tic_type"] = "常规票";
                $row["times"] = $row["times"]."次(共".$row["orig_times"]."次)";
            }elseif($row["tic_type"] == 2){
                $row["tic_type"] = "计时票";
                $row["times"] = "不限次数";
            }elseif($row["tic_type"] == 3){
                $row["tic_type"] = "套票";
                $row["times"] = "-";
                $row["type"] = "套票";
            }
            $row["begin_end_time"] = "起：".$row["begin_time"]."<br/>止：".$row["end_time"];
            $result["data"]["list"][] = $row;
        }
    }

    /*获取的是券码的详细信息*/
    static function getMyTicDetail(&$result,$GET){
        if (!Ajax::isLogin($result,$GET)){
            return;
        }
        if (empty($GET["ticket_KEY"])){
            $result["msg"] = "必须传入券码！";
            return;
        }
        $client = $GET["client"];
        $ticket_key = $GET["ticket_KEY"];
        $uid = $_SESSION[$client]["uid"];
        $sql_rnd = "select timestampdiff(second,rnd_time,now()) as rnd_sec from a_mytickets where uid = '$uid' and ticket_KEY = '$ticket_key'";
        $data_rnd = Mysql::query($sql_rnd,1);
        if (empty($data_rnd[0]["rnd_sec"])||$data_rnd[0]["rnd_sec"] > RND_KEY_LIVE_TIME){
            $rnd_key = Sys::rndKey(4);
            $sql_update = "update a_mytickets set rnd_key = '$rnd_key',rnd_time = now() where ticket_KEY = '$ticket_key'";
            Mysql::query($sql_update);
        }

        $sql = "select a.*,b.state_name,c.device_name from a_mytickets a left join app_ticket_state b on a.state = b.state_id left join b_device c on a.device_id = c.device_id where a.uid = '$uid' and a.ticket_KEY = '$ticket_key'";
        $data = Mysql::query($sql,1);
        if (empty($data[0])){
            $result["msg"] = "没有找到此券码，请确定是当前账户购买";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "成功！";
        $result["data"] = $data[0];
        //如果是组合票，加入子票券信息
        if ($data[0]["tic_type"] == 3){
            $sql = "select a.*,b.state_name,c.type_name from a_mytickets a left join app_ticket_state b on a.state = b.state_id left join app_ticket_type c on a.type = c.id where a.uid = '$uid' and a.father_KEY = '$ticket_key'";
            $data_sec = Mysql::query($sql, 1);
            $result["data"]["my_tic_sec"] = $data_sec;
        }
    }

    /*请求作废券码，仅售票员可操作自己售卖的券码*/
    static function invalidTic(&$result,$POST){
        if ($POST["client"] !== "conductor"){
            $result["msg"] = "此功能仅售票员可用！";
            return;
        }
        if (!Ajax::isLogin($result,$POST)){
            return;
        }
        if (empty($POST["ticket_KEY"])){
            $result["msg"] = "必须传入券码！";
            return;
        }

        $client = $POST["client"];
        $ticket_key = $POST["ticket_KEY"];
        $uid = $_SESSION[$client]["uid"];
        $sql = "select * from a_mytickets where ticket_KEY = '$ticket_key' and uid = '$uid'";
        $data = Mysql::query($sql,1);
        if (empty($data[0])){
            $result["msg"] = "没有找到此券码，请确定是当前账户购买";
            return;
        }
        if ($data[0]["state"] != 1){
            $result["msg"] = "票券状态不正确，无法作废！";
            return;
        }

        $sql = "update a_mytickets set state = 6 where ticket_KEY = '$ticket_key' and uid = '$uid'";
        if (!Mysql::query($sql)){
            $result["msg"] = "执行操作出现问题";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "此票券已经作废成功！";
    }

}