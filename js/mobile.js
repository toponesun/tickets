"use strict";
let t = {
    no_response:"服务器未响应！"
};
let ajax_url = "app/customer/controller/Ajax.php";

function to_top() {
    $(".card-list").animate({scrollTop:"0"},300);
}

function delAllHis(){
    $.post(ajax_url,
        {
            action_key:"hisCtrl"
        },
        function(result){
            $(".body-main").html(result);
            msg("浏览记录已清空");
        });
}

function changeGoodsByNum(tid,act){
    var scrollTop = $(".card-list").scrollTop();
    $.post(ajax_url,
        {
            tid:tid,
            act:act,
            action_key:"m_cartControl"
        },
        function(result){
            $(".body-main").html(result);
            $(".card-list").scrollTop(scrollTop);
            msg("操作成功！");
        })
}

function addToCart(tid){
    $.ajax({
        type:"post",
        url:"api/customer/cart_ctrl.php",
        data:{1:1},
        dataType:"json",
        success:function (o) {
            if (o.code === 0){
                warn(o.msg);
            }else{
                //warn(o.msg);
            }
        },
        error:function () {
            warn(t.no_response);
        }
    });
    return;


    $.post("app/",
        {
            tid:tid,
            action_key:"addToCart"
        },
        function(result){
            msg("加入购物车成功");
            var div = $("#cart-num");
            if (result<=99){
                var divRadius = (18 + result/50) + "px";
                var divLnHt =  (16 + result/50) + "px";
            }else{
                result = "99+";
                var divRadius = "20px";
                var divLnHt = "18px";
            }
            div.html(result);
            div.animate({height:"22px",width:"22px",lineHeight:"20px",fontSize:"18px"},100);
            div.animate({height:divRadius,width:divRadius,lineHeight:divLnHt,fontSize:"10px"},100);
    });
}

function area_show(area){
    var div1 = $('.cover-10');
    var div2 = $('.select-area');
    var div3 = $('.search-area');
    if(area == "select-area"){
        div2.slideDown(200);
        div1.slideDown(200);
    }else if(area == "search-area") {
        div3.slideDown(200);
        div1.slideDown(200);
    }else{
        div1.slideUp(200);
        div2.slideUp(200);
        div3.slideUp(200);
    }
}

function search() {
    var txt = $('#search').val();
    if(txt){
        go("?file=tickets&title=" + txt);
    }else{
        warn("查找内容不允许为空！");
    }
}

function ticCtDwn(second,key) {
    t.rnd_sec = second;
    t.rnd_sec_flag = setInterval(
        function() {
            if (t.rnd_sec > 1){
                t.rnd_sec -= 1;
                $(".rnd-sec").html(t.rnd_sec);
            }else{
                warn("随机码已经失效！即将刷新！");
                setTimeout(function() {
                    location.reload();
                },1000);
                clearInterval(t.rnd_sec_flag);
            }
        },1000);
}

function payOrder(oid){
    $.post(ajax_url,
        {
            oid:oid,
            action_key:"payOrder"
        },
        function(result){
            msg(result);
            setTimeout(function () {
                window.location.href="?file=myOrders";
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
            },1000)
        });
}

function cancel(oid) {
    $.post(ajax_url,
        {
            oid : oid,
            act : 1,
            action_key : "orderCtrl"
        },
        function(result){
            msg(result);
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
        });
}


function favor(tid) {
    $.ajax(
        {
            type : "post",
            url : ajax_url,
            data : {"tid":tid,"action_key":"addFavor"},
            success : function(result) {
                if(result=="已收藏"){
                    $("#favor-img").attr("src","image/favor-true.png");
                }else{
                    $("#favor-img").attr("src","image/favor-false.png");
                }
                msg(result);
            },
            error : function () {
                msg("js错误");
            }
        }
    );
}