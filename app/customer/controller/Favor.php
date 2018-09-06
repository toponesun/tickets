<?php

class Favor
{
    static function ctrlFavor($GET){
        if(empty($GET["tid"])){
            return "数据错误";
        }
        $uid = User::getUid();
        $tid = $GET["tid"];
        $sql = "select * from a_favor where uid = '$uid' and tid = '$tid'";
        $data = Mysql::query($sql,1);
        if(!empty($data)){
            $sql_up = "delete from a_favor where uid = '$uid' and tid = '$tid'";
            Mysql::query($sql_up);
            return "已取消收藏";
        }else{
            $sql_up = "insert into a_favor(uid,tid,add_time) values('$uid','$tid',now())";
            Mysql::query($sql_up);
            return "已收藏";
        }
    }

    static function getFavorNum(){
        $sql = "select count(*) as num from a_favor where uid = '".UID."'";
        $data = Mysql::query($sql,1);
        $num = empty($data[0]["num"])?0:$data[0]["num"];
        return $num;
    }

    static function getFavorList()
    {
        $sql = "select a.add_time,b.* from a_favor a,a_tickets b where a.uid = '".UID."' and a.tid = b.tid order by a.add_time desc";
        $data = Mysql::query($sql,1);
        return $data;
    }

}