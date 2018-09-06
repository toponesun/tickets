<?php
require_once "../base.php";

$sql = <<<LL
select a.*,b.name as scenic
from a_tickets a left join a_scenic b
on a.pid = b.pid 
where 1 = 1 
LL;
//sql语句加入筛选条件
$sql .= empty($_GET["stock"])?"":"AND a.stock > 0";
$sql .= empty($_GET["title"])?"":" AND a.title LIKE '%$_GET[title]%'";
$sql .= empty($_GET["type"])?"":" AND a.type = '$_GET[type]'";
$sql .= empty($_GET["scenic"])?"":" AND a.pid = '$_GET[pid]'";
//分离次数
$times = empty($GET["times"])?"":explode("-",$GET["times"],2);
$sql .= empty($times[1])?"":" AND a.times between '$times[0]' AND '$times[1]'";
//分离价格
$price = empty($GET["price"])?"":explode("-",$GET["price"],2);
$sql .= empty($price)?"":" AND a.price between '$price[0]' AND '$price[1]'";
//判断日期是否合法
if (!empty($_GET["date_range"])){
    $date_range = explode("-",$_GET["date_range"]);
    if (!empty($date_range[1])){
        $begin_time = $date_range[0]." 00:00:00";
        $end_time = $date_range[1]." 23:59:59";
        $sql .= !(strtotime($begin_time) && strtotime($end_time))?"":" AND ((a.begin_time <= '$end_time' and a.end_time >= '$begin_time') or a.tic_type = 2)";
    }
}
//按update_time倒序排列
$sql .=" order by a.update_time DESC";
$data = Mysql::query($sql,1);
$result = [];
foreach ($data as $row){
    if ($row["tic_type"] == 2){
        $row["tic_type"] = "计时票";
        $row["begin_end_time"] = "购买后".$row["valid_days"]."天内";
    }else{
        if ($row["tic_type"] == 1){
            $row["tic_type"] = "常规票";
        }else{
            $row["tic_type"] = "套票";
        }
        $row["begin_end_time"] = "> ".$row["begin_time"]."<br/>< ".$row["end_time"];
    }
    $result[] = $row;
}

$result = json_encode($result,1);
echo $result;