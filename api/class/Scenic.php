<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20 0020
 * Time: 15:52
 */

class Scenic
{
    static function getScenicList(&$result,$GET){
        $sql = "select * from a_scenic";
        $data = Mysql::query($sql,1);
        if (empty($data)){
            $result["msg"] = "未找到景点";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "成功";
        foreach ($data as $row){
            $result["data"][] = $row;
        }
    }

}