<?php

class MobileSystem
{
    public $file_name,$GET,$POST,$page_name,$html_title,$padding,$show_menu;
    //构建函数，生成必要数据
    function __construct($GET,$POST)
    {
        $this->file_name = empty($GET["file"])?"index":$GET["file"];
        $this->GET = $GET;$this->POST = $POST;
        //根据请求的文件名查找页面名称
        $sql = "select * from sys_files_mobile where file_name = '$this->file_name'";
        if($data = Mysql::query($sql,1)){
            $this->page_name = $data[0]["page_name"];
            $this->show_menu = $data[0]["show_menu"];
        }else{
            $this->page_name = "404页面";
            $this->show_menu = 1;
        }

        if (!empty($GET['title'])){
            $arr = array();
            if(!empty($_COOKIE['search_history'])){
                $arr = unserialize($_COOKIE['search_history']);
            }
            if(!in_array($GET['title'],$arr)){
                $arr[] = $GET['title'];
                $arr = serialize($arr);
                setcookie('search_history',$arr);
            }
        }

        //生成页面标题
        $this->html_title = $this->page_name.TITLE_SUFFIX;
        //输出页面
        echo $this->getHeadHtml();//html头部
        echo $this->getTopHtml();//页面顶部栏
        echo $this->getBodyMainHtml();//页面主体div
        if ($this->show_menu == "1"){
            echo $this->getMenuHtml();//输出底部菜单
        }
        echo $this->getRightMenuHtml();//输出右边栏菜单
        echo $this->getScriptHtml();//输出JavaScript和页面底部
    }


    //输出html头部的函数
    private function getHeadHtml()
    {
        $head_html = <<<LL
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <title>{$this->html_title}</title>
    <link rel="stylesheet" href="css/swiper.min.css"/>
    <link rel="stylesheet" href="css/base.css"/>
    <link rel="stylesheet" href="css/mobile.css"/>
    <script src="js/jquery.min.js"></script>
    <script src="js/swiper.min.js"></script>
    <script src="js/qrcode.min.js"></script>
    <script src="js/highcharts.js"></script>
    <script src="js/base.js"></script>
    <script src="js/mobile.js"></script>
</head>
<body>
<div class="hidden-area">
    <div class="cover-10"></div>
    <div class="cover-90"></div>
    <div class="body-alert">
        <div class="body-alert-img"><img src="image/yes.png"></div>
        <div class="body-alert-text">alert-area</div>
    </div>
    <div class="body-ask">
        <div class="body-ask-img"><img src="image/warn.png"></div>
        <div class="body-ask-text">ask area</div>
        <div class="body-ask-true" onclick="alert(true)">确定</div>
        <div class="body-ask-false" onclick="closeAsk()">取消</div>
    </div>
</div>
LL;
        return $head_html;
    }
    //输出页面顶部栏的函数
    private function getTopHtml()
    {
        switch ($this->file_name){
            case "index":
                $top_html = <<<LL
        
<div class="body-top">
    <input type="text" id="search" onfocus="area_show('search-area')" onblur="area_show()" maxlength="20" class="search" placeholder="输入你要查找的内容"/>
    <div class="search-touch" onclick="search();">搜索</div>
</div>

LL;
                break;
            case "tickets":
                $top_html = <<<LL
        
<div class="body-top">
    <div class="return" onclick="history.back(-1);"></div>
    $this->page_name
    <div class="search-touch" style="float: right" onclick="go('?file=category')">筛选</div>
</div>
LL;
                break;
            case "cart":
                $top_html = <<<LL
        
<div class="body-top">
    <div class="return" onclick="history.back(-1);"></div>
    $this->page_name
    <div class="search-touch" style="float: right" onclick="ask('是否确认清空购物车？','changeGoodsByNum(0,0)')">清空</div>
</div>
LL;
                break;
            case "me":
                $top_html = <<<LL
        
<div class="body-top">
    <div class="return" onclick="history.back(-1);"></div>
    $this->page_name
    <div class="search-touch" style="float: right;" onclick="go('?file=setting')">
        <img src="image/setting.png" style="height: 20px">
    </div>
</div>
LL;
                break;
            case "myOrders":
                $top_html = <<<LL
                
<div class="body-top">
    <div class="return" onclick="history.back(-1);"></div>
    $this->page_name
    <div class="search-touch" style="float: right" onclick="area_show('select-area');">筛选</div>
</div>
LL;
                break;
            case "myTickets":
                $top_html = <<<LL
                
<div class="body-top">
    <div class="return" onclick="history.back(-1);"></div>
    $this->page_name
    <div class="search-touch" style="float: right" onclick="area_show('select-area');">筛选</div>
</div>
LL;
                break;
            case "tradeRec":
                $top_html = <<<LL
                
<div class="body-top">
    <div class="return" onclick="history.back(-1);"></div>
    $this->page_name
    <div class="search-touch" style="float: right" onclick="area_show('select-area');">筛选</div>
</div>
LL;
                break;
            case "ticketDetail":
                $ticket_key = empty($this->GET["ticket_key"])?"":$this->GET["ticket_key"];
                $next_ticket_key = myTickets::getNextTicketKey($ticket_key);
                $next_url = empty($next_ticket_key)?"":"go('?file=ticketDetail&ticket_key={$next_ticket_key}')";
                $top_html = <<<LL
                
<div class="body-top">
    <div class="return" onclick="history.back(-1);"></div>
    $this->page_name
    <div class="search-touch" style="float: right;" onclick="$next_url">下一张</div>
</div>
LL;
                break;
            case "history":
                $top_html = <<<LL
        
<div class="body-top">
    <div class="return" onclick="history.back(-1);"></div>
    $this->page_name
    <div class="search-touch" style="float: right" onclick="ask('确认清空浏览记录？','delAllHis();');">清空</div>
</div>
LL;
                break;
            default:
                $top_html = <<<LL
                
<div class="body-top">
    <div class="return" onclick="history.back(-1);"></div>
    $this->page_name
</div>
LL;
                break;
        }
        return $top_html;
    }

