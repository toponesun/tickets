"use strict";
let conductor = {
    /*通用api地址*/
    api_url: "api/index.php",
    /*默认端口，conductor内不建议修改*/
    client: "conductor",
    /*默认页面*/
    page: "ticket",
    /*默认页面名称*/
    page_name: "售票",
    /*常量，系统设置的title后缀*/
    TITLE_SUFFIX: "",
    /*常量，系统设置的页脚文字*/
    FOOTER_TEXT: "",
    ROWS_PER_PAGE: 5,

    cashierCountDown: "",

    //加载类
    /*加载系统设置*/
    load: {
        SysSet: function () {
            $.ajax({
                url: conductor.api_url,
                data: {
                    act: "sys_set"
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        alert("获取系统设置失败！");
                    } else {
                        conductor.TITLE_SUFFIX = o.data.TITLE_SUFFIX;
                        conductor.FOOTER_TEXT = o.data.FOOTER_TEXT;
                    }
                },
                error: function () {
                    alert("服务器未响应loadSysSet");
                }
            });
        },
        /*加载用户信息*/
        UserInfo: function () {
            let isLogin = false;
            $.ajax({
                async :false,
                url: conductor.api_url,
                data: {
                    act: "user_info",
                    client: conductor.client
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn("您尚未登录,现在带您到登录页面！");
                        setTimeout(function () {
                            go("login.html");
                        },1000);
                    } else {
                        isLogin = true;
                        $(".nickname").html(o.data.nickname);
                        $(".money").html(o.data.money);
                    }
                },
                error: function () {
                    alert("服务器未响应loadUserInfo");
                }
            });
            return isLogin;
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
                url: conductor.api_url,
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
                    if (o.code === 0) {
                        alert(o.msg);
                    } else {
                        $("#ticket-table table tbody").html("");
                        if (typeof p * 1 !== "number" || p * 1 < 1) {
                            rows = conductor.p;
                        }
                        if (typeof rows * 1 !== "number" || rows * 1 < 1) {
                            rows = conductor.ROWS_PER_PAGE;
                        }
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
                            //加入表格式票券显示
                            $("#ticket-table table").append(
                                `<tr>
                                <td>${i}</td>
                                <td>
                                    <img src="pictures/ticket/${row.tid}/index.jpg" onerror="$(this).attr('src','pictures/scenic/default.jpg');return false;" width="40px" height="40px">
                                </td>
                                <td>${row.title}</td>
                                <td>${row.tic_type}</td>
                                <td>${row.type}</td>
                                <td>${row.scenic}</td>
                                <td>${row.begin_end_time}</td>
                                <td>${row.price}</td>
                                <td>${row.times}</td>
                                <td>${row.city}</td>
                                <td>${row.stock}</td>
                                <td> 
                                    <img src="image/cart-small.png" onclick="conductor.do.cartCtrl('${row.tid}','+1')" style="width: 30px;cursor: pointer;"/>
                                </td>
                            </tr>`);
                            //加入分页机制
                            $(".pagination").html("");
                            $("#ticket-table .pagination").html(conductor.sys.mkPagination(p, o.data.pages));

                        }
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载票券筛选条件*/
        TicketTerm: function () {
            $.ajax({
                url: conductor.api_url,
                data: {
                    act: "ticket_term"
                },
                dataType: "json",
                success: function (o) {
                    if (o.code == 0) {
                        alert(o.msg);
                    } else {
                        $(".auto-term").html("");
                        for (let row of o.data) {
                            let options = "";
                            for (let key in row.value) {
                                options += `<option value="${key}">${row.value[key]}</option>`;
                            }
                            $(".auto-term").append(
                                `<select name="${row.name}" class="form-control mr-1 mb-1" style="width: 120px">
                        <option value="">${row.title}</option>
                        ${options}
                    </select>`);
                        }
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载订单列表*/
        OrderList: function (p, rows) {
            $.ajax({
                url: conductor.api_url,
                data: {
                    act: "order_list",
                    client: conductor.client,
                    p: p,
                    rows: rows
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        $("#order-table table tbody").html("");
                        if (typeof p * 1 !== "number" || p * 1 < 1) {
                            rows = conductor.p;
                        }
                        if (typeof rows * 1 !== "number" || rows * 1 < 1) {
                            rows = conductor.ROWS_PER_PAGE;
                        }
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
                                        <button type="button" class="btn btn-sm btn-info" onclick="sys.setCookie('this_oid','${row.oid}',3600);conductor.do.switchPage('cashier')">
                                            支付
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="ask('确定要取消这个订单么？',function() {
                                            conductor.do.refundOrder('${row.oid}');
                                        })">
                                            取消
                                        </button>`;
                                    } else {
                                        row.state_name += " (正在取消)";
                                        order_btn = `
                                        <button type="button" class="btn btn-sm btn-danger" onclick="ask('确定要删除这个订单么？',function() {
                                            conductor.do.delOrder('${row.oid}');
                                        })">
                                            删除
                                        </button>`;
                                    }
                                    break;
                                case "1":
                                    order_btn = `
                                <button type="button" class="btn btn-sm btn-warning" onclick="ask('确定要退掉这个订单么？',function() {
                                            conductor.do.refundOrder('${row.oid}');
                                        })">
                                    退票
                                </button>`;
                                    break;
                                default:
                                    if (row.visibility === "1") {
                                        order_btn = `
                                        <button type="button" class="btn btn-sm btn-danger" onclick="ask('确定要删除这个订单么？',function() {
                                            conductor.do.delOrder('${row.oid}');
                                        })">
                                            删除
                                        </button>`;
                                    } else {
                                        order_btn = `
                                        <button type="button" class="btn btn-sm btn-info" onclick="ask('确定要还原这个订单么？',function() {
                                            conductor.do.delOrder('${row.oid}');
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
                    $("#order-table .pagination").html(conductor.sys.mkPagination(p, o.data.pages));
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载已售出的票券列表*/
        SoldTicList: function (p, rows) {
            $.ajax({
                url: conductor.api_url,
                data: {
                    act: "my_tic_list",
                    client:conductor.client,
                    p: p,
                    rows: rows
                },
                dataType: "json",
                success: function (o) {
                    $("#sold-tic-table table tbody").html("");
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        if (typeof (p * 1) !== "number" || p * 1 < 1) {
                            p = 1;
                        }
                        if (typeof (rows * 1) !== "number" || rows * 1 < 1) {
                            rows = conductor.ROWS_PER_PAGE;
                        }
                        let i = (p * 1 - 1) * rows;

                        for (let row of o.data.list) {
                            i++;
                            let bgGray;
                            if (row.state == 6) {
                                bgGray = "style='background-color:#CCC'";
                            }
                            //加入表格式票券显示
                            $("#sold-tic-table table tbody").append(
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
                                <td ${bgGray}>${row.state_name}</td>
                                <td>${row.create_time}</td>
                                <td> 
                                    <button class="btn btn-xs btn-info" onclick="view_ticket('10ZZ6Juz6VFVZN');">
                                        展开
                                    </button>
                                    <button class="btn btn-xs btn-warning" onclick="sys.setCookie('this_ticket_KEY','${row.ticket_KEY}',3600);conductor.do.switchPage('print');">
                                        打印
                                    </button>
                                    <button class="btn btn-xs btn-danger" onclick="ask('作废后票券将无法使用！请确认操作！',function() {conductor.do.invalidTic('${row.ticket_KEY}');})">
                                        作废
                                    </button>
                                </td>
                            </tr>`);
                        }
                        //加入分页机制
                        $(".pagination").html("");
                        $("#sold-tic-table .pagination").html(conductor.sys.mkPagination(p, o.data.pages));

                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载购物车列表*/
        CartList: function () {
            $.ajax({
                url: conductor.api_url,
                data: {
                    act: "cart_list",
                    client:conductor.client
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        $(".cart-list table tbody").html("");
                        $(".final-num").html(o.data.total.final.num);
                        $(".final-price").html(o.data.total.final.sale_price);
                        //e(o.data.total);//return;
                        for (let sale in o.data.cart) {
                            let i = 0;
                            let sale_arr = o.data.cart[sale];
                            for (let good in sale_arr) {
                                let goods = sale_arr[good];
                                let options = sys.mkOptions(goods.num, 99);
                                i++;
                                $(".cart-list table tbody").append(`
                                <tr>
                                    <td> 
                                        ${i}
                                    </td>
                                    <td> 
                                        ${goods.title}
                                    </td>
                                    <td>
                                    <div style="height: 24px;float: left;margin-right: 3px">
                                        <img src="image/-.png" style="cursor:pointer;vertical-align:top;height: 100%"  onclick="conductor.do.cartCtrl('${goods.tid}','-1')"/>
                                    </div>
                                    <div style="height: 24px;float: left;margin-right: 3px;">
                                        <select style="width:50px;font-size: 20px;font-family: '黑体',serif" onchange="conductor.do.cartCtrl('${goods.tid}','~'+$(this).val())">
                                            ${options}
                                        </select>         
                                    </div>
                                    <div style="height: 24px;float: left;margin-right: 5px">
                                        <img src="image/+.png" style="cursor:pointer;vertical-align:top;height: 100%" onclick="conductor.do.cartCtrl('${goods.tid}','+1')"/>
                                    </div>
                                    
                                    </td>
                                    <td>￥${goods.price}</td>
                                    <td>
                                        <img width="25px" src="image/delete.png" onclick="ask('您确定要删除吗？',function(){conductor.do.cartCtrl('${goods.tid}',0)})"/>
                                    </td>
                                </tr>`);
                            }
                        }
                    }
                },
                error: function () {
                    alert("服务器未响应");
                }
            });
        },
        /*加载订单确认页面列表*/
        ConfirmList: function () {
            $.ajax({
                url: conductor.api_url,
                data: {
                    act: "confirm_list",
                    client:conductor.client
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
                url: conductor.api_url,
                data: {
                    act: "order_detail",
                    client:conductor.client,
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

                            $(".receivable").val(o.data.price);
                            $(".payable").val("");
                            $(".odd-change").val("");


                            let createTime = o.data.create_time;
                            createTime = createTime.substring(0, 19);
                            createTime = createTime.replace(/-/g, '/');
                            let nowTimeStamp = parseInt(new Date().getTime() / 1000);
                            let orderTimeStamp = (new Date(createTime).getTime()) / 1000;
                            let order_time = orderTimeStamp + o.data.order_live_time * 1 - nowTimeStamp;
                            let order_min = 0, order_sec = 0;
                            if (order_time > 0) {
                                clearTimeout(conductor.cashierCountDown);
                                conductor.cashierCountDown = setInterval(function () {
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
                        conductor.do.switchPage('order');
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*加载打印页面*/
        PrintInfo: function () {
            let ticket_KEY = sys.getCookie('this_ticket_KEY');
            if (!ticket_KEY) {
                warn("请先选择要打印的票券");
                $(".menu-of-my-tic").click();
                return;
            }
            $.ajax({
                url: conductor.api_url,
                data: {
                    act: "my_tic_detail",
                    client:conductor.client,
                    ticket_KEY:ticket_KEY
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        $(".print-table").html("");
                        $("#qrcode").html("");
                        $(".print-table").append(`
                            <tr class="print-title">
                                <td class="print-key">标题</td>
                                <td>${o.data.title}</td>
                            </tr>
                            <tr class="print-KEY"> 
                                <td class="print-key">券码</td>
                                <td>${ticket_KEY}</td>
                            </tr>
                            <tr class="print-date-range"> 
                                <td class="print-key">有效时间</td>
                                <td>${o.data.begin_time}(起)<br/>${o.data.end_time}(止)</td>
                            </tr>
                            <tr class="print-date">
                                <td class="print-key">打印时间</td>
                                <td>${sys.time()}(售)</td>
                            </tr>`);
                        let qrcode = new QRCode("qrcode");
                        qrcode.makeCode(ticket_KEY);
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
            if (!conductor.load.UserInfo()) {
                return;
            }
            /*page不存在则获取cookie，还不存在则默认*/
            if (!page) {
                page = sys.getCookie("page");
                if (!page) {
                    page = conductor.page;
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
                    rows = conductor.ROWS_PER_PAGE;
                }
            }
            /*执行非cookie新页面则保存新页面名称，p=1*/
            if (sys.getCookie("page") && page !== sys.getCookie("page")) {
                sys.setCookie("page",page);
                p = 1;
            }
            /*隐藏所有主页面*/
            $("main").hide();
            switch (page) {
                case "ticket":
                    conductor.load.TicketList(p, rows);
                    $(".page-of-ticket").show();
                    conductor.page_name = "售票中心";
                    break;
                case "order":
                    conductor.load.OrderList(p, rows);
                    $(".page-of-order").show();
                    conductor.page_name = "订单管理";
                    break;
                case "soldTic":
                    conductor.load.SoldTicList(p, rows);
                    $(".page-of-sold-tic").show();
                    conductor.page_name = "票券管理";
                    break;
                case "print":
                    conductor.load.PrintInfo();
                    $(".page-of-print").show();
                    conductor.page_name = "打印测试";
                    break;
                case "confirm":
                    conductor.load.ConfirmList();
                    $(".page-of-confirm").show();
                    conductor.page_name = "确认订单";
                    break;
                case "cashier":
                    conductor.load.CashierInfo();
                    $(".page-of-cashier").show();
                    conductor.page_name = "收银台";
                    $(".payable").focus();
                    break;
                default:
                    return;
            }
            /*当前页名称*/
            conductor.page = page;
            /*修正页面名称*/
            $(".page-name").html(conductor.page_name);
            /*修正网页标题*/
            $(document).attr("title", conductor.page_name + conductor.TITLE_SUFFIX);
        },
        /*请求提交订单*/
        createOrder: function () {
            if (conductor.do.ajaxBusy()) {
                return;
            } else {
                conductor.do.ajaxLock();
            }
            $.ajax({
                type: "post",
                url: conductor.api_url,
                data: {
                    act: "create_order",
                    client:conductor.client,
                    cart: [],
                    buy_way: "pc"
                },
                dataType: "json",
                success: function (o) {

                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg + o.data.oid);
                        conductor.load.CartList();
                        conductor.load.ConfirmList();
                        sys.setCookie("this_oid", o.data.oid, 3600);
                        conductor.do.switchPage("cashier");
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    e(XMLHttpRequest);
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*请求取消订单*/
        refundOrder: function (oid) {
            if (conductor.do.ajaxBusy()) {
                return;
            } else {
                conductor.do.ajaxLock();
            }
            $.ajax({
                type: "post",
                url: conductor.api_url,
                data: {
                    act: "refund_order",
                    oid: oid,
                    client:conductor.client
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg);
                        conductor.do.switchPage();
                    }
                },
                error: function (XMLHttpRequest, textStatus) {
                    alert("服务器未响应，" + textStatus);
                }
            });
        },
        /*请求删除（隐藏）订单*/
        delOrder: function (oid) {
            if (conductor.do.ajaxBusy()) {
                return;
            } else {
                conductor.do.ajaxLock();
            }
            $.ajax({
                type: "post",
                url: conductor.api_url,
                data: {
                    act: "del_order",
                    oid: oid
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg);
                        conductor.do.switchPage();
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
            if (conductor.do.ajaxBusy()) {
                return;
            } else {
                conductor.do.ajaxLock();
            }
            if (!(oid = sys.getCookie("this_oid"))) {
                warn("支付超时，请重新进入支付页面");
                return;
            }
            $.ajax({
                type: "post",
                url: conductor.api_url,
                data: {
                    act: "pay_order",
                    client:conductor.client,
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
                        }, 1000);
                    }
                },
                error: function () {
                    warn("服务器未响应支付事件");
                }
            });
        },

        /*请求作废票券*/
        invalidTic: function (ticket_KEY) {
            if (conductor.do.ajaxBusy()) {
                return;
            } else {
                conductor.do.ajaxLock();
            }
            $.ajax({
                type: "post",
                url: conductor.api_url,
                data: {
                    act: "invalid_tic",
                    client:conductor.client,
                    ticket_KEY: ticket_KEY
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg);
                        setTimeout(function () {
                            $(".menu-of-soldTic").click();
                        }, 1000);
                    }
                },
                error: function () {
                    warn("服务器未响应作废票券事件");
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
                $(".menu-in-out img").css({'animation': 'rotate180 0.3s', 'animation-fill-mode': 'forwards'});
                sys.setCookie("show_menu", true);
            } else if (display === "hide") {
                menu.animate({width: "39px"}, 300);
                $("main").animate({paddingLeft: "40px"}, 300);
                $(".menu-in-out img").css({'animation': 'rotate-180 0.3s', 'animation-fill-mode': 'forwards'});
                sys.setCookie("show_menu", false);
            } else {
                if (menu.width() < 100) {
                    menu.animate({width: "129px"}, 300);
                    $("main").animate({paddingLeft: "130px"}, 300);
                    $(".menu-in-out img").css({'animation': 'rotate180 0.3s', 'animation-fill-mode': 'forwards'});
                    sys.setCookie("show_menu", true);
                } else {
                    menu.animate({width: "39px"}, 300);
                    $("main").animate({paddingLeft: "40px"}, 300);
                    $(".menu-in-out img").css({'animation': 'rotate-180 0.3s', 'animation-fill-mode': 'forwards'});
                    sys.setCookie("show_menu", false);
                }
            }
        },
        /*购物车控制*/
        cartCtrl: function (tid, mark) {
            if (conductor.do.ajaxBusy()) {
                return;
            } else {
                conductor.do.ajaxLock();
            }
            $.ajax({
                type: "post",
                url: conductor.api_url,
                data: {
                    act: "cart_ctrl",
                    client:conductor.client,
                    tid: tid,
                    mark: mark
                },
                dataType: "json",
                success: function (o) {
                    if (o.code === 0) {
                        warn(o.msg);
                    } else {
                        msg(o.msg);
                        conductor.load.CartList();
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

    /*不便放入sys的常用方法*/
    sys: {
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
                _pageHtml += `<li onclick="conductor.do.switchPage(conductor.page,1,${rows})">|<<</li>`;
                _pageHtml += `<li onclick="conductor.do.switchPage(conductor.page,${p - 1},${rows})">上页</li>`;
            }
            for (let i = _start; i <= _end; i++) {
                if (i === p) {
                    _pageHtml += `<li class="active">${i}</li>`;
                } else {
                    _pageHtml += `<li onclick="conductor.do.switchPage(conductor.page,${i},${rows})">${i}</li>`;
                }
            }
            if (p < _end) {
                _pageHtml += `<li onclick="conductor.do.switchPage(conductor.page,${p + 1},${rows})">下页</li>`;
                _pageHtml += `<li onclick="conductor.do.switchPage(conductor.page,${pages},${rows})">>>|</li>`;
            }
            _pageHtml += `
            <li style="cursor: default;background-color: #FFF;color: #363636">
                每页显示 <input class="rows-number" type="number" value="${sys.getCookie('rows')}" style="height:24px;width: 50px;text-align: center"/> 
                条 <button onclick="sys.setCookie('rows',$('.rows-number').val());window.location.reload();" class="btn btn-info btn-xs" style="vertical-align: 1px">保存</button>
            </li>`;
            _pageHtml += '</ul>';
            return _pageHtml;
        }
    },



};


