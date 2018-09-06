'use strict';
let t = {};
let ajax_url = "app/customer/controller/Ajax.php";

function menuTgl() {
    let menu = $(".body-left");
    if(menu.width() < 100){
        menu.animate({width:"129px"},300);
        $(".body-main").animate({paddingLeft:"140px"},300);
        $(".menu-in-out img").css({'animation':'rotate180 0.3s','animation-fill-mode': 'forwards'});

    }else{
        menu.animate({width:"39px"},300);
        $(".body-main").animate({paddingLeft:"50px"},300);
        $(".menu-in-out img").css({'animation':'rotate-180 0.3s','animation-fill-mode': 'forwards'});
    }
    $.post(ajax_url,
        {
            action_key : "upShowMenu"
        },function (result) {
            //msg("菜单栏状态修改成功")
        }
    )
}

function cartTgl(){
    var cart_big = $(".body-right");
    var cart_small = $(".cart-ball");
    var div_main = $(".body-main");

    if (cart_big.is(":hidden")){
        div_main.animate({paddingRight:"220px"},200);
    }else{
        div_main.animate({paddingRight: 0}, 200);
    }
    cart_big.toggle(200);
    cart_small.toggle(200);

    $.post(ajax_url,
        {
            action_key : "upShowCart"
        },
        function(result){
            //msg("购物车状态修改成功")
        });
}


//搜索日期
function chk_date(){
    let date = $("#daterange").val();
    date = date.replace(/[ ]/g,"");
    if(date === ""){
        warn("请选择正确的日期！");
    }else{
        location.href = "" + date;
    }
}

//搜索标题中带有关键字的票
function search(file) {
    var title = $("#search-text").val();
    if(title){
        location.href = "?file=" + file + "&title=" + title;
    }else{
        warn("查找内容不允许为空！");
    }
}



function changeGoodsByNum(tid,act){
    var scrollTop = $(".cart-list").scrollTop();
    $.post(ajax_url,
        {
            tid:tid,
            act:act,
            action_key:"cartControl"
        },
        function(result){
            //result = eval('('+result+')');
            result = JSON.parse(result);
            $(".body-right").html(result.html);
            $(".cart-list").scrollTop(scrollTop);
            $("#cart-num").html(result.num);
            msg("操作成功！");
            $("#cart-num").animate({height:"28px",width:"28px",lineHeight:"24px",fontSize:"18px"},100);
            $("#cart-num").animate({height:"24px",width:"24px",lineHeight:"20px",fontSize:"14px"},100);
        })
}


function showDetail(tid){
    $.post(ajax_url,
        {
            tid:tid,
            action_key : "getTicketsDetail"
        },
        function(result){
            Frame("票券介绍",result);
        });
}
function view_order(oid){
    $.post(ajax_url,
        {
            oid : oid,
            action_key : "getOrderDetail"
        },
        function(result){
            Frame("订单详情",result);
        });
}
function view_ticket(ticket_key){
    clearInterval(t.rnd_sec_flag);
    $.post(ajax_url,
        {
            ticket_key : ticket_key,
            action_key : "getMyTicDetail"
        },
        function(result){
            Frame("票券详情",result);
            let qrcode = new QRCode("qrcode");
            let rnd_key = $(".rnd-key").text();
            qrcode.makeCode(ticket_key + rnd_key);
        });
}