    //输出页面主体div(.body-main)
    private function getBodyMainHtml()
    {
        $p = empty($this->GET['p']) ? 1 : $this->GET['p'];

        switch ($this->file_name) {
            case "index":
                $note_html = "";
                if(!empty($_COOKIE["search_history"])){
                    $search_history = unserialize($_COOKIE["search_history"]);
                    krsort($search_history);
                    foreach ($search_history as $value){
                        $note_html.=<<<LL
                        <a href="?file=tickets&title=$value" style="padding:0 10px;font-size:18px;color:#FFF;background-color: #3B707B;float: left;width: auto;margin: 5px 0 5px 5px;border-radius: 5px">$value</a>
LL;
                    }
                }
                $pic_group = [
                    ["title"=>"标题1","img"=>"pictures/scenic/P201805090001/index.jpg","url"=>"www.baidu.com"],
                    ["img"=>"pictures/scenic/P201805090002/index.jpg"]
                ];
                $swiper_html = Template::getSwiper($pic_group);
                $html = <<<LL

<div class="body-main" style="">
    <div class="search-area" hidden>
        $note_html
        <div style="font: 0/0 sans-serif;clear: both;display: block"> </div>
    </div>
    <div class="card-list"> 
        $swiper_html    
        <div class="main-title" style="border:none;font-size: 24px;text-align: center;color: #02879B;line-height: 36px">我的消费统计</div>
        <div id="container1" class="chart" style="height: 300px;padding: 0 10px;margin-top: 10px"></div>
        <div id="container2" class="chart" style="height: 300px;padding: 0 10px;margin-top: 10px"></div>
        <div id="container3" class="chart" style="height: 300px;padding: 0 10px;margin-top: 10px"></div>
    </div>
</div>
LL;
                break;
            case "category":
                $category_html = MobileActions::getCategoryHtml();
                $note_html = "";
                if(!empty($_COOKIE["search_history"])){
                    $search_history = unserialize($_COOKIE["search_history"]);
                    krsort($search_history);
                    foreach ($search_history as $value){
                        $note_html.=<<<LL
                        <a href="?file=tickets&title=$value" style="padding:0 10px;font-size:18px;color:#363636;background-color: #FFF;float: left;width: auto;margin: 5px 0 5px 5px;border-radius: 5px">$value</a>
LL;
                    }
                }
                $html = <<<LL

<div class="body-main">
    <div class="search-area" style="margin-top: 60px" hidden>
        $note_html
        <div style="font: 0/0 sans-serif;clear: both;display: block"> </div>
    </div>
    <div class="card-list" style="padding-bottom: 70px">
        $category_html
    </div>
    
</div>
LL;
                break;
            case "tickets":
                $term_html = MobileActions::getTermHtml($this->file_name);
                $selected_html = MobileActions::select_area($this->GET);
                $note_html = "";
                if(!empty($_COOKIE["search_history"])){
                    $search_history = unserialize($_COOKIE["search_history"]);
                    krsort($search_history);
                    foreach ($search_history as $value){
                        $note_html.=<<<LL
                        <a href="?file=tickets&title=$value" style="padding:0 10px;font-size:18px;color:#FFF;background-color: #3B707B;float: left;width: auto;margin: 5px 0 5px 5px;border-radius: 5px">$value</a>
LL;
                    }
                }
                $tickets_data = Tickets::getTicketsInfo($this->GET);
                $tickets_html = MobileTemplate::getTemp("tickets",$tickets_data);
                //$tickets_html = MobileTemplate::addTailHtml($tickets_html,"找不到对应的票券！");
                $tickets_html .= MobileActions::getPageHtml($p, Tickets::$tickets_page, "", $this->GET);

                $html = <<<LL

<div class="body-main">
    $term_html
    <div class="search-area" hidden>
        $note_html
        <div style="font: 0/0 sans-serif;clear: both;display: block"> </div>
    </div>
    <div class="card-list" style="padding-bottom: 150px">
        $selected_html
        $tickets_html
    </div>
    
</div>
LL;
                break;
            case "find":
                $find_data = Scenic::getScenicList();
                $find_html = MobileTemplate::getTemp("find",$find_data,1,"没有发现景点...");

                $html = <<<LL

<div class="body-main">
    <div class="card-list">
        $find_html
    </div>
</div>
LL;
                break;
            case "cart"://购物车界面
                $cart_html = MobileCart::getCartInfo();
                $html = <<<LL
<div class="body-main">
    $cart_html
</div>
LL;
                break;
            case "me"://"我的"界面
                $num_arr = MobileActions::getNum();
                $html = <<<LL
                
<div class="body-main">
    <div class="card-list">
        <div class="me-info">
            <img src="image/a7n.png"/><br/>
            用户昵称：{$_SESSION['customer']['nickname']}<br/>
            账户余额：￥ {$_SESSION['customer']['money']} 元<br/>
            上次登录：{$_SESSION['customer']['last_login']}
        </div>
        <div class="me-box-area">
            <div class="me-box" onclick="go('?file=myOrders')">
                <img src="image/order.png"/><br/>我的订单($num_arr[myorders])
            </div>
            <div class="me-box" onclick="go('?file=myTickets')">
                <img src="image/myticket.png"/><br/>我的票券($num_arr[mytickets])
            </div>
            <div class="me-box" onclick="go('?file=tradeRec')">
                <img src="image/trade.png"/><br/>交易记录($num_arr[traderec])
            </div>
            <div class="me-box" onclick="go('?file=history')">
                <img src="image/history.png"/><br/>我的足迹($num_arr[history])
            </div>
            <div class="me-box" onclick="go('?file=favor')">
                <img src="image/favor.png"/><br/>我的收藏($num_arr[favor])
            </div>
            <div class="me-box" onclick="go('?file=coupon')">
                <img src="image/coupon.png"/><br/>优惠券($num_arr[mycoupon])
            </div>
            <div class="me-box" onclick="go('?file=')">
                <img src="image/chart.png"/><br/>消费统计
            </div>
            <div class="me-box" onclick="sys.logout('customer')">
                <img src="image/logout.png"/><br/>退出登录
            </div>
        </div>
    </div>
</div>
LL;
                break;

            case "setting"://"我的"界面
                $html = <<<LL
<div class="body-main">
    <br/>
    昵称：<input type="text" style=""/><br/>
    手机号：<input type="text" style=""/><br/>
    修改密码：<input type="text" style=""/><br/>

</div>
LL;
                break;

            case "myOrders":
                $term_html = MobileActions::getTermHtml($this->file_name);
                $selected_html = MobileActions::select_area($this->GET);
                $orders_data = myOrders::getMyOrdersInfo($this->GET);
                $orders_html = MobileTemplate::getTemp("myOrders",$orders_data,$p,"没有此类订单...");
                $orders_html .= MobileActions::getPageHtml($p, MyOrders::$my_orders_page, "", $this->GET);

                $html = <<<LL
                
<div class="body-main">
    $term_html
    <div class="card-list">
        $selected_html
        $orders_html
    </div>
</div>
LL;
                break;
            case "myTickets":
                $term_html = MobileActions::getTermHtml($this->file_name);
                $selected_html = MobileActions::select_area($this->GET);
                $myTickets_data = myTickets::getMyTicketsInfo($this->GET);
                $myTickets_html = MobileTemplate::getTemp("myTickets",$myTickets_data,$p,"没有此类票券...");
                $myTickets_html .= MobileActions::getPageHtml($p, MyTickets::$my_tickets_page, "", $this->GET);

                $html = <<<LL
<div class="body-main">
    $term_html
    <div class="card-list">
        $selected_html
        $myTickets_html
    </div>
</div>
LL;
                break;
            case "tradeRec"://交易记录
                $tradeRec_data = TradeRec::getTradeRec($this->GET);
                $term_html = MobileActions::getTermHtml($this->file_name);
                $selected_html = MobileActions::select_area($this->GET);
                $tradeRec_html = MobileTemplate::getTemp("tradeRec",$tradeRec_data,$p,"没有此类交易记录...");
                $tradeRec_html .= MobileActions::getPageHtml($p, TradeRec::$trade_rec_page, "", $this->GET);

                $html = <<<LL
                
<div class="body-main">
    $term_html
    
    <div class="card-list">
        $selected_html
        $tradeRec_html
    </div>
</div>
LL;
                break;

            case "detail":
                History::addHistory($this->GET);
                $Detail_html = Ticket::getDetail($this->GET);
                $html = <<<LL
<div class="body-main">
    $Detail_html
</div>
LL;
                break;
            case "scenic":
                $Detail_html = Scenic::getScenic($this->GET);
                $html = <<<LL
<div class="body-main">
    $Detail_html
</div>
LL;
                break;
            case "business":
                $Detail_html = Business::getBusiness($this->GET);
                $html = <<<LL
<div class="body-main">
    $Detail_html
</div>
LL;
                break;
            case "orderDetail":
                $detail_data = MyOrders::getOrderDetail($this->GET);
                $detail_html = MobileTemplate::getTemp("orderDetail",$detail_data,$p);

                $html = <<<LL
<div class="body-main">
    $detail_html
</div>
LL;
                break;
            case "ticketDetail":
                $detail_data = MyTickets::getTicketDetail($this->GET);
                $detail_html = MobileTemplate::getTemp("ticDetail",$detail_data);
                $html = <<<LL
<div class="body-main">
    <div class="card-list">
    $detail_html
    </div>
</div>
LL;
                break;
            case "createOrder":
                $msg_txt = "";
                if (empty($this->GET["tid"])){
                    if (!Cart::getCartNum(1)) $msg_txt .= "请先选中要结算的商品！";
                    if (!Cart::checkCartStock(1)) $msg_txt .= "商品库存不足！";
                    $new_oid = Cashier::createOrderByCart('mobile');
                }else{
                    if (empty($this->GET["num"])){
                        $num = 1;
                    }else{
                        $num = (int)$this->GET["num"];
                    }
                    $num = (is_int($num)&&$num>0)?$num:1;
                    $cart_arr = [$this->GET["tid"]=>$num];
                    $json = json_encode($cart_arr);
                    if (!Cart::getCartNumByJson($json)) $msg_txt .= "请先选中要结算的商品！";
                    if (!Cart::checkStockByJson($json)) $msg_txt .= "商品库存不足！";
                    $new_oid = Cashier::createOrderByJson($json);
                }

                if ($new_oid){
                    $url = "?file=cashier&oid=$new_oid";
                    $msg = "msg('订单提交成功！订单号为：$new_oid ，即将进入支付页面！')";
                }else{
                    $url = "?file=tickets";
                    $msg = "warn('订单生成失败：$msg_txt')";
                }
                $html = <<<LL
        <div class="body-list">
            <script>
                $msg;
                setTimeout(function() {
                    go('$url')
                },1500)
            </script>
        </div>
LL;
                break;
            case "cashier"://支付页面
                $cashier_data = Cashier::getCashier($this->GET);
                if ($cashier_data){
                    $cashier_html = MobileTemplate::getTemp("cashier",$cashier_data);
                }else{
                    $cashier_html = <<<LL
            <script>
                warn("订单号不存在！");
                setTimeout(function() {
                    go("?file=myOrders")
                },1500)
            </script>
LL;
                }
                $html = <<<LL
                
<div class="body-main">
    $cashier_html
</div>
LL;
                break;
            case "buy"://快速购买功能
                $buy_data = Cashier::getBuy($this->GET);
                if ($buy_data){
                    $buy_html = MobileTemplate::getTemp("buy",$buy_data);
                }else {
                    $buy_html = <<<LL
            <script>
                warn("无法购买，票券不存在！");
                setTimeout(function() {
                    history.back(-1);
                },1200)
            </script>
LL;
                }
                $html = <<<LL
<div class="body-main">
    $buy_html
</div>
LL;
                break;
            case "confirm"://通过购物车确认订单
                $confirm_data = Cashier::getConfirmByCart();
                if (!empty($confirm_data)) {
                    $confirm_html = MobileTemplate::getTemp("confirm",$confirm_data);
                }else{
                    $confirm_html = <<<LL
            <script>
                warn("请先选择商品！");
                setTimeout(function() {
                    history.back(-1);
                },1200)
            </script>
LL;
                }
                $html = <<<LL
<div class="body-main">
    $confirm_html
</div>
LL;
                break;
            case "refund"://退单界面
                $refund_data = Cashier::refund($this->GET);
                if ($refund_data){
                    $refund_html = MobileTemplate::getTemp("refund",$refund_data);
                }else{
                    $refund_html = <<<LL
            <script>
                warn("此订单号不满足退票条件！");
                setTimeout(function() {
                    go("?file=myOrders")
                },1500)
            </script>
LL;
                }
                $html = <<<LL
                
<div class="body-main">
    $refund_html
</div>
LL;
                break;
            case "coupon":
                $coupon_data = Coupon::getCouponList();
                $coupon_html = Template::getTemp("coupon",$coupon_data,"优惠券空空如也...");
                $html = <<<LL
                
<div class="body-main">
    <div class="card-list">
        $coupon_html
    </div>
</div>
LL;
                break;
            case "favor":
                $favor_data = Favor::getFavorList();
                $favor_html = MobileTemplate::getTemp("tickets",$favor_data,"没有收藏夹...");
                $favor_html = MobileTemplate::addTailHtml($favor_html,"没有收藏夹...");
                $html = <<<LL
                
<div class="body-main">
    <div class="card-list">
        $favor_html
    </div>
</div>
LL;
                break;
            case "history":
                $history_data = History::getHistoryList();
                $history_html = MobileTemplate::getTemp('Ticket',$history_data);
                $html = <<<LL
                
<div class="body-main">
    <div class="card-list">
        $history_html
    </div>
</div>
LL;
                break;
            default:
                $html = <<<LL
                
<div class="body-main">
    <div style="text-align: center;color: #3B707B;font-size: 24px;position: absolute;top: 30%;width: 100%"> 
        <a style="font-size: 64px">:(</a></br>页面走丢了...
    </div>
</div>
LL;
                break;
        }
        return $html;
    }

    //输出底部菜单栏menu
    private function getMenuHtml()
    {
        $displsy = "";
        $imgs = array("",
            "n-home.png",
            "n-menu.png",
            "n-find.png",
            "n-cart.png",
            "n-me.png"
        );
        //根据当前页面设置选中菜单图标
        switch ($this->file_name){
            case "index":
                $imgs[1] = "a-home.png";
                break;
            case "tickets":
                $imgs[2] = "a-menu.png";
                break;
            case "category":
                $imgs[2] = "a-menu.png";
                break;
            case "find":
                $imgs[3] = "a-find.png";
                break;
            case "cart":
                $imgs[4] = "a-cart.png";
                break;
            case "me":
                $imgs[4] = "a-cart.png";
                break;
            case "myOrders":
                $imgs[4] = "a-cart.png";
                break;
            case "myTickets":
                $imgs[4] = "a-cart.png";
                break;
            case "tradeRec":
                $imgs[4] = "a-cart.png";
                break;
            default:
                break;
        }
        $html = <<<LL
        
<div class="body-menu">
    <div class="menu-box" onclick="go('?file=index')">
        <img src="image/$imgs[1]"/>
    </div>
    <div class="menu-box" onclick="go('?file=tickets')">
        <img src="image/$imgs[2]"/>
    </div>
    <div class="menu-box" onclick="go('?file=find')">
        <img src="image/$imgs[3]"/>
    </div>
    <div class="menu-box" onclick="go('?file=me')">
        <img src="image/$imgs[4]"/>
    </div>

</div>
LL;
        return $html;
    }

    //侧边栏菜单输出
    private function getRightMenuHtml()
    {
        $cart_num = Cart::getCartNum();
        $cart_num = ($cart_num>99)?"99+":$cart_num;

        switch ($this->file_name){
            case "tickets":
                $html = <<<LL
            
    <div class="right-menu">
        <div id="show-cart" class="button-ball" onclick="go('?file=cart')">
            <img src="image/cart-ball.png"/>
            <div id="cart-num">$cart_num</div>
        </div>
        <div id="foot" onclick="go('?file=history')" class="button-ball" >
            <img src="image/foot.png"/>
        </div>
        <div id="to-top" onclick="to_top();" class="button-ball" >
            <img src="image/to-top.png"/>
        </div>
    </div>
LL;
                break;
            case "cart":
                $html = <<<LL
            
    <div class="right-menu" style="bottom: 100px">
        <div id="show-cart" class="button-ball" onclick="go('?file=cart')">
            <img src="image/cart-ball.png"/>
            <div id="cart-num">$cart_num</div>
        </div>
        <div id="to-top" onclick="to_top();" class="button-ball" >
            <img src="image/to-top.png"/>
        </div>
    </div>
LL;
                break;
                default:
                    $html = <<<LL
            
    <div class="right-menu">
        <div id="show-cart" class="button-ball" onclick="go('?file=cart')">
            <img src="image/cart-ball.png"/>
            <div id="cart-num">$cart_num</div>
        </div>
        <div id="to-top" onclick="to_top();" class="button-ball" >
            <img src="image/to-top.png"/>
        </div>
    </div>
LL;
                break;
        }
        return $html;
    }

    //输出JavaScript和html结尾
    private function getScriptHtml()
    {
        $uid = UID;
        switch ($this->file_name) {
            case "index":
                $data1 = array();
                $data2 = array();
                $data3 = array();

                for ($i=30;$i>=1;$i--){
                    $j = $i-1;
                    $sql = "select sum(money) from a_trade_rec where uid = '$uid' and type = '订单支付'  and create_time between date_add(now(), interval -$i day) and date_add(now(), interval -$j day)";
                    if ($data = Mysql::query($sql,1)) {
                        $data1[] = empty($data[0]["sum(money)"]) ? 0 : (int)$data[0]["sum(money)"];
                    }
                }

                for ($i=30;$i>=1;$i--){
                    $j = $i-1;
                    $sql = "select count(*) from a_mytickets where state > 0 and uid = '$uid' and create_time between date_add(now(), interval -$i day) and date_add(now(), interval -$j day)";
                    if ($data = Mysql::query($sql,1)) {
                        $data2[] = empty($data[0]["count(*)"]) ? 0 : (int)$data[0]["count(*)"];
                    }
                }

                $term = array("", " between 0 and 200", " between 200 and 500", "  between 500 and 1000", "  between 1000 and 1000000");
                for ($i = 1; $i <= 4; $i++) {
                    $sql = "select count(*) from a_mytickets a,a_tickets b where a.state > 0 and a.uid = '$uid' and a.create_time > date_add(now(), interval -1 month) and a.tid = b.tid and b.price";
                    $sql .= $term[$i];
                    if ($data = Mysql::query($sql,1)) {
                        $data3[$i] = empty($data[0]["count(*)"]) ? 0 : $data[0]["count(*)"];
                    }
                }

                $trade_data = array("name"=>"时间->最近","data"=>$data1);
                $trade_json = json_encode($trade_data);
                $tic_num_data = array("name"=>"时间->最近","data"=>$data2);
                $tic_num_json = json_encode($tic_num_data);

                $html = <<<LL
<script type="text/javascript">

$(function () {
    
    $('#container1').highcharts({
        chart: {
            type: 'area'
        },
        credits:{
        	enabled:false
        },
        title: {
            text: '最近30天消费趋势'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            allowDecimals: false,
            labels: {
                formatter: function () {
                    return this.value; // clean, unformatted number for year
                }
            }
        },
        yAxis: {
            title: {
                text: '金额'
            },
            labels: {
                formatter: function () {
                    return this.value;
                }
            }
        },
        tooltip: {
            pointFormat: '{series.name} 消费 <b>{point.y:,.0f}</b>元'
        },
        plotOptions: {
            area: {
                pointStart: 1,
                marker: {
                    enabled: false,
                    symbol: 'circle',
                    radius: 2,
                    states: {
                        hover: {
                            enabled: true
                        }
                    }
                }
            }
        },
        series: [$trade_json]
    });
    $('#container2').highcharts({
        chart: {
            type: 'area'
        },
        credits:{
        	enabled:false
        },
        title: {
            text: '最近30天购票趋势'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            allowDecimals: false,
            labels: {
                formatter: function () {
                    return this.value; // clean, unformatted number for year
                }
            }
        },
        yAxis: {
            title: {
                text: '张数'
            },
            labels: {
                formatter: function () {
                    return this.value;
                }
            }
        },
        tooltip: {
            pointFormat: '{series.name} 购票 <b>{point.y:,.0f}</b>张'
        },
        plotOptions: {
            area: {
                pointStart: 1,
                marker: {
                    enabled: false,
                    symbol: 'circle',
                    radius: 2,
                    states: {
                        hover: {
                            enabled: true
                        }
                    }
                }
            }
        },
        series: [$tic_num_json]
    });
    $('#container3').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        credits:{
        	enabled:false
        },
        title: {
            text: '最近30天购票单价'
        },
        tooltip: {
            headerFormat: '{series.name}<br>',
            pointFormat: '{point.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: '单价',
            data: [
                ['0-200元',   $data3[1]],
                ['200-500元',  $data3[2]],
                ['500-1000元',  $data3[3]],
                ['1000元以上',  $data3[4]]
            ]
        }]
    });
    
});

</script>
</body>
</html>
LL;

