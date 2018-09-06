<?php
require_once "../base.php";

$sql = <<<LL
select * from a_scenic
LL;
$data = Mysql::query($sql,1);

$result = $data;

$result = json_encode($result);
echo $result;