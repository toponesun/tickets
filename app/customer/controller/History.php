<?php

class History
{
    static function getHisNum(){
        $sql = "select count(*) as num from a_history where uid = '".UID."'";
        $data = Mysql::query($sql,1);
        $num = empty($data[0]["num"])?0:$data[0]["num"];
        return $num;
    }

    static function hisCtrl(){
        $sql = "DELETE FROM a_history where uid = '".UID."'";
        @Mysql::query($sql);
        return self::getHistoryList();
    }

    static function addHistory($GET){
        if(empty($GET["tid"])){
            return false;
        }
        $tid = $GET["tid"];
        $sql = "select * from a_history where uid = '".UID."' and tid = '$tid'";
        if($data = Mysql::query($sql,1)){
            $sql_up = "update a_history set click_time = now() where uid = '".UID.".' and tid = '$tid'";
        }else{
            $sql_up = "insert into a_history(uid,tid,click_time) values('".UID."','$tid',now())";
        }
        @Mysql::query($sql_up);
        return true;
    }

    static function getHistoryList()
    {
        $sql = "select a.click_time,b.* from a_history a,a_tickets b where a.uid = '".UID."' and a.tid = b.tid order by a.click_time desc";
        $data = Mysql::query($sql,1);
        return $data;
    }

}