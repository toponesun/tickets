<?php

class Ticket
{
    //获取可购买的票务信息分页版
    static function getTicketList(&$result,$GET)
    {
        $sql_head = <<<LL
        select a.*,b.name as scenic
        from a_tickets a left join a_scenic b
        on a.pid = b.pid 
        where 1 = 1 
LL;
        //sql语句加入筛选条件
        $sql_body = empty($GET["stock"])?" AND a.stock >= 0":"a.stock > 0";
        $sql_body.= empty($GET["title"])?"":" AND a.title LIKE '%$GET[title]%'";
        $sql_body.= empty($GET["type"])?"":" AND a.type = '$GET[type]'";
        $sql_body.= empty($GET["pid"])?"":" AND a.pid = '$GET[pid]'";
        //分离次数
        $times = empty($GET["times"])?"":explode("-",$GET["times"],2);
        $sql_body.= empty($times[1])?"":" AND a.times between '$times[0]' AND '$times[1]'";
        //分离价格
        $price = empty($GET["price"])?"":explode("-",$GET["price"],2);
        $sql_body.= empty($price)?"":" AND a.price between '$price[0]' AND '$price[1]'";
        //判断日期是否合法
        if (!empty($GET["date_range"])){
            $date_range = explode("-",$GET["date_range"]);
            if (!empty($date_range[1])){
                $begin_time = $date_range[0]." 00:00:00";
                $end_time = $date_range[1]." 23:59:59";
                $sql_body .= !(strtotime($begin_time) && strtotime($end_time))?"":" AND ((a.begin_time <= '$end_time' and a.end_time >= '$begin_time') or a.tic_type = 2)";
            }
        }

        //按update_time倒序排列
        $sql_body .= " order by a.update_time DESC";

        //计算页码
        $sql_count = "select count(*) from a_tickets a where 1 = 1".$sql_body;
        $num = Mysql::query($sql_count,1);
        $rows = empty($GET["rows"])?ROWS_PER_PAGE:(int)$GET["rows"];
        $pages = ceil($num[0]["count(*)"] / $rows);
        //获取范围内数据
        $begin_i = empty($GET["p"]) ? 0 : ($GET["p"]-1) * $rows;
        $sql = $sql_head.$sql_body." limit $begin_i,".$rows;

        $data = Mysql::query($sql,1);
        if (empty($data)){
            $result["msg"] = "没有找到此类票券！";
            return;
        }
        $result["code"] = 1;
        $result["msg"] = "成功";
        $result["data"]["pages"] = $pages;
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
            $result["data"]["list"][] = $row;
        }
    }


    //获取订单详情
    static function getTicketDetail(&$result,$GET){
        if (empty($GET["tid"])){
            $result["msg"] = "必须传入tid！";
            return;
        }
        $tid = $GET["tid"];
        $sql = "select a.*,b.sale_name,b.begin_time as sale_begin_time,b.end_time as sale_end_time from a_tickets a left join a_sale b on a.sale_id = b.sale_id where a.tid = '$tid' ";
        $data = Mysql::query($sql,1);
        if (empty($data)){
            $result["msg"] = "未找到此票券信息！";
            return;
        }

        $result["code"] = 1;
        $result["msg"] = "成功！";
        $result["data"] = $data[0];
        $result["data"]["pic_group"] = Sys::getPicUrl("ticket",$tid);
        //如果是组合票，加入子票券信息
        if ($data[0]["tic_type"] == 3){
            $sql = "select a.father_tid,b.* from a_tickets_child a,a_tickets b where a.father_tid = '$tid' and a.child_tid = b.tid";
            $data_group = Mysql::query($sql,1);
            $result["data"]["tic_sec"] = $data_group;
        }
    }

    static function getTicketTerm(&$result,$GET){
        $result["code"] = 1;
        $result["msg"] = "成功";
        $terms = [
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
            $result["data"][] = ["title"=>$term["title"],"name"=>$term["name"],"value"=>$value];
        }

        //加入手动控制的查找内容
        $result["data"][] = ["title"=>"有效次数","name"=>"times","value"=>["0-1"=>"1次","2-10"=>"2-10次","10-1000"=>"10次以上"]];
        $result["data"][] = ["title"=>"价格区间","name"=>"price","value"=>["0-200"=>"200元以下","200-500"=>"200-500元","500-1000"=>"500-1000元"]];
    }

}