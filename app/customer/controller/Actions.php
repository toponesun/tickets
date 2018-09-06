<?php

class Actions
{
    //json转化为数据库查询条件
    static function jsonToTerm($json){
        $arr = json_decode($json,1);
        $term = "";
        if (is_array($arr)){
            foreach ($arr as $value){
                $term.= empty($term)?"'$value'":",'$value'";
            }
        }
        $term = "($term)";
        return $term;
    }

    static function getTermHtml($GET,$FILE_NAME){
        $html = <<<LL
               <div class='form-inline' style="width:90%;height: auto;float: left;">
                    <div class="input-group" style="margin-right: 5px;width: 245px;float: left;">
                        <input id="daterange" type="text" class="form-control" placeholder="请选择开始和结束日期"/>
                        <span id="date_button" class="input-group-addon" style="cursor: pointer">确定</span>
                    </div>
                    <div class="input-group" style="margin-right: 5px;width: 220px;float: left;">
                        <input type="text" id="search-text" class="form-control" placeholder="输入要查找的关键字"/>
                        <span onclick="search('$FILE_NAME')" class="input-group-addon" style="cursor: pointer">搜索</span>
                    </div>
LL;

        switch ($FILE_NAME){
            case "tickets":
                $terms = array("type"=>array("title"=>"票券种类","1"=>"成人票","4"=>"儿童票","3"=>"老年票","2"=>"学生票"),
                    "times"=>array("title"=>"有效次数","0-1"=>"1次","2-10"=>"2-10次","10-1000"=>"10次以上"),
                    "city"=>array("title"=>"选择城市","深圳"=>"深圳","广州"=>"广州"),
                    "price"=>array("title"=>"价格区间","0-200"=>"200元以下","200-500"=>"200-500元","500-1000"=>"500-1000元")
                );

                $sys_terms = array("city"=>"城市");
                foreach ($sys_terms as $key=>$value){
                    $sql = "select $key from a_tickets group by $key";
                    $data = Mysql::query($sql,1);
                    $terms[$key] = array("title"=>$value);
                    foreach ($data as $term){
                        $terms[$key][$term[$key]] = $term[$key];
                    }
                }
                break;
            case "myTickets":
                $terms = array("ticket_state"=>array("title"=>"票券状态","1"=>"有效","2"=>"已使用","3"=>"已退票"),
                    "times"=>array("title"=>"有效次数","0-1"=>"1次","2-10"=>"2-10次","10-1000"=>"10次以上"),
                    "city"=>array("title"=>"选择城市","深圳"=>"深圳","广州"=>"广州"),
                    "price"=>array("title"=>"价格区间","0-200"=>"200元以下","200-500"=>"200-500元","500-1000"=>"500-1000元")
                );
                $sys_terms = array("type"=>"票券种类","city"=>"城市");
                foreach ($sys_terms as $key=>$value){
                    $sql = "select b.$key from a_mytickets a,a_tickets b where a.tid = b.tid group by b.$key";
                    $data = Mysql::query($sql,1);
                    $terms[$key] = array("title"=>$value);
                    foreach ($data as $row){
                        $terms[$key][$row[$key]] = $row[$key];
                    }
                }
                break;
            case "myOrders":
                $terms = array("order_state"=>array("title"=>"订单状态","-1"=>"待支付","1"=>"已支付","2"=>"已取消","3"=>"已退票"),
                    "visibility"=>array("title"=>"回收站","2"=>"已删除订单")
                );
                break;
            case "account":
                $terms = array("account_type"=>array("title"=>"交易类型","1"=>"订单支付","2"=>"订单退款","3"=>"余额充值"),
                    "account_way"=>array("title"=>"交易方式","balance"=>"余额支付","WeChat"=>"微信支付","AliPay"=>"支付宝")
                );
                break;
            default:
                $terms = array();
                break;
        }

        foreach ($terms as $term=>$array){
            $html.= <<<LL
            
                    <div class="btn-group" style="margin-right: 5px;float: left;">
                        <button type="button" class="btn btn-default"> $array[title] </button>
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">切换下拉菜单</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
LL;
            foreach ($array as $key=>$value){
                if($key!="title"){
                    $url[$key] = self::_GET_kv($term,$key,$GET);

                    $html.= "
                            <li><a href='$url[$key]'>$value</a></li>";
                }
            }
            $base_url = self::del_select($term,$GET);
            $html.=<<<LL
            
                            <li class="divider"></li>
                            <li><a href=" $base_url ">全部</a></li>
                        </ul>
                    </div>
LL;
        }
        $html.=<<<LL
                </div>
LL;
        return $html;
    }

