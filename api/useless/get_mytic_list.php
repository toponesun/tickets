<?php
require_once "../base.php";
if (empty($_SESSION["user"]["uid"])){
    return "未登录";
}
$uid = $_SESSION["user"]["uid"];
$sql = <<<LL
select * from a_mytickets where uid = '$uid'
LL;
$data = Mysql::query($sql,1);

$result = $data;

$result = json_encode($result);
echo $result;