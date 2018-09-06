<?php

class System
{
    public $file_name,$GET,$POST,$page_name,$html_title;
    //构建函数，生成必要数据
    function __construct($GET,$POST)
    {
        $this->file_name = empty($GET["file"])?"index":$GET["file"];
        $this->GET = $GET;
        //根据请求的文件名获取页面名称
        $this->page_name = Actions::getPageNameByFileName($this->file_name);
        //生成页面标题
        $this->html_title = $this->page_name.TITLE_SUFFIX;
        //输出页面
        echo $this->getHeadHtml();//html头部和顶部
        echo $this->getBodyMainHtml();//页面主体div
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
    <title>$this->html_title</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/base.css"/>
    <link rel="stylesheet" href="css/admin.css"/>
    <script src="js/jquery.min.js"></script>
    <script src="js/qrcode.min.js"></script>
    <script src="js/laydate/laydate.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/highcharts.js"></script>
    <script src="js/base.js"></script>
    <script src="js/admin.js"></script>
    <script>
        $(function() {
            $.ajax({
                type:"get",
                url:"api/index.php",
                data:{act:"user_info",client:"admin"},
                dataType:"json",
                success:function(o) {
                    if (o.code == 0) {
                        warn("您尚未登录,现在带您到登录页面！");
                        setTimeout(function () {
                            go("login.html");
                        },1500);
                    } else {
                        $(".nickname").html(obj.data.nickname);
                        $(".username").html(obj.data.username);
                        $(".last-login").html(obj.data.last_login);
                    }
                },
                error:function() {
                    alert("服务器未响应！");
                }
            });

        });
    </script>
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
        <div class="body-ask-true" onclick="alert(true)">确定</div>
        <div class="body-ask-false" onclick="closeAsk()">取消</div>
    </div>
    <div class="body-frame">
        <div class="body-frame-title"></div>
        <div class="body-frame-close" onclick="closeFrame()">×</div>
        <div class="body-frame-html"></div>
    </div>
    <div class="user-area">
        管理员：<span class="nickname"></span><br/>
        登录名：<span class="username"></span><br/>
        权限级别：255<br/>
        上次登录：<span class="last-login"></span>
        <div class="logout" onclick="sys.logout('admin')">退出登录</div>
    </div>
</div>
<div class="body-top">
    <img src="image/logo.png" style="height: 30px;vertical-align: -8px">
    票务系统管理后台
    <div onclick="$('.user-area').toggle(200)" style="position: relative;float: right;cursor: pointer">
        <img src="image/user.png" style="height: 20px;vertical-align: text-top">
        <div class="body-user">
            <span class="nickname"></span> 已登录
        </div>
    </div>
</div>
<div class="body-left">
    <ul>
        <li><a href="?file=index">后台首页</a></li>
        <li class="left-item">
            <a>票券设置 <img src="image/menu-out.png"/></a>
            <ul>
				<li><a href="?file=all-tickets">票券总览</a></li>
				<li><a href="?file=auto-update">自动库存</a></li>
				<li><a href="?file=all-tic-type">票券种类</a></li>
			</ul>
        </li>
        <li><a href="?file=all-scenic">景点设置</a></li>
        <li><a href="?file=all-business">商家设置</a></li>
        <li><a href="?file=all-sale">优惠设置</a></li>
        <li><a href="?file=all-device">闸机设置</a></li>
        <li><a href="?file=web-set">网站设置</a></li>
        <li class="left-item"> 
            <a>数据报表 <img src="image/menu-out.png"/></a>
            <ul> 
                <li><a href="?file=order-form">订单报表</a></li>
                <li><a href="?file=ticket-form">票券报表</a></li>
                <li><a href="?file=trade-form">交易报表</a></li>
                <li><a href="?file=record-form">流水报表</a></li>
            </ul>
        </li>
        <li><a href="?file=test">测试功能</a></li>
        <li><a href="?logout=1">退出登录</a></li>
    </ul>
    <script>
        $(function(){
            let sidebar = $("[href='?file=$this->file_name']");
            sidebar.parent("li").addClass("strong-active").parent("ul").parent("li").addClass("strong-active");
            sidebar.parent("li").parent("ul").siblings("a").click();
        });
    </script>
</div>
LL;
        return $head_html;
    }

