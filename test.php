<?php
require_once "base.php";

echo "BASE_PATH = " .BASE_PATH."<br/>";
echo "APP_NAME = " .APP_NAME."<br/>";
echo "BASE_SERVER = " .BASE_SERVER."<br/>";

$sql = "delete from z_log";
$data = Mysql::query($sql);
//var_dump($data);