<?php
new Mysql();
class Mysql
{
    static $con;
    function __construct(){
        self::$con = mysqli_connect(MYSQL_HOST,MYSQL_USER,MYSQL_PWD,MYSQL_DB);
        if(!self::$con){die("数据库连接失败！");}
        self::query("SET NAMES 'utf8'");
    }

    //执行语句
    static function query($sql,$fetch = false){
        $sql = trim($sql);
        $result = mysqli_query(self::$con,$sql);
        //添加语句执行记录
        if (true){
            $success = $result?1:0;
            $sql = str_replace("'","\"",$sql);
            $log = "insert into z_log(user,datetime,query,result) values('system',now(),'$sql',$success)";
            mysqli_query(self::$con,$log);
        }
        //根据是否需要转数组返回结果
        if ($fetch){
            return self::fetch($result);
        }
        return $result;
    }
    //查询结果转化数组
    static function fetch($result){
        $data = [];
        if (!empty($result)){
            while ($row = mysqli_fetch_array($result,1)){
                $data[] = $row;
            }
        }
        return $data;
    }
}