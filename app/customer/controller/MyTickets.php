<?php

class MyTickets
{
    static $my_tickets_page;
    static function getMyTicketsInfo($GET)
    {
        $uid = UID;
        $sql_head = "select a.*,c.state_name from a_mytickets a left join app_ticket_state c on a.state = c.state_id where ";
        //sql语句加入筛选条件
        $sql_body =  "a.uid = '$uid' and a.father_KEY is null";
        $sql_body.= empty($GET["title"])?"":" AND b.title LIKE '%$GET[title]%'";
        $sql_body.= empty($GET["type"])?"":" AND b.type = '$GET[type]'";
        $sql_body.= empty($GET["city"])?"":" AND b.city = '$GET[city]'";
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
        $sql_count = "select count(*) from a_mytickets a where ".$sql_body;
        $num = Mysql::query($sql_count,1);
        self::$my_tickets_page = ceil($num[0]["count(*)"] / ROWS_PER_PAGE);

        //获取范围内数据
        $begin_i = empty($GET["p"]) ? 0 : ($GET["p"]-1) * ROWS_PER_PAGE;
        $sql = $sql_head.$sql_body." limit $begin_i,".ROWS_PER_PAGE;
        $data = Mysql::query($sql,1);
        return $data;
    }

    //获取我的票券详细信息
    static function getTicketDetail($GET)
    {
        $uid = UID;
        $ticket_key = empty($GET["ticket_key"])?"":$GET["ticket_key"];
        if (!empty($ticket_key)){
            $sql_rnd = "select timestampdiff(second,rnd_time,now()) as rnd_sec from a_mytickets where uid = '$uid' and ticket_KEY = '$ticket_key'";
            $data_rnd = Mysql::query($sql_rnd,1);
            if (empty($data_rnd[0]["rnd_sec"])||$data_rnd[0]["rnd_sec"] > RND_KEY_LIVE_TIME){
                $rnd_key = Actions::randKey(4);
                $sql_update = "update a_mytickets set rnd_key = '$rnd_key',rnd_time = now() where ticket_KEY = '$ticket_key'";
                Mysql::query($sql_update);
            }

            $sql = "select a.*,b.state_name,c.device_name from a_mytickets a left join app_ticket_state b on a.state = b.state_id left join b_device c on a.device_id = c.device_id where a.uid = '$uid' and a.ticket_KEY = '$ticket_key'";
            $data = Mysql::query($sql,1);
            $data[0] = empty($data[0])?[]:$data[0];
            return $data[0];
        }else{
            return [];
        }
    }

    //获取下一张票券信息
    static function getNextTicketKey($ticket_key){
        $uid = UID;
        $sql = "select state from a_mytickets where ticket_key = '$ticket_key' and uid = '$uid'";
        $ticket_state = ($data = Mysql::query($sql,1))?$data[0]["state"]:"";
        $next_ticket_key = "";
        if ($ticket_state == 2){
            $sql = "select ticket_KEY from a_mytickets where uid = '$uid' and tid = (select tid from a_mytickets where ticket_KEY = '$ticket_key') and state = 1 and ticket_KEY != '$ticket_key' ORDER BY create_time LIMIT 0,1";
            if ($data = Mysql::query($sql,1)){
                $next_ticket_key = $data[0]["ticket_KEY"];
            }else{
                $next_ticket_key = "none";
            }
        }
        return $next_ticket_key;
    }
}