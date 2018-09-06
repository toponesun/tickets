<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20 0020
 * Time: 9:39
 */

class Sys
{
    static function rndKey($len){
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $KEY = "";
        $char_len = strlen($chars) - 1;
        if (!is_numeric($len)||$len <= 0)
            return false;
        for ($i = 0; $i < $len; $i++) {
            $KEY .= $chars[mt_rand(0, $char_len)];
        }
        $sql = "select ticket_KEY from a_mytickets where ticket_KEY = '$KEY'";
        if (Mysql::query($sql,1)){
            $KEY = self::rndKey($len);
        }
        return $KEY;
    }

    static function getSysSet(&$result){
        $data = Mysql::query("select * from sys_web_set",1);
        if (empty($data)){
            $result["msg"] = "没有找到有效的系统设置";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "成功";
        $result["data"]["TITLE_SUFFIX"] = $data[0]["title_suffix"];
        $result["data"]["ROWS_PER_PAGE"] = $data[0]["rows_per_page"];
        $result["data"]["FOOTER_TEXT"] = $data[0]["footer"];
    }


    static function arrToTerm($arr){
        $term = "";
        if (is_array($arr)){
            foreach ($arr as $value){
                $term.= ",'$value'";
            }
            $term = substr($term,1);
        }
        $term = "($term)";
        return $term;
    }


    //参数分析
    static function getParam($name,$Ajax){
        if (empty($Ajax[$name])){
            return false;
        }
        switch ($Ajax[$name]){
            case "title":
                $result = $Ajax[$name];
                break;
            case "data_range":
                $date_range = explode("-",$_GET["date_range"]);
                $result = [];
                if (!empty($date_range[1])){
                    $begin_time = $date_range[0]." 00:00:00";
                    $end_time = $date_range[1]." 23:59:59";
                    $result = [$begin_time,$end_time];
                }
                break;
            default:
                $result = false;
                break;
        }
        return $result;
    }

    //获取图片URL，返回数组
    static function getPicUrl($xid_name,$xid){
        if ($xid_name != "ticket" && $xid_name != "scenic" && $xid_name != "business"){
            return [];
        }
        $pic_dir = BASE_PATH."pictures/$xid_name/$xid/";
        $pic_group = [];
        if (@$dir = scandir($pic_dir)){
            if (file_exists($pic_dir."index.jpg")){
                $pic_group[] = "pictures/$xid_name/$xid/index.jpg";
            }
            foreach ($dir as $key => $value){
                if ($key > 1 && $value != "index.jpg"){
                    $file_type = explode("." , $value);
                    if (end($file_type) == "jpg"){
                        $pic_group[] = "pictures/$xid_name/$xid/$value";
                    }
                }
            }
        }
        if (empty($pic_group)){
            $pic_group[] = "pictures/$xid_name/default.jpg";
        }
        return $pic_group;
    }

}