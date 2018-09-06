<?php

class System
{
    public $file_name,$GET,$POST,$page_name,$html_title,$menu_style,$body_main_style,$show_cart;

    function __construct($GET,$POST)
    {
        $this->file_name = empty($GET["file"])?"index":$GET["file"];
        $this->GET = $GET;$this->POST = $POST;
        //根据请求的文件名查找页面名称
        $sql = "select * from sys_files where file_name = '$this->file_name'";
        $data = Mysql::query($sql,1);
        $this->page_name = empty($data[0]["page_name"])?"404页面":$data[0]["page_name"];
        //获得购物车和菜单栏显示状态
        $sql = "select show_cart,show_menu from user_customer where uid = '".UID."'";
        $data = Mysql::query($sql,1);
        $this->show_cart = empty($data[0]["show_cart"])?"":$data[0]["show_cart"];
        $body_main_style_inner = "";
        if ($this->file_name == "tickets")
            if ($this->show_cart) $body_main_style_inner = "padding-right:230px";
        if (empty($data[0]["show_menu"])){
            $this->body_main_style = "style='padding-left:50px;$body_main_style_inner'";
            $this->menu_style = "style='width:39px'";
        }else{
            $this->body_main_style = "style='$body_main_style_inner'";
        }
        //生成页面标题
        $this->html_title = $this->page_name.TITLE_SUFFIX;
        //输出页面
        echo $this->getHeadHtml();
        echo $this->getLeftHtml();
        echo $this->getMainHtml();
        echo $this->getRightHtml($this->show_cart);
        echo $this->getScriptHtml();
    }

    private function getHeadHtml()
    {
        $head_html = <<<LL
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>{$this->html_title}</title>
    <link rel="bookmark" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/swiper.min.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/main.css"/>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/swiper.min.js"></script>
    <script src="http://www.jq22.com/jquery/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="js/jquery.jqprint-0.3.js"></script>
    <script type="text/javascript" src="js/qrcode.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/laydate/laydate.js"></script>
    <script type="text/javascript" src="js/highcharts.js"></script>
    <script type="text/javascript" src="js/base.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
</head>
<body>
<div class="hidden-area">
    <div class="full-gray"></div>
    <div class="back-gray"></div>
    <div class="body-alert">
        <div class="body-alert-img"><img src="image/yes.png"></div>
        <div class="body-alert-text">alert-area</div>
    </div>
    <div class="body-ask">
        <div class="body-ask-img"><img src="image/warn.png"></div>
        <div class="body-ask-text">ask area</div>
        <div class="body-ask-true" onclick="alert()">确定</div>
        <div class="body-ask-false" onclick="closeAsk()">取消</div>
    </div>
    <div class="body-frame">
        <div class="body-frame-title"></div>
        <div class="body-frame-close" onclick="closeFrame()">×</div>
        <div class="body-frame-html">
        </div>
    </div>
</div>
<div class="body-top">                                                                                                                                                                                                                                              
    <div class="logo">
        <img src="image/logo.png" height="100%">
        <img src="image/logo2.png" height="100%">
    </div>
    <div class="user-info">
        <img src="image/user.png" height="100%">
            {$_SESSION['customer']['nickname']}
        <img src="image/wallet.png" height="100%">
            {$_SESSION['customer']['money']}
        <img src="image/logout-pc.png" height="100%">
        <span style="cursor: pointer" onclick="sys.logout('customer')">退出</span>
    </div>
</div>
<div class="welcome">
    <div class="welcome-line">
        <img src="image/balloon.png" height="100%" style="vertical-align:top"/>
        欢迎您，{$_SESSION['customer']['nickname']}。
        当前位置>>{$this->page_name}
    </div>
</div>
LL;
        return $head_html;
    }


