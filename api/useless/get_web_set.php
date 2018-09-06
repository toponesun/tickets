<?php
require_once "../base.php";
$sql = <<<LL
select * from sys_web_set where state = 1
LL;
$data = Mysql::query($sql,1);
$result = $data;
$result = json_encode($result);
echo $result;