function ticCtDwn(second,key) {
    t.rnd_sec = second;
    t.rnd_sec_flag = setInterval(
        function() {
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
}

function addToCart(tid){
    var divRadius;
    var divLnHt;
    $.post(ajax_url,
        {
            tid:tid,
            action_key:"addToCart"
        },
        function(result){
            msg("加入购物车成功");
            var div = $("#cart-num");
            if (result<=99){
                divRadius = (18 + result/50) + "px";
                divLnHt =  (16 + result/50) + "px";
            }else{
                result = "99+";
                divRadius = "20px";
                divLnHt = "18px";
            }
            div.html(result);
            div.animate({height:"22px",width:"22px",lineHeight:"20px",fontSize:"18px"},100);
            div.animate({height:divRadius,width:divRadius,lineHeight:divLnHt,fontSize:"10px"},100);
        });
}

function changeGoodsById(uid,tid,goods_id){
    var act = "~" + document.getElementById(goods_id).value;
    changeGoodsByNum(uid,tid,act)
}


function payOrder(oid){
    var payment = $(".payment-area div input[name='payment']:checked").val();
    switch (payment){
        case "balance":
            break;
        case "wechat":
            warn("微信支付尚未开通，敬请谅解");
            return;
        case "alipay":
            warn("支付宝尚未接入，敬请谅解");
            return;
        default:
            warn("请选择支付方式！");
            return;
    }

    $.post(ajax_url,
        {
            oid:oid,
            action_key:"payOrder"
        },
        function(result){
            msg(result);
            setTimeout(function () {
                go("?file=myOrders");
            },1000)
        });
}


function refund(oid){
    $.post(ajax_url,
        {
            oid : oid,
            action_key : 'checkRefund'
        },
        function(result){
            if(result){
                ask("确定要退票吗？此订单下所有票券将失效！",'go("?file=refund&oid=' + oid +'")');
            }else{
                warn("此订单不符合退票条件，无法完成退票！");
            }
        });
}

function refund_true(oid){
    $.post(ajax_url,
        {
            oid : oid,
            act : 1,
            action_key : "orderCtrl"
        },
        function(result){
            msg(result);
            setTimeout(function () {
                go("?file=myOrders");
            },1500)
        });
}

function delOrder(oid) {
    $.post(ajax_url,
        {
            oid : oid,
            act : 2,
            action_key : "orderCtrl"
        },
        function(result){
            msg(result);
            setTimeout(function () {
                window.location.reload();
            },1500)
        });
}

function recover(oid) {
    $.post(ajax_url,
        {
            oid : oid,
            act : 3,
            action_key : "orderCtrl"
        },
        function(result){
            msg(result);
            setTimeout(function () {
                window.location.reload();
            },1500);
        });
}

function move(e,el) {
    var obig = document.getElementById("box");
    var osmall = el;
    e = e || window.event;

    /*用于保存小的div拖拽前的坐标*/
    osmall.startX = e.clientX - osmall.offsetLeft;
    osmall.startY = e.clientY - osmall.offsetTop;
    /*鼠标的移动事件*/
    document.onmousemove = function(e) {
        e = e || window.event;
        osmall.style.left = e.clientX - osmall.startX + "px";
        osmall.style.top = e.clientY - osmall.startY + "px";
        /*对于大的DIV四个边界的判断*/

        if (e.clientX - osmall.startX <= 0) {
            osmall.style.left = 0 + "px";
        }
        if (e.clientY - osmall.startY <= 0) {
            osmall.style.top = 0 + "px";
        }
        if (e.clientX - osmall.startX >= obig.clientWidth-osmall.clientWidth) {
            osmall.style.left = obig.clientWidth-osmall.clientWidth + "px";
        }
        if (e.clientY - osmall.startY >= obig.clientHeight-osmall.clientHeight) {
            osmall.style.top =obig.clientHeight-osmall.clientHeight+ "px";
        }
    };
    /*鼠标的抬起事件,终止拖动*/
    document.onmouseup = function() {
        document.onmousemove = null;
        document.onmouseup = null;
    };
}

function mkLayDate(){
    laydate.render({
        elem: '#date-range'
        ,range: true
        ,format: 'yyyy/MM/dd'
    });
}
function getWebSet() {
    $.get("app/customer/controller/Request.php",
        {
            "need" : "get_web_set"
        },function (r) {
            var res = JSON.parse(r);
            $(document).attr("title",document.title+res[0].title_suffix);
            $(".footer").html(res[0].footer)
        })
}
function getScenicList() {
    $.get("app/customer/controller/Request.php",
        {
            "need" : "get_scenic_list"
        },function (r) {
            var res = JSON.parse(r);

            for(let row of res){
                //console.log(row.spans);
                $(".item-list").append(
                    `<div class="scenic-item">
                <div class="scenic-img">
                    <img src="pictures/scenic/${row.pid}/index.jpg" onerror="$(this).attr('src','pictures/scenic/default.jpg')"/>
                </div>
                <div class="scenic-info">
                    <div class="scenic-name">
                        ${row.name}
                    </div>
                    <div class="scenic-price">
                        <span>¥ <b>${row.avg_pay}</b></span> 人均消费
                    </div>
                    <div class="scenic-href">
                        <a href="#" class="btn btn-warning">抢购</a>
                    </div>
                </div>
            </div>`);
            }
            endLoading();
        }
    )
}
function getTicketsList() {
    $.get("app/customer/controller/Request.php",
        {
            "need" : "get_tickets_list"
        },function (r) {
            var res = JSON.parse(r);

            for(let row of res){
                //console.log(row.spans);
                let tic_type = getTicType(row.tic_type);
                $(".item-list").append(
                    `<div class="tickets-item">
                <div class="tickets-img">
                    <img src="pictures/ticket/${row.tid}/index.jpg" onerror="$(this).attr('src','pictures/ticket/default.jpg')"/>
                </div>
                <div class="tickets-info">
                    <div class="tickets-name">
                        ${row.title}
                    </div>
                    <div class="tickets-type">
                        ${row.type}(${tic_type})
                    </div>
                    <div class="tickets-price">
                        <span>¥ <b>${row.price}</b></span>
                    </div>
                    <div class="tickets-href">
                        <a href="#" class="btn btn-warning">抢购</a>
                    </div>
                </div>
            </div>`);
            }
            endLoading();
        }
    )
}

function getTicType(type) {
    switch (type){
        case "1":
            return "计次";
        case "2":
            return "计时";
        case "3":
            return "套票";
        default:
            return "";
    }
}


function getTicketTerm() {
    $.ajax({
        url: "api/index.php",
        data: {
            act:"ticket_term"
        },
        dataType: 'json',
        success:function (obj) {
            console.log(obj);
            var options;
            $.each(obj,function(){
                options = "";
                $.each(this.value,function (idx,val) {
                    options += `<option value="${idx}">${val}</option>`;
                });
                $(".auto-term").append(
                    `<select name="${this.name}" class="form-control mr-1 mb-1" style="width: 120px">
                        <option value="">${this.title}</option>
                        ${options}
                    </select>`);
            })
        },
        error:function () {
            alert("未响应")
        }
    });
}
function getOrderTerm() {
    $.ajax({
        url: "api/get_order_term.php",
        type: 'GET',
        data: {
        },                    // 上传formdata封装的数据
        dataType: 'JSON',
        cache: false,                      // 不缓存
        processData: false,                // jQuery不要去处理发送的数据
        contentType: false,                // jQuery不要去设置Content-Type请求头
        success:function (obj) {           //成功回调
            var options;
            $.each(obj,function(){
                options = "";
                $.each(this.value,function (idx,val) {
                    options += `<option value="${idx}">${val}</option>`;
                });
                $(".auto-term").append(
                    `<select name="${this.name}" class="form-control mr-1 mb-1" style="width: 120px">
                        <option value="">${this.title}</option>
                        ${options}
                    </select>`);
            })
        }
    });
}

function getMyTicTerm() {
    $.ajax({
        url: "api/get_mytic_term.php",
        type: 'GET',
        async:false,
        data: {
        },                    // 上传formdata封装的数据
        dataType: 'JSON',
        success:function (obj) {           //成功回调
            var options;
            $.each(obj,function(){
                options = "";
                $.each(this.value,function (idx,val) {
                    options += `<option value="${idx}">${val}</option>`;
                });
                $(".auto-term").append(
                    `<select name="${this.name}" class="form-control mr-1 mb-1" style="width: 120px">
                        <option value="">${this.title}</option>
                        ${options}
                    </select>`);
            });
        }
    });
}









