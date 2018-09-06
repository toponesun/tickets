<?php

class Coupon
{
    static function getCouponNum(){
        if(empty($_SESSION["uid"])){
            return 0;
        }
        $uid = $_SESSION["uid"];
        $sql = "select count(*) as num from a_mycoupon where uid = '$uid'";
        $num = 0;
        if ($data = Mysql::query($sql,1)){
            $num = $data[0]["num"];
        }
        return $num;
    }

    static function getCouponList()
    {
        if(empty($_SESSION["uid"])){
            return "未登录！";
        }
        $uid = $_SESSION["uid"];
        $sql = "select b.* from a_mycoupon a,a_coupon b where a.cid = b.cid and a.uid = '$uid' order by get_time desc";
        $data = Mysql::query($sql,1);
        return $data;
    }


}