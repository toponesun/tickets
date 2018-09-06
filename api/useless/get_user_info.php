<?php
include_once "../base.php";
$result = [
    "code"=>"0", "msg"=>"", "data"=>[]
];
if (empty($_POST["client"])){
    exit(json_encode($result));
}
if (!empty($_SESSION[$_POST["client"]])){
    $result["code"] = 1;
    $result["data"] = $_SESSION[$_POST["client"]];
}
exit(json_encode($result));
