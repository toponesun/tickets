<?php

class MobileActions extends Actions
{
    static function getCategoryHtml(){
        $terms = array("type"=>array("title"=>"适合人群","成人票"=>"成人票","儿童票"=>"儿童票","老年票"=>"老年票","学生票"=>"学生票"),
            "scenic"=>array("title"=>"景点"),
            "tic_type"=>array("title"=>"票券类型","1"=>"常规票","2"=>"计时票","3"=>"组合票/套票"),
            "price"=>array("title"=>"价格区间","0-200"=>"200元以下","200-500"=>"200-500元","500-1000"=>"500-1000元")
        );

        $sql = "select id,type_name from app_ticket_type group by type_name";
        $data = Mysql::query($sql,1);
        $terms["type"] = array("title"=>"适合人群");
        foreach ($data as $row){
            $terms["type"][$row['type_name']] = $row['type_name'];
        }

        $sql = "select pid,name from a_scenic group by name";
        $data = Mysql::query($sql,1);
        $terms["scenic"] = array("title"=>"景点");
        foreach ($data as $row){
            $terms["scenic"][$row['pid']] = $row['name'];
        }

        $html = "";
        foreach ($terms as $term=>$array) {
            $array[""] = "全部";
            $cate_html = "";
            foreach ($array as $key => $value) {
                if ($key != "title") {
                    $cate_html .= <<<LL
                    <label class="cate-value">
                        <input type="radio" style="display: none" name="$term" value="$key"/>
                        <div class="cate-val">
                            $value
                        </div>
                    </label>
LL;
                }
            }
            $html .= <<<LL
            
            <div class="card">
                <div class="cate-name">$array[title]</div>
                <div class="cate-values">$cate_html </div>
            </div>
LL;
        }

        $html = <<<LL
        <form id="form-category" action="?file=tickets" method="get" style="padding-top: 50px">
            <div id="search-box" class="main-title" style="width:100%;position:absolute;top:0;text-align: center;color: #02879B;z-index: 52;border: none">
                <input type="text" id="search" style="border: solid 0.5px #666;" name="title" onfocus="area_show('search-area');" onblur="area_show()" maxlength="20" class="search" placeholder="输入你要查找的内容"/>
                <div class="search-touch" style="color: #02879B" onclick="$('#form-category').submit();">搜索</div>
            </div>
            <input type="text" hidden id="filename" name="file" value="tickets"/>
            $html
            <div class="card">
                <div class="cate-name">日期</div>
                <div class="cate-values">
                    <label style="margin-left: 10px">
                        <input type="date" name="start_time"/> ~~     
                        <input type="date" name="end_time"/>
                    </label>  
                </div>
            </div>
        </form>
LL;

        return $html;
    }