    static function getInventoryHtml($GET){
        if(!empty($GET["inventory"])){
            $a = "selected";
            $b = "";
        }else{
            $a = "";
            $b = "selected";
        }
        $inventory_html = <<<LL
        
                    <div style="float: left;width: 10%;">
                        <select id="inventory" class="form-control" style="float: right">
                            <option $a>只看有票</option>
                            <option $b>查看全部</option>
                        </select>
                    </div>
LL;
        return $inventory_html;
    }
    static function getStatePng($state_name){
        switch ($state_name){
            case "已使用":
                $state_png = "<img src='image/state-used.png'>";
                break;
            case "已退票":
                $state_png = "<img src='image/state-refund.png'>";
                break;
            case "已取消":
                $state_png = "<img src='image/state-cancel.png'>";
                break;
            case "已过期":
                $state_png = "<img src='image/state-over.png'>";
                break;
            case "已删除":
                $state_png = "<img src='image/state-del.png'>";
                break;
            default:
                $state_png = "";
                break;
        }
        return $state_png;
    }

    static function getPageHtml($page=1, $pages, $url,$GET){
        //最多显示多少个页码
        $_pageNum = 7;
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

        $_pageHtml = '<ul class="pagination">';

        if($page>1){
            $_pageHtml .= '<li class="page-item"><a class="page-link" title="首页" href="'.$url.self::_GET_kv("p",1,$GET).'">|<<</a></li>';
            $_pageHtml .= '<li class="page-item"><a class="page-link" title="上页" href="'.$url.self::_GET_kv("p",($page-1),$GET).'">上页</a></li>';
        }
        for ($i = $_start; $i <= $_end; $i++) {
            if($i == $page){
                $_pageHtml .= '<li class="page-item active"><a class="page-link">'.$i.'</a></li>';
            }else{
                $_pageHtml .= '<li class="page-item"><a class="page-link" href="'.$url.self::_GET_kv("p",$i,$GET).'">'.$i.'</a></li>';
            }
        }

        if($page<$_end){
            $_pageHtml .= '<li class="page-item"><a class="page-link" title="下页" href="'.$url.self::_GET_kv("p",($page+1),$GET).'">下页</a></li>';
            $_pageHtml .= '<li class="page-item"><a class="page-link" title="尾页" href="'.$url.self::_GET_kv("p",$pages,$GET).'">>>|</a></li>';
        }

        $_pageHtml .= '</ul>';


        return $_pageHtml;
    }

    static function _GET_kv($keyword,$values,$GET){
        $url = "?";
        if(!empty($GET)){
            foreach($GET as $key=>$value){
                $url.= ($key==$keyword)?"":"$key=$value&";
            }
        }
        $url.= "$keyword=$values";
        return $url;
    }

