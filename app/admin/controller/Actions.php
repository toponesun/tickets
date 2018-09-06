<?php

class Actions
{
    static function getPageNameByFileName($file_name){
        switch ($file_name){
            case "index":
                $page_name = "主页";
                break;
            case "all-tickets":
                $page_name = "票券总览";
                break;
            case "auto-update":
                $page_name = "库存自动更新";
                break;
            case "all-tic-type":
                $page_name = "票券种类";
                break;
            case "all-scenic":
                $page_name = "景点总览";
                break;
            case "all-business":
                $page_name = "商家总览";
                break;
            case "all-sale":
                $page_name = "折扣设置";
                break;
            case "all-device":
                $page_name = "设备总览";
                break;
            case "web-set":
                $page_name = "网站设置";
                break;
            case "order-form":
                $page_name = "订单报表";
                break;
            case "ticket-form":
                $page_name = "票券报表";
                break;
            case "trade-form":
                $page_name = "交易报表";
                break;
            case "record-form":
                $page_name = "流水报表";
                break;
            case "test":
                $page_name = "测试功能";
                break;
            default:
                $page_name = "404页面";
                break;
        }
        return $page_name;
    }


    static function getNameByXid($xid_name,$xid){
        switch ($xid_name){
            case "tid":
                $tb_name = "a_tickets";
                $col_name = "title";
                break;
            case "pid":
                $tb_name = "a_scenic";
                $col_name = "name";
                break;
            case "bid":
                $tb_name = "a_business";
                $col_name = "name";
                break;
            case "sale_id":
                $tb_name = "a_sale";
                $col_name = "sale_name";
                break;
            default:
                return false;
        }
        $sql = "select $col_name from $tb_name where $xid_name = '$xid'";
        $data = Mysql::query($sql,1);
        if (empty($data)){
            return false;
        }else{
            return $data[0][$col_name];
        }
    }

    //随机生成KEY
    static function randKey($len){
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $Key = "";
        $char_len = strlen($chars) - 1;
        if (!is_numeric($len)||$len <= 0)
            return false;
        for ($j = 0; $j < $len; $j++) {
            $Key .= $chars[mt_rand(0, $char_len)];
        }
        return $Key;
    }

    //json转化为数据库查询条件
    static function jsonToTerm($json){
        $arr = json_decode($json);
        $term = "";
        if (is_array($arr)){
            foreach ($arr as $value){
                $term.= empty($term)?"'$value'":",'$value'";
            }
            $term = "($term)";
        }
        return $term;
    }

    static function getOpts($opt_type=null,$selected=null,$where=null){
        $sql_tail = "";
        switch ($opt_type){
            case "ticket":
                $opt_val = "tid";
                $opt_txt = "title";
                $tb_name = "a_tickets";
                $sql_tail = "and tic_type != 3";
                break;
            case "scenic":
                $opt_val = "pid";
                $opt_txt = "name";
                $tb_name = "a_scenic";
                break;
            case "business":
                $opt_val = "bid";
                $opt_txt = "name";
                $tb_name = "a_business";
                break;
            case "sale":
                $opt_val = "sale_id";
                $opt_txt = "sale_name";
                $tb_name = "a_sale";
                break;
            case "type":
                $opt_val = "type_name";
                $opt_txt = "type_name";
                $tb_name = "app_ticket_type";
                break;
            case "city":
                $opt_val = "city_name";
                $opt_txt = "city_name";
                $tb_name = "app_city";
                break;
            case "device":
                $opt_val = "device_id";
                $opt_txt = "device_name";
                $tb_name = "b_device";
                break;
            case "tic-state":
                $opt_val = "state_id";
                $opt_txt = "state_name";
                $tb_name = "app_ticket_state";
                break;
            default:
                return false;
        }
        $sql = "select $opt_val,$opt_txt from $tb_name where 1=1 $sql_tail $where group by $opt_val";
        $data = Mysql::query($sql,1);
        $options = "";
        foreach ($data as $arr){
            $options .= ($selected == $arr[$opt_val])
                ?"<option value='$arr[$opt_val]' selected>$arr[$opt_txt]</option>"
                :"<option value='$arr[$opt_val]'>$arr[$opt_txt]</option>";
        }
        return $options;
    }


    //生成各种id
    static function makeID($xid,$pre_xid = ""){
        switch ($xid){
            case "tid":
                $tb_name = "a_tickets";
                break;
            case "pid":
                $tb_name = "a_scenic";
                break;
            case "bid":
                $tb_name = "a_business";
                break;
            case "sale_id":
                $tb_name = "a_sale";
                break;
            case "device_id":
                $tb_name = "b_device";
                break;
            default:
                return false;
        }

        $today_str = date("Ymd",time());
        $sql = "select max($xid) as xid from $tb_name where $xid like '%$today_str%'";
        $data = Mysql::query($sql,1);
        $num = 1;
        if(!empty($data[0]["xid"])){
            $last_xid = $data[0]["xid"];
            $num = substr($last_xid,-4);
            $num++;
        }
        $new_xid = $pre_xid.$today_str.sprintf("%04d",$num);
        return $new_xid;
    }

    static function _GET_kv($keyword,$value,$GET){
        $url = "?";
        if(!empty($GET)){
            foreach($GET as $key=>$val){
                $url.= ($key==$keyword)?"":"$key=$val&";
            }
        }
        $url.= "$keyword=$value";
        return $url;
    }

    static function getPageHtml($page=1, $pages, $url,$GET){
        //最多显示多少个页码
        $_pageNum = 5;
        //当前页面小于1 则为1
        $page = $page<1 ? 1:$page;
        //当前页大于总页数 则为总页数
        $page = $page > $pages ? $pages : $page;
        //页数小当前页 则为当前页
        $pages = $pages < $page ? $page : $pages;
        //计算开始页
        $_start = $page - floor($_pageNum/2);
        $_start = $_start<1 ? 1 : $_start;
        //计算结束页
        $_end = $page + floor($_pageNum/2);
        $_end = $_end>$pages? $pages : $_end;
        //当前显示的页码个数不够最大页码数，在进行左右调整
        $_curPageNum = $_end-$_start+1;
        //左调整
        if($_curPageNum<$_pageNum && $_start>1){
            $_start = $_start - ($_pageNum-$_curPageNum);
            $_start = $_start<1 ? 1 : $_start;
            $_curPageNum = $_end-$_start+1;
        }
        //右边调整
        if($_curPageNum<$_pageNum && $_end<$pages){
            $_end = $_end + ($_pageNum-$_curPageNum);
            $_end = $_end>$pages? $pages : $_end;
        }
        $_pageHtml = '<ul>';
        if($page>1){
            $_pageHtml .= '<li onclick="go(\''.$url.self::_GET_kv("p",($page-1),$GET).'\')"><<</li>';
        }
        for ($i = $_start; $i <= $_end; $i++) {
            if($i == $page){
                $_pageHtml .= '<li class="active">'.$i.'</li>';
            }else{
                $_pageHtml .= '<li onclick="go(\''.$url.self::_GET_kv("p",$i,$GET).'\')">'.$i.'</li>';
            }
        }
        if($page<$_end){
            $_pageHtml .= '<li onclick="go(\''.$url.self::_GET_kv("p",($page+1),$GET).'\')">>></li>';
        }
        $_pageHtml .= '</ul>';
        $_pageHtml = <<<LL
        <div class="pagination">
            $_pageHtml
        </div>
LL;
        return $_pageHtml;
    }

}