    static function getTermHtml($file_name,$b=null){
        switch ($file_name){
            case "tickets":
                $terms = array("type"=>array("title"=>"票券种类","成人票"=>"成人票","儿童票"=>"儿童票","老年票"=>"老年票","学生票"=>"学生票"),
                    "times"=>array("title"=>"有效次数","0-1"=>"1次","2-10"=>"2-10次","10-1000"=>"10次以上"),
                    "city"=>array("title"=>"选择城市","深圳"=>"深圳","广州"=>"广州"),
                    "price"=>array("title"=>"价格区间","0-200"=>"200元以下","200-500"=>"200-500元","500-1000"=>"500-1000元")
                );
                $sys_terms = array("type"=>"票券种类","city"=>"城市");
                foreach ($sys_terms as $key=>$value){
                    $sql = "select $key from a_tickets group by $key";
                    $data = Mysql::query($sql,1);
                    $terms[$key] = array("title"=>$value);
                    foreach ($data as $term){
                        if (!empty($term[$key])){
                            $terms[$key][$term[$key]] = $term[$key];
                        }
                    }
                }
                break;
            case "myTickets":
                $terms = array("ticket_state"=>array("title"=>"票券状态","1"=>"有效","2"=>"已使用","3"=>"已退票","5"=>"已过期"),
                    "times"=>array("title"=>"有效次数","0-1"=>"1次","2-10"=>"2-10次","10-1000"=>"10次以上"),
                    "city"=>array("title"=>"选择城市","深圳"=>"深圳","广州"=>"广州"),
                    "price"=>array("title"=>"价格区间","0-200"=>"200元以下","200-500"=>"200-500元","500-1000"=>"500-1000元")
                );
                $sys_terms = array("city"=>"城市");
                foreach ($sys_terms as $key=>$value){
                    $sql = "select b.$key from a_mytickets a,a_tickets b where a.tid = b.tid group by b.$key";
                    $data = Mysql::query($sql,1);
                    $terms[$key] = array("title"=>$value);
                    foreach ($data as $term){
                        if (!empty($term[$key])){
                            $terms[$key][$term[$key]] = $term[$key];
                        }
                    }
                }
                break;
            case "myOrders":
                $terms = array("order_state"=>array("title"=>"订单状态","-1"=>"待支付","1"=>"已支付","2"=>"已取消","3"=>"已退票"),
                    "visibility"=>array("title"=>"回收站","2"=>"已删除订单")
                );
                break;
            case "tradeRec":
                $terms = array("trade_type"=>array("title"=>"交易类型","订单支付"=>"订单支付","订单退款"=>"订单退款","余额充值"=>"余额充值"),
                    "payment"=>array("title"=>"交易方式","余额支付"=>"余额支付","微信支付"=>"微信支付","支付宝"=>"支付宝")
                );
                break;
            default:
                $terms = array();
                break;
        }
        $html = <<<LL
        <div class="auto-output" style="font-size: 18px;overflow-y: auto;height: 100%;padding-bottom: 42px">
LL;
        foreach ($terms as $term=>$array){
            $html.= <<<LL
            
            <div style="border-top: solid 0.5px #8c8c8c;margin-top: 0;height: auto;padding: 8px">
                <div style="float: left;border-right: solid 0.5px #8c8c8c;width: 25%;height:100%;text-align: center;line-height: 30px">
                    $array[title]
                </div>
                <div style="width:75%;float: left;">
                    <label style="margin-left: 10px">
                        <input type="radio" style="display: none" name="$term" value=""/>
                        <span style="font-size: 18px;font-weight: normal;padding: 3px 10px">
                            全部
                        </span>
                    </label>
LL;

            foreach ($array as $key=>$value){
                if($key!="title"){
                    $html.= <<<LL
                    
                    <label style="margin-left: 10px"><input type="radio" style="display: none" name="$term" value="$key"/>
                        <span style="font-size: 18px;font-weight: normal;padding: 3px 10px">
                            $value
                        </span>
                    </label>
LL;
                }
            }
            $html.=<<<LL
            
                </div>
                <div style="font: 0/0 sans-serif;clear: both;display: block"> </div>
            </div>
LL;
        }
        $html.=<<<LL
           <div style="border-top: solid 0.5px #8c8c8c;margin-top: 0;height: auto;padding: 8px">
                <div style="float: left;border-right: solid 0.5px #8c8c8c;width: 25%;height:100%;text-align: center;line-height: 30px">
                    日期
                </div>
                <div style="width:75%;float: left;">
                    <label style="margin-left: 10px"><input type="date" name="start_time"/></label>~~              
                    <label style="margin-left: 10px"><input type="date" name="end_time"/></label>  
                </div>
            </div>        
                </div>

LL;
        $html = <<<LL
    <div class="select-area" hidden>
        <form id="form1" action="" method="get" style="height: 100%">
            <input type="text" hidden id="filename" name="file" value="{$file_name}"/>
            $html
        </form>
        <div style="position: absolute;bottom: 0;width: 100%;">
            <div class="select-area-button" onclick="area_show()" style="border-top:solid 0.5px #8c8c8c;background-color: #FFF;">
                取消
            </div>
            <div class="select-area-button" onclick="$('#form1').submit();" style="color:#FFF;background-color: #02879B;">
                确定
            </div>
        </div>
    </div>
LL;

        return $html;
    }


