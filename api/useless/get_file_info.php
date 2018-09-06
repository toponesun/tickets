<?php
require_once "../base.php";
$file_names = [
    "admin" => [
        "index" => "主页",
        "all-tickets" => "票券总览",
        "auto-update" => "自动库存",
    ],
    "customer" => [

    ],
    "conductor" => [

    ],
    "business" => [

    ]
];
$result = [
    "code"=>"1",
    "msg"=>"",
    "data"=>$file_names
];

exit(json_encode($result,1));