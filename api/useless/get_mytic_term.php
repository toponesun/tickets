<?php
require_once "../base.php";
$terms = [
    ["title"=>"票券状态","name"=>"ticket_state","col_key"=>"state_id","col_val"=>"state_name","tb"=>"app_ticket_state"],
    ["title"=>"票券种类","name"=>"type","col_key"=>"type_name","col_val"=>"type_name","tb"=>"app_ticket_type"],
    ["title"=>"所属景点","name"=>"scenic","col_key"=>"pid","col_val"=>"name","tb"=>"a_scenic"]
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
$result[] = ["title"=>"有效次数","name"=>"times","value"=>["0-1"=>"1次","2-10"=>"2-10次","10-1000"=>"10次以上"]];
$result[] = ["title"=>"价格区间","name"=>"price","value"=>["0-200"=>"200元以下","200-500"=>"200-500元","500-1000"=>"500-1000元"]];

$result = json_encode($result);
echo $result;
