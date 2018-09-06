"use strict";
let customer = {
    /*通用api地址*/
    api_url: "api/index.php",
    /*默认端口，customer内不建议修改*/
    client: "customer",
    /*默认页码*/
    p: 1,
    /*默认页面*/
    page: "index",
    /*默认页面名称*/
    page_name: "主页",

    show_cart: true,
    show_menu: true,

    /*常量，系统设置的title后缀*/
    TITLE_SUFFIX: "",
    /*常量，系统设置的页脚文字*/
    FOOTER_TEXT: "",
    ROWS_PER_PAGE: 5,

    /*订单支付倒计时flag*/
    cashierCountDown: "",

    //加载类
    /*加载系统设置*/
    load: {
        SysSet: function () {
            customer.TITLE_SUFFIX = sys.getCookie("TITLE_SUFFIX");
            customer.FOOTER_TEXT = sys.getCookie("FOOTER_TEXT");
            if (customer.TITLE_SUFFIX && customer.FOOTER_TEXT) {
                return;//已有数据直接返回
            }
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "sys_set"
                },
                async: false,
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        console.log("获取云端系统设置失败！");
                    } else {
                        customer.TITLE_SUFFIX = o.data.TITLE_SUFFIX;
                        customer.FOOTER_TEXT = o.data.FOOTER_TEXT;
                    }
                },
                error: function () {
                    console.log("服务器未响应loadSysSet");
                }
            });
        },
        /*加载用户信息，必须实时更新*/
        UserInfo: function () {
            let isLogin = false;
            $.ajax({
                async:false,
                url: customer.api_url,
                data: {
                    act: "user_info",
                    client: customer.client
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn("您尚未登录,现在带您到登录页面！");
                        setTimeout(function () {
                            go("login.html");
                        }, 1000);
                    } else {
                        isLogin = true;
                        $(".nickname").html(o.data.nickname);
                        $(".money").html(o.data.money);
                    }
                },
                error: function () {
                    warn("获取用户信息失败了");
                }
            });
            return isLogin;
        },
        /*加载主页图表*/
        FormReport: function () {
            let o = $.ajax({
                async:false,
                url: customer.api_url,
                data: {
                    act: "report_data",
                    client: customer.client
                },
                dataType: "json",
                error: function () {
                    warn("获取统计信息失败了");
                }
            });
            let data = o.responseJSON.data;
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
                credits: {
                    enabled: false
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
                    data: data.data1
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
                credits: {
                    enabled: false
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
                    data: data.data2
                }]
            });
            $('#container3').highcharts({
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false
                },
                credits: {
                    enabled: false
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
                        ['0-200元', data.data3[1]],
                        ['200-500元', data.data3[2]],
                        ['500-1000元', data.data3[3]],
                        ['1000元以上', data.data3[4]]
                    ]
                }]
            });
            $('#container4').highcharts({
                chart: {
                    type: 'area'
                },
                credits: {
                    enabled: false
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
                series: [{"name":"时间趋势","data":data.data4}]
            });
        },
        /*加载票券列表*/
        TicketList: function (p, rows) {
            if (!p) p = 1;
            if (!rows) rows = 0;
            let date_range = $("#ticket-date-range").val(),
                title = $("#ticket-search-text").val(),
                type = $("#ticket-term-type option:checked").val(),
                pid = $("#ticket-term-scenic option:checked").val(),
                times = $("#ticket-term-times option:checked").val(),
                price = $("#ticket-term-price option:checked").val();
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "ticket_list",
                    p: p,
                    rows: rows,
                    date_range: date_range,
                    title: title,
                    type: type,
                    pid: pid,
                    times: times,
                    price: price
                },
                dataType: "json",
                success: function (o) {
                    $("#ticket-thumb").html("");
                    $("#ticket-table table tbody").html("");
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        let i = (p * 1 - 1) * rows;
                        for (let row of o.data.list) {
                            i++;
                            /*显示次数的处理*/
                            if (row.tic_type === "常规票") {
                                row.times = row.times + " 次";
                            } else if (row.tic_type === "计时票") {
                                row.times = "不限次数";
                            } else if (row.tic_type === "套票") {
                                row.times = "以子票券为准";
                            }
                            //加入卡片式票券显示
                            $("#ticket-thumb").append(`
                                <div class="card-item" onclick="customer.show.ticket('${row.tid}')">
                                    <div class="card-img">
                                        <img src="pictures/ticket/${row.tid}/index.jpg" onerror="$(this).attr('src','pictures/scenic/default.jpg');return false;"/>
                                    </div>
                                    <div class="card-info">
                                        <div class="card-name">
                                            ${row.title}
                                        </div>
                                        <div class="card-price">
                                            <span>¥ <b>${row.price}</b></span>
                                        </div>
                                        <div class="card-href">
                                            <button onclick="customer.do.cartCtrl('${row.tid}','+1');" type="button" class="btn btn-warning btn-sm add-to-cart">抢购</button>
                                        </div>
                                    </div>
                                </div>`);
                            $(".add-to-cart").click(function (event) {
                                event.stopPropagation();//阻止事件冒泡即可
                            });
                            //加入表格式票券显示
                            $("#ticket-table table tbody").append(`
                                <tr>
                                    <td>${i}</td>
                                    <td>
                                        <img src="pictures/ticket/${row.tid}/index.jpg" onerror="$(this).attr('src','pictures/scenic/default.jpg');return false;" width="40px" height="40px">
                                    </td>
                                    <td>${row.title}
                                    <details>
                                        <summary>详情</summary>
                                        ${row.detail}
                                    </details>
                                    </td>
                                    <td>${row.tic_type}</td>
                                    <td>${row.begin_end_time}</td>
                                    <td>${row.times}</td>
                                    <td>${row.type}</td>
                                    <td>${row.city}</td>
                                    <td>${row.price}</td>
                                    <td>${row.stock}</td>
                                    <td> 
                                        <img src="image/cart-small.png" onclick="customer.do.cartCtrl('${row.tid}','+1')" style="width: 30px;cursor: pointer;"/>
                                    </td>
                                </tr>`);
                        }
                        //加入分页机制
                        $(".pagination").html("");
                        $(".page-of-ticket .body-list .pagination").html(customer.sys.mkPagination(p, o.data.pages));
                    }
                },
                error: function () {
                    warn("获取票券列表失败了");
                }
            });
        },
        /*加载票券筛选条件*/
        TicketTerm: function () {
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "ticket_term"
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        $(".auto-term").html("");
                        for (let row of o.data) {
                            let options = "";
                            for (let key in row.value) {
                                options += `<option value="${key}">${row.value[key]}</option>`;
                            }
                            $(".auto-term").append(
                                `<select name="${row.name}" class="form-control mr-1 mb-1 ticket-term" style="width: 120px">
                            <option value="">${row.title}</option>
                                ${options}
                            </select>`);
                        }
                    }
                },
                error: function () {
                    warn("服务器未响应，");
                }
            });
        },
        /*加载景点列表*/
        ScenicList: function () {
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "scenic_list"
                },
                dataType: "json",
                success: function (o) {
                    $("#scenic-item-list").html("");
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {

                        for (let row of o.data) {
                            $("#scenic-item-list").append(
                                `<div class="card-item">
                <div class="card-img">
                    <img src="pictures/scenic/${row.pid}/index.jpg" onerror="$(this).attr('src','pictures/scenic/default.jpg');return false;"/>
                </div>
                <div class="card-info">
                    <div class="card-name">
                        ${row.name}
                    </div>
                    <div class="card-price">
                        <span>¥ <b>${row.avg_pay}</b></span> 人均消费
                    </div>
                    <div class="card-href">
                        <a href="#" class="btn btn-warning btn-sm">抢购</a>
                    </div>
                </div>
            </div>`);
                        }
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载订单列表*/
        OrderList: function (p, rows) {
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "order_list",
                    client: customer.client,
                    p: p,
                    rows: rows
                },
                dataType: "json",
                success: function (o) {
                    $("#order-table table tbody").html("");
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        let i = (p * 1 - 1) * rows;
                        for (let row of o.data.list) {
                            i++;
                            let order_btn = "";

                            switch (row.state) {
                                case "-1":
                                    let createTime = row.create_time;
                                    createTime = createTime.substring(0, 19);
                                    createTime = createTime.replace(/-/g, '/');
                                    let nowTimeStamp = parseInt(new Date().getTime() / 1000);
                                    let orderTimeStamp = (new Date(createTime).getTime()) / 1000;
                                    let second = orderTimeStamp + o.data.order_live_time * 1 - nowTimeStamp;
                                    if (second > 0) {
                                        row.state_name +=
                                            ` (<span class='left-time-${i}'> </span>)
                                        <script>
                                            makeTimeCtDwn('.left-time-${i}',${second});
                                        </script>`;
                                        order_btn = `
                                        <button type="button" class="btn btn-sm btn-info" onclick="sys.setCookie('this_oid','${row.oid}',3600);customer.do.switchPage('cashier')">
                                            支付
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="ask('确定要取消这个订单么？',function() {
                                            customer.do.refundOrder('${row.oid}');
                                        })">
                                            取消
                                        </button>`;
                                    } else {
                                        row.state_name += " (正在取消)";
                                        order_btn = `
                                        <button type="button" class="btn btn-sm btn-danger" onclick="ask('确定要删除这个订单么？',function() {
                                            customer.do.delOrder('${row.oid}');
                                        })">
                                            删除
                                        </button>`;
                                    }
                                    break;
                                case "1":
                                    order_btn = `
                                <button type="button" class="btn btn-sm btn-warning" onclick="ask('确定要退掉这个订单么？',function() {
                                            customer.do.refundOrder('${row.oid}');
                                        })">
                                    退票
                                </button>`;
                                    break;
                                default:
                                    if (row.visibility === "1") {
                                        order_btn = `
                                        <button type="button" class="btn btn-sm btn-danger" onclick="ask('确定要删除这个订单么？',function() {
                                            customer.do.delOrder('${row.oid}');
                                        })">
                                            删除
                                        </button>`;
                                    } else {
                                        order_btn = `
                                        <button type="button" class="btn btn-sm btn-info" onclick="ask('确定要还原这个订单么？',function() {
                                            customer.do.delOrder('${row.oid}');
                                        })">
                                            还原
                                        </button>`;
                                    }
                                    break;
                            }
                            //加入表格式票券显示
                            $("#order-table table tbody").append(
                                `<tr>
                                <td>${i}</td>
                                <td>${row.oid}</td>
                                <td>${row.price} 元</td>
                                <td>${row.num} 张</td>
                                <td>${row.create_time}</td>
                                <td>${row.state_name}</td>
                                <td> 
                                    ${order_btn}
                                </td>
                            </tr>`);
                        }
                    }
                    //加入分页机制
                    $(".pagination").html("");
                    $("#order-table .pagination").html(customer.sys.mkPagination(p, o.data.pages));
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载我的票券列表*/
        MyTicList: function (p, rows) {
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "my_tic_list",
                    client: customer.client,
                    p: p,
                    rows: rows
                },
                dataType: "json",
                success: function (o) {
                    $("#my-tic-table table tbody").html("");
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        let i = (p * 1 - 1) * rows;
                        for (let row of o.data.list) {
                            i++;
                            //加入表格式票券显示
                            $("#my-tic-table table tbody").append(
                                `<tr>
                                <td>${i}</td>
                                <td>
                                    <img src="pictures/ticket/${row.tid}/index.jpg" onerror="$(this).attr('src','pictures/scenic/default.jpg');return false;" width="40px" height="40px">
                                </td>
                                <td>${row.title}</td>
                                <td>${row.ticket_KEY}</td>
                                <td>${row.begin_end_time}</td>
                                <td>${row.times}</td>
                                <td>${row.type}</td>
                                <td>${row.state_name}</td>
                                <td>${row.create_time}</td>
                                <td> 
                                    <button class="btn btn-sm btn-info" onclick="customer.show.MyTic('${row.ticket_KEY}');">
                                        查看
                                    </button>
                                </td>
                            </tr>`);
                        }
                        //加入分页机制
                        $(".pagination").html("");
                        $("#my-tic-table .pagination").html(customer.sys.mkPagination(p, o.data.pages));

                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载交易记录列表*/
        TradeList: function (p, rows) {
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "trade_list",
                    client:customer.client,
                    p: p,
                    rows: rows
                },
                dataType: "json",
                success: function (o) {
                    $("#trade-table table tbody").html("");
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        let i = (p * 1 - 1) * rows;
                        for (let row of o.data.list) {
                            i++;
                            //加入表格式票券显示
                            $("#trade-table table tbody").append(
                                `<tr>
                                <td>${i}</td>
                                <td>${row.create_time}</td>
                                <td>${row.oid}</td>
                                <td>${row.trade_num}</td>
                                <td>${row.type}</td>
                                <td>${row.payment}</td>
                                <td>${row.money}</td>
                            </tr>`);
                        }
                    }
                    //加入分页机制
                    $(".pagination").html("");
                    $("#trade-table .pagination").html(customer.sys.mkPagination(p, o.data.pages));
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载购物车列表*/
        CartList: function () {
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "cart_list",
                    client: customer.client
                },
                dataType: "json",
                success: function (o) {
                    let card_list = $(".cart-list");
                    card_list.html("");
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        $(".final-num").html(o.data.total.final.num);
                        $(".final-price").html(o.data.total.final.sale_price);
                        for (let sale in o.data.cart) {
                            let goodsHtml = "";
                            let sale_arr = o.data.cart[sale];
                            for (let good in sale_arr) {
                                let chk = "";
                                let goods = sale_arr[good];
                                let options = sys.mkOptions(goods.num, 99);
                                if (goods.state === "1") {
                                    chk = "checked";
                                }
                                goodsHtml += `<div class="goods">
                                <div style="position: absolute;"> 
                                    <label for="${goods.tid}">
                                        <input id="${goods.tid}" onchange="customer.do.cartCtrl('${goods.tid}','*')" type="checkbox" ${chk}/>
                                        <span> </span>
                                    </label>
                                </div>
                                <div class="cart-ticket-title">
                                    ${goods.title}
                                </div>
                                <div style="height: 24px;float: left;margin-right: 3px">
                                    <img src="image/-.png" style="cursor:pointer;vertical-align:top;height: 100%"  onclick="customer.do.cartCtrl('${goods.tid}','-1')"/>
                                </div>
                                <div style="height: 24px;float: left;margin-right: 3px;">
                                    <select style="width:50px;font-size: 20px;font-family: '黑体',serif" onchange="customer.do.cartCtrl('${goods.tid}','~'+$(this).val())">
                                        ${options}
                                    </select>         
                                </div>
                                <div style="height: 24px;float: left;margin-right: 5px">
                                    <img src="image/+.png" style="cursor:pointer;vertical-align:top;height: 100%" onclick="customer.do.cartCtrl('${goods.tid}','+1')"/>
                                </div>
                                
                                <label style="color: #F00;line-height: 15px">￥${goods.price}</label><br>
                                <div class="del-cart">
                                    <img width="25px" src="image/delete.png" onclick="ask('您确定要删除吗？',function(){customer.do.cartCtrl('${goods.tid}',0)})"/>
                                </div>
                            </div>`;
                            }
                            card_list.append(
                                `<div class="sale-name">无优惠：不参与优惠</div>
                            <div class="goods-sale">
                                ${goodsHtml}
                                <div style="text-align: right">
                                 <label style="color: #FFF;font-size:16px;line-height: 12px"><del>￥??</del></label>
                                 <label style="color: #FFF;font-size:18px;line-height: 12px">￥???</label>
                                </div>
                            </div>
                        `);
                        }
                    }
                },
                error: function () {
                    warn("购物车加载失败");
                }
            });
        },
        /*加载订单确认页面列表*/
        ConfirmList: function () {
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "confirm_list",
                    client: customer.client
                },
                dataType: "json",
                success: function (o) {
                    $("#confirm-table tbody").html("");
                    if (o.code === 0) {
                        warn(o.msg);
                        $(".pay-num").html(0);
                        $(".pay-orig-price").html(0);
                        $(".pay-save-money").html(0);
                        $(".pay-sale-price").html(0);
                    } else {
                        $(".pay-num").html(o.data.pay.final.num);
                        $(".pay-orig-price").html(o.data.pay.final.orig_price);
                        $(".pay-save-money").html(o.data.pay.final.orig_price - o.data.pay.final.sale_price);
                        $(".pay-sale-price").html(o.data.pay.final.sale_price);
                        //return;
                        let i = 0;
                        for (let row of o.data.list) {
                            i++;
                            switch (row.tic_type) {
                                case "1":
                                    row.tic_type = "常规票";
                                    break;
                                case "2":
                                    row.tic_type = "计时票";
                                    break;
                                case "3":
                                    row.tic_type = "套票";
                                    break;
                                default:
                                    row.tic_type = "";
                                    break;
                            }
                            $("#confirm-table tbody").append(
                                `<tr>
                                <td>${i}</td>
                                <td><a onclick="showDetail('${row.tid}');"><img src="pictures/ticket/${row.tid}/index.jpg" width="40px" height="40px"></a></td>
                                <td><span class="badge badge-pill badge-info">${row.tic_type}</span> ${row.title} </td>
                                <td>起：${row.begin_time}<br>止：${row.end_time}</td>
                                <td>${row.type}</td>
                                <td>${row.price} 元</td>
                                <td>${row.num}</td>
                            </tr>`);
                        }
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载收银台页面*/
        CashierInfo: function () {
            let oid = sys.getCookie('this_oid');
            if (!oid) {
                warn("请选择要支付的订单");
                $(".menu-of-order").click();
                return;
            }
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "order_detail",
                    client:customer.client,
                    oid: oid
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        if (o.data.state === "-1") {
                            warn("订单超时将自动取消，请尽快支付！");
                            $(".cashier-oid").html(o.data.oid);
                            $(".cashier-tic-num").html(o.data.num);
                            $(".cashier-orig-price").html(o.data.orig_price);
                            $(".cashier-save-money").html(o.data.orig_price - o.data.price);
                            $(".cashier-sale-price").html(o.data.price);

                            let createTime = o.data.create_time;
                            createTime = createTime.substring(0, 19);
                            createTime = createTime.replace(/-/g, '/');
                            let nowTimeStamp = parseInt(new Date().getTime() / 1000);
                            let orderTimeStamp = (new Date(createTime).getTime()) / 1000;
                            let order_time = orderTimeStamp + o.data.order_live_time * 1 - nowTimeStamp;
                            let order_min = 0, order_sec = 0;
                            if (order_time > 0) {
                                clearTimeout(customer.cashierCountDown);
                                customer.cashierCountDown = setInterval(function () {
                                    if (order_time <= 0) {
                                        warn("订单支付已超时！");
                                        setTimeout(function () {
                                            $(".page-of-order").click();
                                        }, 2000);
                                        return;
                                    }
                                    order_time--;
                                    order_min = Math.floor(order_time / 60);
                                    order_sec = order_time % 60;
                                    $(".order-min").html(order_min);
                                    $(".order-sec").html(order_sec);
                                }, 1000);
                            }
                            return;
                        } else if (o.data.state === "1") {
                            warn("您已支付过此笔订单！");
                        } else if (o.data.state === "2") {
                            warn("订单已经取消不允许再支付！");
                        } else if (o.data.state === "3") {
                            warn("订单已经退款不允许再支付！");
                        } else if (o.data.state === "4") {
                            warn("订单已经取消不允许再支付！");
                        }
                        customer.do.switchPage('order');
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },

    },

    //执行操作类型
    do: {
        /*ajax是否使用中*/
        ajaxBusy: function () {
            let ajaxBusy = sys.getCookie("ajaxBusy");
            if (ajaxBusy) {
                warn("处理中，请稍等...");
                return true;
            } else {
                return false;
            }
        },
        /*锁定ajax,0.5秒后自动释放*/
        ajaxLock: function () {
            sys.setCookie("ajaxBusy", 1, 0.5);
        },
        /*选择切换页面*/
        switchPage: function (page, p, rows) {
            /*去除小数*/
            p = Math.floor(p);
            rows = Math.floor(rows);
            /*未登录不允许操作*/
            if (!customer.load.UserInfo()) {
                return;
            }
            /*page不存在则获取cookie，还不存在则默认*/
            if (!page) {
                page = sys.getCookie("page");
                if (!page) {
                    page = customer.page;
                }
            }
            /*p不合法则取cookie，还不合法则1*/
            if (!p || p < 1) {
                p = Math.floor(sys.getCookie("p"));
                if (!p || p < 1) {
                    p = 1;
                }
            }
            /*rows不合法则cookie，还不合法则默认*/
            if (!rows || rows < 1) {
                rows = Math.floor(sys.getCookie("rows"));
                if (!rows || rows < 1) {
                    rows = customer.ROWS_PER_PAGE;
                }
            }
            /*执行非cookie新页面则保存新页面名称，p=1*/
            if (sys.getCookie("page") && page !== sys.getCookie("page")) {
                sys.setCookie("page",page);
                p = 1;
            }
            //初始化加载系统设置
            customer.load.SysSet();
            //初始化加载用户信息
            customer.load.UserInfo();
            $("main").hide();
            switch (page) {
                case "index":
                    $(".page-of-index").show();
                    //必须先显示再绘图，否则会发生异常
                    customer.load.FormReport();
                    customer.page_name = "主页";
                    break;
                case "ticket":
                    customer.load.TicketList(p, rows);
                    customer.load.TicketTerm();
                    $(".page-of-ticket").show();
                    customer.page_name = "票券商城";
                    break;
                case "scenic":
                    customer.load.ScenicList();
                    $(".page-of-scenic").show();
                    customer.page_name = "热门景点";
                    break;
                case "order":
                    customer.load.OrderList(p, rows);
                    $(".page-of-order").show();
                    customer.page_name = "我的订单";
                    break;
                case "mytic":
                    customer.load.MyTicList(p, rows);
                    $(".page-of-my-tic").show();
                    customer.page_name = "我的票券";
                    break;
                case "trade":
                    customer.load.TradeList(p, rows);
                    $(".page-of-trade").show();
                    customer.page_name = "交易记录";
                    break;
                case "confirm":
                    customer.load.ConfirmList();
                    $(".page-of-confirm").show();
                    customer.page_name = "确认订单";
                    break;
                case "cashier":
                    customer.load.CashierInfo();
                    $(".page-of-cashier").show();
                    customer.page_name = "收银台";
                    break;
                default:
                    return;
            }
            customer.page = page;
            sys.setCookie("page", page);
            sys.setCookie("p", p);
            //sys.setCookie("rows", rows);
            $(".page-name").html(customer.page_name);
            $(document).attr("title", customer.page_name + customer.TITLE_SUFFIX);
            $("footer").html(customer.FOOTER_TEXT + " @2018");
        },
        /*请求提交订单*/
        createOrder: function () {
            if (customer.do.ajaxBusy()) {
                return;
            } else {
                customer.do.ajaxLock();
            }
            $.ajax({
                type: "post",
                url: customer.api_url,
                data: {
                    act: "create_order",
                    client:customer.client,
                    cart: [],
                    buy_way: "pc"
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg + o.data.oid);
                        customer.load.CartList();
                        customer.load.ConfirmList();
                        sys.setCookie("this_oid", o.data.oid, 3600);
                        customer.do.switchPage("cashier");
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*请求取消订单*/
        refundOrder: function (oid) {
            if (customer.do.ajaxBusy()) {
                return;
            } else {
                customer.do.ajaxLock();
            }
            $.ajax({
                type: "post",
                url: customer.api_url,
                data: {
                    act: "refund_order",
                    client:customer.client,
                    oid: oid
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg);
                        customer.do.switchPage();
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*请求删除（隐藏）订单*/
        delOrder: function (oid) {
            if (customer.do.ajaxBusy()) {
                return;
            } else {
                customer.do.ajaxLock();
            }
            $.ajax({
                type: "post",
                url: customer.api_url,
                data: {
                    act: "del_order",
                    client:customer.client,
                    oid: oid
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg);
                        customer.do.switchPage();
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*请求余额支付订单*/
        payOrder: function () {
            let oid;
            if (customer.do.ajaxBusy()) {
                return;
            } else {
                customer.do.ajaxLock();
            }
            if (!(oid = sys.getCookie("this_oid"))) {
                warn("支付超时，请重新进入支付页面");
                return;
            }
            $.ajax({
                type: "post",
                url: customer.api_url,
                data: {
                    act: "pay_order",
                    client:customer.client,
                    oid: oid
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg);
                        setTimeout(function () {
                            $(".menu-of-order").click();
                        }, 1500);
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*购物车显示/隐藏*/
        cartTgl: function (display) {
            let cart_big = $(".body-right");
            let cart_small = $(".cart-ball");
            let div_main = $(".page-of-ticket");
            if (display === "show") {
                div_main.animate({paddingRight: "220px"}, 200);
                cart_big.slideDown(300);
                cart_small.hide(300);
                sys.setCookie("show_cart", true);
            } else if (display === "hide") {
                div_main.animate({paddingRight: 0}, 200);
                cart_big.slideUp(300);
                cart_small.show(300);
                sys.setCookie("show_cart", false);
            } else {
                if (cart_big.is(":hidden")) {
                    div_main.animate({paddingRight: "220px"}, 300);
                    sys.setCookie("show_cart", true);
                } else {
                    div_main.animate({paddingRight: 0}, 300);
                    sys.setCookie("show_cart", false);
                }
                cart_big.slideToggle(300);
                cart_small.toggle(300);
            }

        },
        menuTgl: function (display) {
            let menu = $("menu");
            if (display === "show") {
                menu.animate({width: "129px"}, 300);
                $("main").animate({paddingLeft: "130px"}, 300);
                $(".menu-in-out img").css({'animation': 'rotate-180 0.3s', 'animation-fill-mode': 'forwards'});
                sys.setCookie("show_menu", true);
            } else if (display === "hide") {
                menu.animate({width: "39px"}, 300);
                $("main").animate({paddingLeft: "40px"}, 300);
                $(".menu-in-out img").css({'animation': 'rotate180 0.3s', 'animation-fill-mode': 'forwards'});
                sys.setCookie("show_menu", false);
            } else {
                if (menu.width() < 100) {
                    menu.animate({width: "129px"}, 300);
                    $("main").animate({paddingLeft: "130px"}, 300);
                    $(".menu-in-out img").css({'animation': 'rotate-180 0.3s', 'animation-fill-mode': 'forwards'});
                    sys.setCookie("show_menu", true);
                } else {
                    menu.animate({width: "39px"}, 300);
                    $("main").animate({paddingLeft: "40px"}, 300);
                    $(".menu-in-out img").css({'animation': 'rotate180 0.3s', 'animation-fill-mode': 'forwards'});
                    sys.setCookie("show_menu", false);
                }
            }
        },
        /*购物车控制*/
        cartCtrl: function (tid, mark) {
            if (customer.do.ajaxBusy()) {
                return;
            } else {
                customer.do.ajaxLock();
            }
            $.ajax({
                type: "post",
                url: customer.api_url,
                data: {
                    act: "cart_ctrl",
                    client: customer.client,
                    tid: tid,
                    mark: mark
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg);
                        customer.load.CartList();
                        $("#cart-num").animate({
                            height: "28px",
                            width: "28px",
                            lineHeight: "24px",
                            fontSize: "18px"
                        }, 100);
                        $("#cart-num").animate({
                            height: "24px",
                            width: "24px",
                            lineHeight: "20px",
                            fontSize: "14px"
                        }, 100);
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应cartCtrl，" + textStatus);
                }
            });
        },

    },
    /*显示详情*/
    show: {
        ticket: function (tid) {
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "ticket_detail",
                    tid: tid
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        let slideHtml = sys.mkSlide(o.data.pic_group);
                        let secHtml = "",mainHtml = "",infoHtml = "";
                        if (o.data.tic_type == "3"){
                            secHtml = o.data.tic_sec.map(customer.view.ticSec).reduce(function (a,b) {
                                return a + b;
                            });
                            secHtml = `此票券包含以下子票券：` + secHtml;
                            infoHtml = `限时售卖<br/>
                                        有效时间及类型请查看子票券<br/>`;
                        }else if (o.data.tic_type == "2"){
                            infoHtml = `购买后${o.data.valid_days}天内有效<br/>
                                        不限次数，每次限1人（${o.data.type}）<br/>`;
                        }else if (o.data.tic_type == "1"){
                            infoHtml = `${o.data.begin_time} 至 ${o.data.end_time}<br/>
                                        期间可用 ${o.data.times} 次（${o.data.type}）<br/>`;
                        }

                        mainHtml = `
                        <div class="card-list" style="font-family: '宋体', serif">
                            <div class="swiper"> 
                                ${slideHtml}
                            </div>
                            <div class="main-title">
                                <span class="badge badge-info">套票</span> 
                                ${o.data.title}
                            </div>
                            <div class="main-ticket-info">
                                <div style="position:absolute;bottom:35px;right:10px;color: #8c8c8c;font-size: 14px;line-height: 20px">原价 <del>￥${o.data.orig_price}</del></div>
                                <div style="position:absolute;bottom:5px;right:10px;color: #F00;font-size: 28px;line-height: 30px">￥${o.data.price}</div>
                                ${infoHtml}
                                仅剩 ${o.data.stock} 份<br>
                            </div>
                            ${secHtml}
                            <div class="main-ticket-detail">
                                ${o.data.detail}
                            </div>
                        </div>`;
                        Frame("我的票券", mainHtml);
                    }
                }
            });
        },
        MyTic: function (KEY) {
            $.ajax({
                url: customer.api_url,
                data: {
                    act: "my_tic_detail",
                    client:customer.client,
                    ticket_KEY: KEY
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        let secHtml="",mainHtml="";
                        if (o.data.tic_type == "3"){
                            secHtml = o.data.my_tic_sec.map(customer.view.myTicSec).reduce(function (a,b) {
                                return a + b;
                            });
                            secHtml = `
                                <div class="main-ticket-detail">
                                    此套餐已包含以下票券各1张：
                                </div>` + secHtml;
                        }

                        mainHtml = `
                        <div class="ticket-detail">
                            <div style="padding:5px 0;background-color: #FFF"><div id="qrcode"></div></div>
                            <div class="main-ticket-detail" style="text-align: center;line-height: 28px">
                                ${o.data.title}<br/>
                                券码：${o.data.ticket_KEY}<br/>
                                购买日期：${o.data.create_time}<br/>
                                可用日期：${o.data.begin_time}<br/>
                                失效日期：${o.data.end_time}<br/>
                            </div>
                            ${secHtml}
                        </div>`;
                        Frame("我的票券", mainHtml);
                        let qrcode = new QRCode("qrcode");
                        qrcode.makeCode(KEY + o.data.rnd_key);
                    }
                }
            });
        },
    },
    /*customer显示模板*/
    view:{
        ticketView:function (data) {
            if (data.tic_type == "3"){
                let childHtml = "";
                for (let child of data.ticket_child){
                     childHtml += customer.view.ticketView(child);
                }

            }
        },
        ticSec:function(data){
            let ticketInfo;
            if (data.tic_type == "1") {
                ticketInfo = `${data.begin_time} 至 ${data.end_time}<br>
                        期间可用 ${data.times} 次（${data.type}）<br>`;
            }else{
                ticketInfo = `购买后 ${data.valid_days} 天内有效<br/>
                        单人不限次数（${data.type}）<br/>`;
            }
            let res = `
                <div class="my-card">
                    <div class="ticket-img" style="width: 100px;height: 100px">
                        <img style="width: 100%;height: 100%" src="pictures/ticket/${data.tid}/index.jpg">
                    </div>
                    <div class="ticket-title">
                         ${data.detail}
                    </div>
                    <div class="ticket-info">
                        ${ticketInfo}
                        <b style="color: red;font-size: 18px">￥${data.price}</b>
                    </div>
                </div>`;
            return res;
        },
        myTicSec:function (data) {
            let stateImg = customer.sys.getStateImg(data.state_name);
            let res = `<div class="my-card">
                <div class="card-left">
                    <div class="card-left-i">
                        <img src="pictures/ticket/${data.tid}/index.jpg"/>
                    </div>
                    ${data.type}
                </div>
                <div class="card-text" onclick="go('?file=ticketDetail&ticket_key=$card[ticket_KEY]')">
                    ${data.title}<br/>
                    券码：${data.ticket_KEY}<br/>
                    失效日期：${data.end_time}
                </div>
                <div class="card-right">
                    ${stateImg}
                </div>
            </div>`;
            return res;
        }
    },
    /*非公共系统方法*/
    sys:{
        ticCtDwn:function (second,key) {
            t.rnd_sec = second;
            t.rnd_sec_flag = setInterval(function() {
                if($(".body-frame").is(":visible")){
                    if (t.rnd_sec > 1){
                        t.rnd_sec -= 1;
                        $(".rnd-sec").html(t.rnd_sec);
                    }else{
                        warn("随机码已经失效！即将刷新！");
                        setTimeout(function() {
                            view_ticket(key);
                        },1000);
                        clearInterval(t.rnd_sec_flag);
                    }
                }else{
                    clearInterval(t.rnd_sec_flag);
                }
            },1000);
        },
        getStateImg:function(state_name){
            let state_png;
            switch (state_name){
                case "已使用":
                    state_png = "<img src='image/state-used.png'>";
                    break;
                case "已退票":
                    state_png = "<img src='image/state-refund.png'>";
                    break;
                case "已取消":
                    state_png = "<img src='image/state-cancel.png'>";
                    break;
                case "已过期":
                    state_png = "<img src='image/state-over.png'>";
                    break;
                case "已删除":
                    state_png = "<img src='image/state-del.png'>";
                    break;
                default:
                    state_png = "";
                    break;
            }
            return state_png;
        },
        mkPagination(p, pages) {
            //最多显示多少个页码
            let _pageNum = 7;
            let rows = sys.getCookie("rows");
            //当前页面小于1 则为1
            p = p * 1;
            if (typeof p !== "number" || p <= 0) {
                p = 1;
            }
            //当前页大于总页数 则为总页数
            if (p > pages) {
                p = pages;
            }
            //计算开始页
            let _start = p - Math.floor(_pageNum / 2);
            if (_start < 1) {
                _start = 1
            }
            //计算结束页
            let _end = p + Math.floor(_pageNum / 2);
            if (_end > pages) {
                _end = pages;
            }
            //当前显示的页码个数不够最大页码数，在进行左右调整
            let _curPageNum = _end - _start + 1;
            //左调整
            if (_curPageNum < _pageNum && _start > 1) {
                _start = _start - (_pageNum - _curPageNum);
                if (_start < 1) {
                    _start = 1
                }
                _curPageNum = _end - _start + 1;
            }
            //右边调整
            if (_curPageNum < _pageNum && _end < pages) {
                _end = _end + (_pageNum - _curPageNum);
                if (_end > pages) {
                    _end = pages;
                }
            }
            let _pageHtml = '<ul>';
            if (p > 1) {
                _pageHtml += `<li onclick="customer.do.switchPage(customer.page,1,${rows})">|<<</li>`;
                _pageHtml += `<li onclick="customer.do.switchPage(customer.page,${p - 1},${rows})">上页</li>`;
            }
            for (let i = _start; i <= _end; i++) {
                if (i === p) {
                    _pageHtml += `<li class="active">${i}</li>`;
                } else {
                    _pageHtml += `<li onclick="customer.do.switchPage(customer.page,${i},${rows})">${i}</li>`;
                }
            }
            if (p < _end) {
                _pageHtml += `<li onclick="customer.do.switchPage(customer.page,${p + 1},${rows})">下页</li>`;
                _pageHtml += `<li onclick="customer.do.switchPage(customer.page,${pages},${rows})">>>|</li>`;
            }
            _pageHtml += `
            <li style="cursor: default;background-color: #FFF;color: #363636">
                每页显示 <input class="rows-number" type="number" value="${sys.getCookie('rows')}" style="height:24px;width: 50px;text-align: center"/> 
                条 <button onclick="sys.setCookie('rows',$('.rows-number').val());window.location.reload();" class="btn btn-info btn-xs" style="vertical-align: 1px">保存</button>
            </li>`;
            _pageHtml += '</ul>';
            return _pageHtml;
        }
    }

};