    private function getLeftHtml()
    {
        $left_html = <<<LL
        
    <div class="body-left" $this->menu_style>
        <div class="menu-in-out" onclick="menuTgl()">
            <img src="image/menu-out.png">
        </div>
        <div class="body-menu">
            <ul>
                <li><a href="?file=index"><img src="image/a-index.png"/> 个人信息</a></li>
                <li><a href="?file=tickets"><img src="image/a-ticket.png"/> 票券商城</a></li>
                <li><a href="?file=myOrders"><img src="image/a-order.png"/> 我的订单</a></li>
                <li><a href="?file=myTickets"><img src="image/a-mytic.png"/> 我的票券</a></li>
                <li><a href="?file=tradeRec"><img src="image/a-trade.png"/> 交易记录</a></li>
            </ul>
            <script> 
                $(function() {
                    let sidebar = $("[href='?file=$this->file_name']");
                    let src_path = sidebar.children("img").attr("src");
                    let menu_width = $(".body-left").width();
                    src_path = src_path.replace("a-","n-");
                    sidebar.parent("li").addClass("active");
                    sidebar.children("img").attr("src",src_path);
                    if (menu_width > 100){
                        $(".menu-in-out img").css({'animation':'rotate180 0.3s','animation-fill-mode': 'forwards'});
                    }
                });
            </script>
        </div>
    </div>
LL;
        return $left_html;
    }