    //输出页面主体div(.body-main)
    private function getBodyMainHtml()
    {
        $hang_menu_arr = array();
        switch ($this->file_name) {
            case "index":
                $html = <<<LL
                
        <div class="title-lg">售票情况统计图</div>
        <div class="chart-group">
            <div class="chart-box"> 
                <div id="container1" class="chart" style="left: 0;top: 0;right: 5px;bottom: 5px"></div>
            </div>

            <div class="chart-box"> 
                <div id="container2" class="chart" style="left: 5px;top: 0;right: 0;bottom: 5px"></div>
            </div>
            <div class="chart-box"> 
                <div id="container3" class="chart" style="left: 0;top: 5px;right: 5px;bottom: 0"></div>
            </div>
            <div class="chart-box"> 
                <div id="container4" class="chart" style="left: 5px;top: 5px;right: 0;bottom: 0"></div>
            </div>
        </div>
LL;
                break;
            case "all-tickets":
                $html = Tickets::getAllTickets($this->GET);
                $hang_menu_arr = [
                    "新增票券" => "getFrame(\"AddTicket\",\"\")"
                ];
                break;
            case "auto-update":
                $html = Tickets::getAutoUpdate();
                $hang_menu_arr = [
                ];
                break;
            case "all-tic-type":
                $html = Tickets::getAllTicType();
                $hang_menu_arr = [
                    "新增种类" => "getFrame(\"AddTicType\",\"\")"
                ];
                break;
            case "all-scenic":
                $html = Scenic::getAllScenic();
                $hang_menu_arr = array(
                    "新增景点" => "getFrame(\"AddScenic\",\"\")"
                    
                );
                break;
            case "all-business":
                $html = Business::getAllBusiness();
                $hang_menu_arr = array(
                    "新增商家" => "getFrame(\"AddBusiness\",\"\")"
                    
                );
                break;
            case "all-sale":
                $html = Sale::getAllSale();
                $hang_menu_arr = array(
                    "添加优惠" => "getFrame(\"AddSale\",\"\")"
                    
                );
                break;
            case "all-device":
                $html = Device::getAllDevice();
                $hang_menu_arr = array(
                    "添加设备" => "getFrame(\"AddDevice\",\"\")"
                    
                );
                break;
            case "web-set":
                $html = WebSet::getAllWebSet();
                $hang_menu_arr = array(
                    "设置说明" => ""
                );
                break;
            case "order-form":
                $date_range = empty($this->GET["date_range"])?"":$this->GET["date_range"];
                $html = <<<LL
        <div class="term-area">
            <div> 
                <label for="term-date-range">时间范围：</label>
                <input type="text" id="term-date-range" value="$date_range" placeholder="默认查询本月" style="color: #333;width: 200px"/>
            </div>
            <div> 
                <button type="button" class="btn btn-sm btn-info btn-line" onclick="goTerm('$this->file_name')">查询</button>
            </div>
            <script>
                laydate.render({
                    elem: '#term-date-range'
                    ,type: "datetime",
                    format:"yyyy/MM/dd",
                    max:0,
                    range:true
                });
            </script>
        </div>
LL;
                $html.= ReportForm::getOrderForm($this->GET);
                $hang_menu_arr = array(
                    "报表" => ""
                );
                break;
            case "ticket-form":
                $date_range = empty($this->GET["date_range"])?"":$this->GET["date_range"];
                $pid = empty($this->GET["pid"])?"":$this->GET["pid"];
                $bid = empty($this->GET["bid"])?"":$this->GET["bid"];
                $scenic_options = Actions::getOpts("scenic",$pid);
                $business_options = Actions::getOpts("business",$bid);
                $scenic_name = Actions::getNameByXid("pid",$pid);
                $business_name = Actions::getNameByXid("bid",$bid);
                $scenic_name = empty($scenic_name)?"全部":$scenic_name;
                $business_name = empty($business_name)?"全部":$business_name;
                $html = <<<LL
     <div class="term-area">
            <div> 
                <label for="term-date-range">时间范围：</label>
                <input type="text" id="term-date-range" value="$date_range" placeholder="默认查询本月" style="color: #333;width: 200px"/>
            </div>
            <div> 
                <label for="term-scenic">景点：</label>
                <select id="term-scenic" style="color: #333;width: 150px">
                    <option value="">全部</option>
                    $scenic_options
                </select>
            </div>
            <div> 
                <label for="term-business">商家：</label>
                <select id="term-business" style="color: #333;width: 150px">
                    <option value="">全部</option>
                    $business_options
                </select>
            </div>
            <div> 
                <button type="button" class="btn btn-sm btn-info btn-line" onclick="goTerm('$this->file_name')">查询</button>
            </div>
            <script>
                laydate.render({
                    elem: '#term-date-range'
                    ,type: "datetime",
                    format:"yyyy/MM/dd",
                    max:0,
                    range:true
                });
                $(function() {
                    let term_text = " 景点：$scenic_name 商家：$business_name";
                    $("#term-text").html(term_text);
                });
                
            </script>
        </div>
LL;

                $html.= ReportForm::getTicketForm($this->GET);
                $hang_menu_arr = array(
                    "报表" => ""
                );
                break;
            case "trade-form":
                $date_range = empty($this->GET["date_range"])?"":$this->GET["date_range"];
                $html = <<<LL
        <div class="term-area">
            <div> 
                <label for="term-date-range">时间范围：</label>
                <input type="text" id="term-date-range" value="$date_range" placeholder="默认查询本月" style="color: #333;width: 200px"/>
            </div>
            <div> 
                <button type="button" class="btn btn-sm btn-info btn-line" onclick="goTerm('$this->file_name')">查询</button>
            </div>
            <script>
                laydate.render({
                    elem: '#term-date-range'
                    ,type: "datetime",
                    format:"yyyy/MM/dd",
                    max:0,
                    range:true
                });
            </script>
        </div>
LL;

                $html.= ReportForm::getTradeForm($this->GET);
                $hang_menu_arr = array(
                    "报表" => ""
                );
                break;
            case "record-form":
                $date_range = empty($this->GET["date_range"])?"":$this->GET["date_range"];
                $pid = empty($this->GET["pid"])?"":$this->GET["pid"];
                $bid = empty($this->GET["bid"])?"":$this->GET["bid"];
                $state_id = empty($this->GET["state_id"])?"":$this->GET["state_id"];
                $scenic_options = Actions::getOpts("scenic",$pid);
                $business_options = Actions::getOpts("business",$bid);
                $tic_state_options = Actions::getOpts("tic-state",$state_id);
                $scenic_name = Actions::getNameByXid("pid",$pid);
                $business_name = Actions::getNameByXid("bid",$bid);
                $scenic_name = empty($scenic_name)?"全部":$scenic_name;
                $business_name = empty($business_name)?"全部":$business_name;
                $html = <<<LL
        <div class="term-area">
            <div> 
                <label for="term-date-range">时间范围：</label>
                <input type="text" id="term-date-range" value="$date_range" placeholder="默认查询本月" style="color: #333;width: 200px"/>
            </div>
            <div> 
                <button type="button" class="btn btn-sm btn-info btn-line" onclick="goTerm('$this->file_name')">查询</button>
            </div>
            <div> 
                <label for="term-scenic">景点：</label>
                <select id="term-scenic" style="color: #333;width: 150px">
                    <option value="">全部</option>
                    $scenic_options
                </select>
            </div>
            <div> 
                <label for="term-business">商家：</label>
                <select id="term-business" style="color: #333;width: 150px">
                    <option value="">全部</option>
                    $business_options
                </select>
            </div>
            <div> 
                <label for="term-tic-state">状态：</label>
                <select id="term-tic-state" style="color: #333;width: 150px">
                    <option value="">全部</option>
                    $tic_state_options
                </select>
            </div>
            <div> 
                <button type="button" class="btn btn-sm btn-info btn-line" onclick="goTerm('$this->file_name')">查询</button>
            </div>
            <script>
                laydate.render({
                    elem: '#term-date-range'
                    ,type: "datetime",
                    format:"yyyy/MM/dd",
                    max:0,
                    range:true
                });
                $(function() {
                    let term_text = " 景点：$scenic_name 商家：$business_name";
                    $("#term-text").html(term_text);
                });
            </script>
        </div>
LL;

                $html.= ReportForm::getRecordForm($this->GET);
                $hang_menu_arr = array(
                    "报表" => ""
                );
                break;
            case "test":
                $device_options = Actions::getOpts("device");
                $html = <<<LL
        <div id="body_list">
            <div style="width:600px;border: solid 1px;margin-top: 20px;padding: 20px">
                <div>
                    *相关说明见文档《验票接口接收数据和返回值.txt》<br/>
                    <label for="device_id">选择验票机：</label>
                    <select id="device_id" style="color: #363636;width: 400px">
                        $device_options
                    </select><br/>
                    <input width="100px" type="text" id="ticket_key" class="form-control" placeholder="输入券码">
                    <button type="button" id="valid" onclick="valid_test();" class="btn btn-success">验证</button>
                </div>
            </div>
        </div>
LL;
                $hang_menu_arr = [];
                break;
            default:
                $html = <<<LL
           <script>
               warn('页面走丢了...');
               setTimeout(
                   function() {
                       go('?file=index');
                   },3000
               )
           </script>
LL;
                break;
        }
        $hang_html = "";
        foreach ($hang_menu_arr as $key=>$value){
            $hang_html.= "<div class='hang-menu' onclick='$value'>$key</div>";
        }
        $html = <<<LL
    <div class="hang-area"> 
        $hang_html
    </div>
    <div class="body-main">
        $html
    </div>
LL;
        return $html;
    }