    //输出已筛选区域html
    static function select_area($GET){
        if (!empty($GET["start_time"]) && !empty($GET["end_time"])){
            $GET["date"] = str_replace("-",".",$GET["start_time"])
                ." - ".str_replace("-",".",$GET["start_time"]);
        }
        $html = "";
        if(!empty($GET)){
            foreach($GET as $key=>$value) {
                if ($key != "p" && $key != "q" && !empty($value)) {
                    switch ($key) {
                        case "file":
                            unset($value);
                            break;
                        case "title":
                            $value = "关于：" . $value;
                            break;
                        case "city":
                            break;
                        case "type":
                            break;
                        case "date":
                            break;
                        case "times":
                            $value = $value . "次";
                            break;
                        case "price":
                            $value = $value . "元";
                            break;
                        case "oid":
                            $value = "订单号：" . $value;
                            break;
                        case "order_state":
                            switch ($value){
                                case -1:
                                    $value = "待支付";
                                    break;
                                case 1:
                                    $value = "已支付";
                                    break;
                                case 2:
                                    $value = "已取消";
                                    break;
                                case 3:
                                    $value = "已退票";
                                    break;
                                case 4:
                                    $value = "已取消";
                                    break;
                                default:
                                    unset($value);
                                    break;
                            }
                            break;
                        case "ticket_state":
                            switch ($value){
                                case 1:
                                    $value = "有效";
                                    break;
                                case 2:
                                    $value = "已使用";
                                    break;
                                case 3:
                                    $value = "已退票";
                                    break;
                                case 5:
                                    $value = "已过期";
                                    break;
                                default:
                                    unset($value);
                                    break;
                            }
                            break;

                        case "visibility":
                            $value = ($value==2)?"已删除订单":"其他";
                            break;
                        case "payment":
                            switch ($value){
                                case "AliPay":
                                    $value = "支付宝";
                                    break;
                                case "WeChat":
                                    $value = "微信支付";
                                    break;
                                case "balance":
                                    $value = "余额支付";
                                    break;
                                default:
                                    unset($value);
                                    break;
                            }
                            break;
                        case "trade_type":
                            switch ($value){
                                case 1:
                                    $value = "订单支付";
                                    break;
                                case 2:
                                    $value = "订单退款";
                                    break;
                                case 3:
                                     $value = "余额充值";
                                    break;
                                default:
                                    unset($value);
                                    break;
                            }
                            break;
                        default:
                            unset($value);
                            break;
                    }
                    $url = self::del_select($key,$GET);
                    $html.= !isset($value)?"": <<<LL
                            <button style="float: left;margin: 5px 5px 0 0" type="button" class="btn btn-default btn-sm" onclick="window.location.href='$url'">$value ×</button>
LL;
                }
            }
        }

        if(!empty($html)){
            $html = <<<LL
            <div style="padding: 0 12px">
                $html
                <button style="float: left;margin: 5px 5px 0 0" type="button" class="btn btn-default btn-sm" onclick="go('?file={$GET["file"]}')"> 清除所有 </button>
                <div style="font: 0/0 sans-serif;clear: both;display: block"> </div>
            </div>
LL;
        }
        return $html;
    }

    //获取select 1-99
    static function getOpt($num, $total)
    {
        if ($num < 0 || $total < 0) {
            return "<option>错误的参数，数量不可小于0</option>";
        }
        $options = "";
        for ($i = 1; $i <= $total; $i++) {
            //$sel = ($i == $num) ? "selected" : "";
            $options .= ($i == $num)
                ? "<option selected>$i</option>\n"
                : "<option>$i</option>\n";
        }
        return $options;
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

    //获取各种表中的数量
    static function getNum(){
        $uid = UID;
        $sql = <<<LL
        select 'myorders' as tb_name,count(*) as num from a_orders where uid = '$uid'
union 
select 'history' as tb_name,count(*) as num from a_history where uid = '$uid'
union 
select 'mytickets' as tb_name,count(*) as num from a_mytickets where uid = '$uid' and father_KEY is null and state > 0
union 
select 'favor' as tb_name,count(*) as num from a_favor where uid = '$uid'
union 
select 'mycoupon' as tb_name,count(*) as num from a_mycoupon where uid = '$uid'
union 
select 'traderec' as tb_name,count(*) as num from a_trade_rec where uid = '$uid'
LL;
        $data = Mysql::query($sql,1);
        $result = array(
            "myorders"=>0,
            "history"=>0,
            "mytickets"=>0,
            "favor"=>0,
            "mycoupon"=>0,
            "traderec"=>0
        );
        foreach ($data as $row){
            $result[$row["tb_name"]] = $row["num"];
        }
        return $result;
    }



}