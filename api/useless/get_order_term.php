<?php
require_once "../base.php";
$terms = [
    ["title"=>"订单状态","name"=>"order_state","col_key"=>"state_id","col_val"=>"state_name","tb"=>"app_order_state"]
];

foreach ($terms as $term){
    $sql = "select $term[col_key],$term[col_val] from $term[tb] group by $term[col_val]";
    $data = Mysql::query($sql);
    $value = [];
    if (!empty($data)){
        foreach ($data as $row){
            $value[$row[$term["col_key"]]] = $row[$term["col_val"]];
        }
    }
    $result[] = ["title"=>$term["title"],"name"=>$term["name"],"value"=>$value];
}

//加入手动控制的查找内容
$result[] = ["title"=>"回收站","name"=>"visibility","value"=>["2"=>"已删除订单",""=>"全部"]];

$result = json_encode($result);
echo $result;