    //输出JavaScript和html结尾
    private function getScriptHtml()
    {
        switch ($this->file_name) {
            case "index":
                $data1 = array(0,0,0,0,0);
                $data2 = array(0,0,0,0,0);
                $data3 = array();
                for ($i = 1; $i <= 4; $i++) {
                    $week2 = $i - 1;
                    $sql = "select sum(money) from a_trade_rec where create_time between date_add(now(), interval -$i week) and date_add(now(), interval -$week2 week)";
                    if ($data = Mysql::query($sql,1)) {
                        $data1[$i] = empty($data[0]["sum(money)"]) ? 0 : $data[0]["sum(money)"];
                    }

                    $sql = "select count(*) from a_mytickets where state > 0 and create_time between date_add(now(), interval -$i week) and date_add(now(), interval -$week2 week)";
                    if ($data = Mysql::query($sql,1)) {
                        $data2[$i] = empty($data[0]["count(*)"]) ? 0 : $data[0]["count(*)"];
                    }

                }

                for ($i=30;$i>=1;$i--){
                    $j = $i-1;
                    $sql = "select count(*) from a_mytickets where state > 0 and create_time between date_add(now(), interval -$i day) and date_add(now(), interval -$j day)";
                    if ($data = Mysql::query($sql,1)) {
                        $data3[] = empty($data[0]["count(*)"]) ? 0 : (int)$data[0]["count(*)"];
                    }
                }

                $term = array("", " between 0 and 200", " between 200 and 500", "  between 500 and 1000", "  between 1000 and 1000000");
                $value = array(0,0,0,0,0);
                for ($i = 1; $i <= 4; $i++) {
                    $sql = "select count(*) from a_mytickets a,a_tickets b where a.state > 0 and a.create_time > date_add(now(), interval -1 month) and a.tid = b.tid and b.price ";
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
    $(function () {

    $('#container1').highcharts({
        chart: {
            type: 'column',
            backgroundColor: '#FFF'
        },
        title: {
            text: '最近30天营业额'
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
                text: '营业额 (元)'
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
            name: '营业额',
            data: [$data1[4],$data1[3],$data1[2],$data1[1]]
        }]
    });
    $('#container2').highcharts({
        chart: {
            type: 'column',
            backgroundColor: '#FFF'
        },
        title: {
            text: '最近30天售票张数'
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
                text: '售票张数 (张)'
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
            name: '售票张数',
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
            text: '最近30天售票单价'
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
            text: '30天售票趋势'
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
            pointFormat: '{series.name} 售票 <b>{point.y:,.0f}</b>张'
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