    private function getMainHtml()
    {
        $p = empty($this->GET['p']) ? 1 : $this->GET['p'];
        switch ($this->file_name) {
            case "index":
                $html = <<<LL
    <div class="body-list">
        <div class="title-lg">我的消费统计图</div>
        <div class="chart-group">
            <div class="chart-box"> 
                <div id="container1" class="chart" style="padding: 0 5px 5px 0"></div>
            </div>
            <div class="chart-box"> 
                <div id="container2" class="chart" style="padding: 0 0 5px 5px"></div>
            </div>
            <div class="chart-box"> 
                <div id="container3" class="chart" style="padding: 5px 5px 0 0"></div>
            </div>
            <div class="chart-box"> 
                <div id="container4" class="chart" style="padding: 5px 0 0 5px"></div>
            </div>
        </div>
    </div>
LL;
                break;
            case "tickets":
                //$term_html .= Actions::getInventoryHtml($this->GET);
                $select_html = Actions::select_area($this->GET);
                $tickets_data = Tickets::getTicketsInfo($this->GET);
                $tickets_html = Template::getTemp("tickets",$tickets_data,$p);
                $tickets_html .= Actions::getPageHtml($p, Tickets::$tickets_page, "", $this->GET);
                $html = <<<LL
        <div class="body-list">
            <div class="body-search">
                <form id="term-form" class="form-inline" method="get">
                    <input type="hidden" name="file" value="{$this->file_name}"/>
                    <input id="date-range" type="text" name="date-range" class="form-control mr-1 mb-1" style="width: 220px" placeholder="请选择时间段"/>
                    <input id="search-text" type="text" name="title" class="form-control mr-1 mb-1" placeholder="输入要查找的关键字"/>
                    <div class="auto-term"></div>
                    <input class="btn btn-primary form-control" type="submit" value="查找"/>
                </form>
                <script>
                    getTicketTerm();
                </script>

                $select_html
            </div>
            
            <div class="table-box">
                $tickets_html
            </div>
        </div>
LL;
                break;
            case "cart":
                $cart_html = Cart::getCartList();
                $html = <<<LL
        <div class="body-list">
            <div class="table-box">
                $cart_html
            </div>
        </div>
LL;
                break;
            case "myOrders":
                $p = empty($this->GET['p']) ? 1 : $this->GET['p'];
                $orders_data = MyOrders::getMyOrdersInfo($this->GET);
                $orders_html = Template::getTemp("myOrders",$orders_data,$p,"没有此类订单...");
                $orders_html .= Actions::getPageHtml($p, MyOrders::$my_orders_page, "", $this->GET);
                $term_html = Actions::getTermHtml($this->GET,$this->file_name);
                $select_html = Actions::select_area($this->GET);
                $html = <<<LL
        <div class="body-list">
            <div class="body-search">
                <form id="term-form" class="form-inline" method="get">
                    <input type="hidden" name="file" value="{$this->file_name}"/>
                    <input id="date-range" type="text" name="date-range" class="form-control mr-1 mb-1" style="width: 220px" placeholder="请选择时间段"/>
                    <input id="search-text" type="text" name="title" class="form-control mr-1 mb-1" placeholder="输入要查找的关键字"/>
                    <div class="auto-term"></div>
                    <input class="btn btn-primary form-control" type="submit" value="查找"/>
                </form>
                <script>
                    getOrderTerm();
                </script>
                $select_html
            </div>
            <div class="table-box">
                $orders_html
            </div>
        </div>
LL;
                break;
            case "myTickets":
                $p = empty($this->GET['p']) ? 1 : $this->GET['p'];
                $myTickets_data = MyTickets::getMyTicketsInfo($this->GET);
                $myTickets_html = Template::getTemp("myTickets",$myTickets_data,$p,"没有此类票券...");
                $myTickets_html .= Actions::getPageHtml($p, MyTickets::$my_tickets_page, "", $this->GET);
                $term_html = Actions::getTermHtml($this->GET, $this->file_name);
                $select_html = Actions::select_area($this->GET);
                $html = <<<LL
        <div class="body-list">
            <div class="body-search">
                <form id="term-form" class="form-inline" method="get">
                    <input type="hidden" name="file" value="{$this->file_name}"/>
                    <input id="date-range" type="text" name="date-range" class="form-control mr-1 mb-1" style="width: 220px" placeholder="请选择时间段"/>
                    <input id="search-text" type="text" name="title" class="form-control mr-1 mb-1" placeholder="输入要查找的关键字"/>
                    <div class="auto-term"></div>
                    <input class="btn btn-primary form-control" type="submit" value="查找"/>
                </form>
                <script>
                    getMyTicTerm();
                </script>
                $select_html
            </div>
            <div class="table-box">
                $myTickets_html
            </div>
        </div>
LL;
                break;
            case "tradeRec":
                $p = empty($this->GET['p']) ? 1 : $this->GET['p'];
                $trade_data = TradeRec::getTradeRec($this->GET);
                $trade_html = Template::getTemp("tradeRec",$trade_data,$p);
                $trade_html .= Actions::getPageHtml($p, TradeRec::$trade_rec_page, "", $this->GET);
                $term_html = Actions::getTermHtml($this->GET, $this->file_name);
                $select_html = Actions::select_area($this->GET);
                $html = <<<LL
                
        <div class="body-list">
            <div class="body-search">
                <form id="term-form" class="form-inline" method="get">
                    <input type="hidden" name="file" value="{$this->file_name}"/>
                    <input id="date-range" type="text" name="date-range" class="form-control mr-1 mb-1" style="width: 220px" placeholder="请选择时间段"/>
                    <input id="search-text" type="text" name="title" class="form-control mr-1 mb-1" placeholder="输入要查找的关键字"/>
                    <div class="auto-term"></div>
                    <input class="btn btn-primary form-control" type="submit" value="查找"/>
                </form>
                $select_html
            </div>
            <div class="table-box">
                $trade_html
            </div>
        </div>
LL;
                break;
            case "createOrder":
                $msg_txt = "";
                if (!Cart::getCartNum(1)) $msg_txt .= "请先选中要结算的商品！";
                if (!Cart::checkCartStock(1)) $msg_txt .= "商品库存不足！";
                $new_oid = Cashier::createOrderByCart('pc');

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
            case "cashier":
                $cashier_data = Cashier::getCashier($this->GET);
                if ($cashier_data){
                    $cashier_html = Template::getTemp("cashier",$cashier_data);
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
        <div class="body-list">
            $cashier_html
        </div>
LL;
                break;
            case "confirm":
                $confirm_data = empty($this->POST)?Cashier::getConfirmByCart():Cashier::getConfirmByJson($this->POST);
                if ($confirm_data){
                    $confirm_html = Template::getTemp("confirm",$confirm_data);
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
        <div class="body-list">
            $confirm_html
        </div>
LL;
                break;
            case "refund":
                $refund_data = Cashier::refund($this->GET);
                if ($refund_data){
                    $refund_html = Template::getTemp("refund",$refund_data);
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
        <div class="body-list">
            $refund_html
        </div>
LL;
                break;
            case "print":
                $html = <<<LL
        <div class="body-list">
            <button class="btn btn-warning" onclick="$('#box').jqprint();">打印</button>
            <div id="print">
                <div id="box">
                    <div id="qrcode1" onmousedown="move(event,this)">
                        <img src="image/alipay.png" width="100%">
                    </div>
                    <div id="list" onmousedown="move(event,this)">
                        <table class="my-table">
                            <tr>
                                <th>券码</th>
                                <td>153052720400001KfeNmq</td>
                            </tr>
                            <tr>
                                <th>票名</th>
                                <td>世界之窗重返侏罗纪项目单人票</td>
                            </tr>
                            <tr>
                                <th>景区</th>
                                <td>世界之窗</td>
                            </tr>
                            <tr>
                                <th>种类</th>
                                <td>成人票</td>
                            </tr>
                            <tr>
                                <th>有效时间(起)：</th>
                                <td>2018-06-06 12:52:23</td>
                            </tr>
                            <tr>
                                <th>有效时间(止)：</th>
                                <td>2018-06-06 12:52:23</td>
                            </tr>
                            <tr>
                                <th>取票时间</th>
                                <td>2018-06-06 12:52:23</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
LL;
                break;
            default:
                $html = Template::addTailHtml("","404 访问的页面失踪了！");
                $html = <<<LL
                
        <div class="body-list">
            $html
        </div>
LL;
                break;

        }
        $html = <<<LL
        
        <div class="body-main" $this->body_main_style>
            $html
        </div>
LL;
        return $html;
    }

    private function getRightHtml($show=1)
    {
        $big = "";$small = "";
        if ($show) $small = "style='display:none'";
        else $big = "style='display:none'";

        $cart_num = Cart::getCartNum();
        $cart_num = ($cart_num>99)?"99+":$cart_num;


        if ($this->file_name == "tickets") {
            $cart_html = Cart::getCartInfo();
            $bodyRight_html = <<<LL
        <div class="body-right" $big>
            $cart_html
        </div>
        <div class="cart-ball" onclick="cartTgl()" $small>
            <div id="cart-num">$cart_num</div>
            <img src="image/cart-ball-pc.png" width="100%"/>
        </div>
LL;
        }else{
            $bodyRight_html = <<<LL

        <div class="cart-ball" onclick="go('?file=tickets')">
            <div id="cart-num">$cart_num</div>
            <img src="image/cart-ball-pc.png" width="100%"/>
        </div>
LL;
        }

        return $bodyRight_html;
    }


    function getScriptHtml()
    {
        $url_date = Actions::_GET_kv('date', '', $this->GET);

        $in_script = <<<LL

        //执行一个laydate实例
        laydate.render({
            elem: '#date-range'
            ,range: true
            ,format: 'yyyy/MM/dd'
        });
        $("#date_button").click(function(){
            var date = $("#date-range").val();
            date = date.replace(/[ ]/g,"");
            if(date === ""){
                alert("请选择正确的日期！");
            }else{
                location.href =  "$url_date" + date;
            }
        });
LL;


        switch ($this->file_name) {

            case "index":
                $data1 = array(0,0,0,0,0);
                $data2 = array(0,0,0,0,0);
                $data3 = array();
                for ($i = 1; $i <= 4; $i++) {
                    $week2 = $i - 1;
                    $sql = "select sum(money) from a_trade_rec where uid = '".UID."' and create_time between date_add(now(), interval -$i week) and date_add(now(), interval -$week2 week)";
                    if ($data = Mysql::query($sql,1)) {
                        $data1[$i] = empty($data[0]["sum(money)"]) ? 0 : $data[0]["sum(money)"];
                    }

                    $sql = "select count(*) from a_mytickets where state > 0 and uid = '".UID."' and create_time between date_add(now(), interval -$i week) and date_add(now(), interval -$week2 week)";
                    if ($data = Mysql::query($sql,1)) {
                        $data2[$i] = empty($data[0]["count(*)"]) ? 0 : $data[0]["count(*)"];
                    }

                }

                for ($i=30;$i>=1;$i--){
                    $j = $i-1;
                    $sql = "select count(*) from a_mytickets where state > 0 and uid = '".UID."' and create_time between date_add(now(), interval -$i day) and date_add(now(), interval -$j day)";
                    if ($data = Mysql::query($sql,1)) {
                        $data3[] = empty($data[0]["count(*)"]) ? 0 : (int)$data[0]["count(*)"];
                    }
                }

                $term = array("", " between 0 and 200", " between 200 and 500", "  between 500 and 1000", "  between 1000 and 1000000");
                $value = array(0,0,0,0,0);
                for ($i = 1; $i <= 4; $i++) {
                    $sql = "select count(*) from a_mytickets a,a_tickets b where a.state > 0 and a.uid = '".UID."' and a.create_time > date_add(now(), interval -1 month) and a.tid = b.tid and b.price ";
                    $sql .= $term[$i];
                    //$result = mysqli_query($mysql->con, $sql);
                    if ($data = Mysql::query($sql,1)) {
                        $value[$i] = empty($data[0]["count(*)"]) ? 0 : $data[0]["count(*)"];
                    }
                }

                $this_arr = array("name"=>"时间趋势","data"=>$data3);
                $tmppp = json_encode($this_arr);

                $html = <<<LL
<script>

$in_script
$(function () {

    $('#container1').highcharts({
        chart: {
            type: 'column',
            backgroundColor: '#FFF'
        },
        title: {
            text: '最近30天消费金额'
        },
        subtitle: {
            text: ''
        },
        credits:{
        	enabled:false
        },
        xAxis: {
            categories: [
                '第一周',
                '第二周',
                '第三周',
                '第四周'
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '消费金额 (元)'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} 元</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                borderWidth: 0
            }
        },
        series: [{
            name: '消费金额',
            data: [$data1[4],$data1[3],$data1[2],$data1[1]]
        }]
    });
    $('#container2').highcharts({
        chart: {
            type: 'column',
            backgroundColor: '#FFF'
        },
        title: {
            text: '最近30天购票张数'
        },
        subtitle: {
            text: ''
        },
        credits:{
        	enabled:false
        },
        xAxis: {
            categories: [
                '第一周',
                '第二周',
                '第三周',
                '第四周'
            ],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '购票张数 (张)'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:,.0f} 张</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                borderWidth: 0
            }
        },
        series: [{
            name: '购票张数',
            data: [$data2[4],$data2[3],$data2[2],$data2[1]]
        }]
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
                ['0-200元',   $value[1]],
                ['200-500元',  $value[2]],
                ['500-1000元',  $value[3]],
                ['1000元以上',  $value[4]]
            ]
        }]
    });
    $('#container4').highcharts({
        chart: {
            type: 'area'
        },
        credits:{
        	enabled:false
        },
        title: {
            text: '30天购票趋势'
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
        series: [$tmppp]
    });
});
</script>
</body>
</html>
LL;

                break;
            case "tickets":
                $action = new Actions();
                $url1 = $action->_GET_kv('inventory', '只看有票', $this->GET);
                $url2 = $action->del_select('inventory', $this->GET);
                $url3 = $action->_GET_kv('title', '', $this->GET);
                $html = <<<LL

<script>
$in_script
</script>
</body>
</html>
LL;
                break;
            case "myOrders":
                $json = json_encode($this->GET);
                $html = <<<LL
<script>
    $in_script
</script>
</body>
</html>
LL;
                break;
            case "cashier":
                $oid = empty($this->GET['oid']) ? null : $this->GET['oid'];
                $html = <<<LL
<script>
    $in_script
</script>
</body>
</html>
LL;
                break;

            default:
                $html = <<<LL
                
<script>
    $in_script
</script>
</body>
</html>
LL;
                break;
        }
        $footer_text = FOOTER_TEXT;
        $html = <<<LL
        
<div class="footer">
    $footer_text @2018
</div>
$html
LL;
        return $html;
    }

}