                break;

            case "detail":

                $html = <<<LL
<script>
if(window.lefttime){
    setInterval(function () {
    var nowtime = new Date();
    var time = lefttime - nowtime;
    var day = parseInt(time / 1000 / 60 / 60 / 24);
    var hour = parseInt(time / 1000 / 60 / 60 % 24);
    var minute = parseInt(time / 1000 / 60 % 60);
    var seconds = parseInt(time / 1000 % 60);
    $('.timespan').html(day + "天" + hour + "时" + minute + "分" + seconds + "秒");
  }, 1000);
}

</script>
</body>
</html>
LL;
                break;
            case "ticketDetail":
                $uid = UID;
                $ticket_key = empty($this->GET["ticket_key"])?"":$this->GET["ticket_key"];
                $sql = "select state,rnd_key from a_mytickets where uid = '$uid' and ticket_KEY = '$ticket_key'";
                $data = Mysql::query($sql,1);
                $rnd_key = empty($data[0]["rnd_key"])?"":$data[0]["rnd_key"];
                if (!empty($data) && $data[0]["state"] == 1){
                    $js_html = "setInterval(updateTicket,5000);";
                }else{
                    $js_html = "";
                }
                $html = <<<LL
                
    <script>
        var qrcode = new QRCode("qrcode");
        $(document).ready(function(){
            qrcode.makeCode('$ticket_key'+'$rnd_key');
            $js_html

            function updateTicket() {
                $.post("app/customer/controller/Ajax.php",
                {
                    "ticket_key" : '$ticket_key',
                    "action_key" : "chkTic"
                },
                function(result){
                    if (result == "none"){
                            warn('验票成功<br/>此类型的票券已经是最后一张！');
                            setTimeout(function(){
                                window.location.reload();
                            },2000);
                        }else if(result){
                            msg('验票成功<br/>自动跳转到下一张');
                            setTimeout(function(){
                                go("?file=ticketDetail&ticket_key=" + result);
                            },2000);
                        }
                });
            }
        })
    </script>
</body>
</html>
LL;
                break;
            default:
                $html = <<<LL
</body>
</html>
LL;
                break;
        }

        return $html;
    }

}