    //删除本页GET到的$keyword并返回url
    static function del_select($keyword,$GET){
        $url = ($keyword=="p")?"?":"?p=1";
        foreach($GET as $key=>$value){
            $url.= ($key==$keyword&&!empty($value))?"":"&$key=$value";
        }
        return $url;
    }
    //输出已筛选区域html
    static function select_area($GET){
        $html = "";
        if(!empty($GET)){
            foreach($GET as $key=>$value) {
                if ($key != "p" && $key != "q" &&!empty($value)) {
                    switch ($key) {
                        case "title":
                            $value = "关于：" . $value;
                            break;
                        case "file":
                            unset($value);
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
                        case "account_way":
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
                        case "account_type":
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
                            break;
                    }
                    $url = self::del_select($key,$_GET);
                    $html .= !isset($value)?"": <<<LL
                    <a class="btn btn-primary btn-xs" href="$url">$value ×</a>
LL;
                }
            }
        }

        if(!empty($html)){
            $html = <<<LL
            <div class="select-area">
                <div style="float: left;">筛选条件：</div>
                $html
                <a class="btn btn-danger btn-xs" href="?file=$GET[file]"> 清除所有 </a>
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

    //获取列表页面小图标url，返回字符串
    static function getThumb($xid_name,$xid)
    {
        if ($xid_name != "ticket" && $xid_name != "scenic"){
            return [];
        }
        $pic_dir = BASE_PATH."pictures/$xid_name/$xid/";
        $thumb_url = "pictures/$xid_name/default.jpg";
        if (@$dir = scandir($pic_dir)){
            if (file_exists($pic_dir."index.jpg")){
                $thumb_url = "pictures/$xid_name/$xid/index.jpg";
            }elseif (!empty($dir[2])){
                $thumb_url = "pictures/$xid_name/$xid/$dir[2]";
            }else{
                $thumb_url = "pictures/$xid_name/default.jpg";
            }
        }
        return $thumb_url;
    }

    //获取图片URL，返回数组
    static function getPicUrl($xid_name,$xid){
        if ($xid_name != "ticket" && $xid_name != "scenic" && $xid_name != "business"){
            return [];
        }
        $pic_dir = BASE_PATH."pictures/$xid_name/$xid/";
        $pic_group = [];
        if (@$dir = scandir($pic_dir)){
            if (file_exists($pic_dir."index.jpg")){
                $pic_group[] = "pictures/$xid_name/$xid/index.jpg";
            }
            foreach ($dir as $key => $value){
                if ($key > 1 && $value != "index.jpg"){
                    $file_type = explode("." , $value);
                    if (end($file_type) == "jpg"){
                        $pic_group[] = "pictures/$xid_name/$xid/$value";
                    }
                }
            }
        }
        if (empty($pic_group)){
            $pic_group[] = "pictures/$xid_name/default.jpg";
        }
        return $pic_group;
    }

    //随机生成KEY
    static function randKey($len){
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $KEY = "";
        $char_len = strlen($chars) - 1;
        if (!is_numeric($len)||$len <= 0)
            return false;
        for ($j = 0; $j < $len; $j++) {
            $KEY .= $chars[mt_rand(0, $char_len)];
        }
        $sql = "select ticket_KEY from a_mytickets where ticket_KEY = '$KEY'";
        if (Mysql::query($sql,1)){
            $KEY = self::randKey($len);
        }
        return $KEY;
    }

    //验票测试接口
    static function ticketValid($data){
        //返回数组初始值
        $result = ["code"=>0,"msg"=>"","data"=>[]];
        //将数组转urldecode后的json数据
        function mkRes($result){
            function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
            {
                static $recursive_counter = 0;
                if (++$recursive_counter > 1000) {
                    die('possible deep recursion attack');
                }
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        arrayRecursive($array[$key], $function, $apply_to_keys_also);
                    } else {
                        $array[$key] = $function($value);
                    }
                    if ($apply_to_keys_also && is_string($key)) {
                        $new_key = $function($key);
                        if ($new_key != $key) {
                            $array[$new_key] = $array[$key];
                            unset($array[$key]);
                        }
                    }
                }
                $recursive_counter--;
            }
            arrayRecursive($result, 'urlencode', true);
            $result = urldecode(json_encode($result));
            return $result;
        }
        if (empty($data["ticket_key"])||empty($data["device_id"])){
            $result["msg"] = "ticket_key,device_id均不可为空";
            return mkRes($result);
        }
        $ticket_key = $data["ticket_key"];
        $device_id = $data["device_id"];

        $sql_device = "select * from b_device where device_id = '$device_id'";
        $data_device = Mysql::query($sql_device,1);
        if (empty($data_device)){
            $result["msg"] = "非法闸机，不允许验票！";
            return mkRes($result);
        }
        //sql in条件，设备可验票券列表
        $device_tid_term = self::jsonToTerm($data_device[0]["device_tid_json"]);
        //设备可验票券列表数组
        $device_tid_arr = json_decode($data_device[0]["device_tid_json"]);


        //如果票券位数不超过12位则为线下票,否则按网络票处理
        //第一位1次数票，2时长票，3套票
        //第二位1子票券，0非子票券
        //后四位为随机码，必须校验随机码有效期
        if (strlen($ticket_key)<=12){
            $check_rnd_key = false;
        }else{
            $rnd_key = substr($ticket_key,-4);
            $ticket_key = substr($ticket_key,0,14);
            $check_rnd_key = true;
        }

        $sql = "select uid,tid,oid,title,tic_type,ticket_KEY,rnd_key,rnd_time,times,type,create_time,begin_time,end_time,valid_time,device_id,state from a_mytickets where ticket_KEY = '$ticket_key'";
        $data = Mysql::query($sql,1);
        if(!empty($data)){
            if ($check_rnd_key){
                if ($data[0]["rnd_key"] != $rnd_key){
                    $result["msg"] = "校验码不正确！请获取最新二维码！";
                    return mkRes($result);
                }
                if (time()-strtotime($data[0]["rnd_time"])>60){
                    $result["msg"] = "校验码已失效！请获取最新二维码！";
                    return mkRes($result);
                }
            }
            //如果券码是套票
            if ($data[0]["tic_type"] == 3){
                $sql_child = "select * from a_mytickets where father_KEY = '$ticket_key' and tid in $device_tid_term";
                $data_child = Mysql::query($sql_child,1);
                //套票中是否包含次景点可用子票券
                if (empty($data_child)){
                    $result["msg"] = "券码为套票，但是其中不包含这个景点可用的票券";
                }else{
                    $result["msg"] = "券码为套票，包含此景点可用的票，但状态都不可用";
                    foreach ($data_child as $row){
                        if ($row["state"] == 1){
                            if ($row["tic_type"] == 2){
                                $sql_up = "update a_mytickets set valid_time = now(),device_id = '$device_id' where ticket_KEY = '$row[ticket_KEY]'";
                                $result["msg"] = "放行：券码为套票，包含此景点可用的时长票，已记录使用时间";
                            }else{
                                $sql_up = "update a_mytickets set valid_time = now(),device_id = '$device_id',times = times - 1,state = (case times when 0 then 2 else 1 end)  where ticket_KEY = '$row[ticket_KEY]'";
                                $result["msg"] = "放行：券码为套票，包含此景点可用的次数票，已更新票券剩余次数和状态";
                            }
                            $result["code"] = 1;
                            break;
                        }
                    }
                }
            }else{
                if (in_array($data[0]["tid"],$device_tid_arr)){
                    switch ($data[0]["state"]){
                        case 1:
                            if ($data[0]["tic_type"] == 2){
                                $sql_up = "update a_mytickets set valid_time = now(),device_id = '$device_id',state = 4 where ticket_KEY = '{$data[0]["ticket_KEY"]}'";
                                $result["msg"] = "放行：券码为此景点可用的时长票，已记录使用时间";
                            }else{
                                $sql_up = "update a_mytickets set valid_time = now(),device_id = '$device_id',times = times - 1,state = (case times when 0 then 2 else 1 end)  where ticket_KEY = '{$data[0]["ticket_KEY"]}'";
                                $result["msg"] = "放行：券码为此景点可用的次数票，已更新票券剩余次数和状态";
                            }
                            $result["code"] = 1;
                            break;
                        case 4:
                            $result["msg"] = "此票券正在使用中，请从出口闸机扫码后再用！";
                            break;
                        default:
                            $result["msg"] = "券码状态不可用";
                            break;
                    }
                }else{
                    $result["msg"] = "该券码不可在此景点使用";
                }
            }
            if (!empty($sql_up)){
                Mysql::query($sql_up);
            }
            $result["data"] = $data[0];
        }else{
            $result["msg"] = "券码无效，数据库中未找到";
        }
        return mkRes($result);
    }

    static function isMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger');
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}

