"use strict";
let sys = {
    /*通用api地址*/
    api_url: "api/index.php",
    file:"index",
    file_name:"主页",
    rows_per_page:10,



    logout:function (client) {
        $.ajax({
            type: "get",
            url: sys.api_url,
            data: {
                act: "logout",
                client:client
            },
            dataType: "json",
            success: function (o) {
                warn(o.msg);
                if (o.code === 1) {
                    sys.delCookie("page");
                    sys.delCookie("p");
                    sys.delCookie("rows");
                    setTimeout(function () {
                        go('login.html');
                    },1500);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert("服务器未响应，" + textStatus);
            }
        });
    },

    // 转为unicode 编码
    UniEncode:function (str) {
        let res = [];
        for ( let i=0; i<str.length; i++ ) {
            res[i] = ( "00" + str.charCodeAt(i).toString(16) ).slice(-4);
        }
        return "\\u" + res.join("\\u");
    },
    // 解码
    UniDecode:function (str) {
        str = str.replace(/\\/g, "%");
        return unescape(str);
    },

    //写cookies
    setCookie:function (name,value,second) {
        if (!second){
            //默认cookie有效期30天
            second = 30*24*60*60;
        }
        let exp = new Date();
        exp.setTime(exp.getTime() + second*1000);
        document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
    },
    //读取cookies
    getCookie:function (name)
    {
        let arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
        if(arr=document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    },
    //删除cookies
    delCookie:function (name)
    {
        let exp = new Date();
        exp.setTime(exp.getTime() - 1);
        let cval = sys.getCookie(name);
        if(cval!=null)
            document.cookie= name + "="+cval+";expires="+exp.toGMTString();
    },
    //制作select下的options
    mkOptions:function (num,total) {
        let option = "",options = "";
        if (num * total < 0) {
            return false;
        }
        for (let i = 1; i <= total; i++) {
            if (i == num){
                option = `<option selected>${i}</option>`
            }else{
                option = `<option>${i}</option>`
            }
            options += option;
        }
        return options;
    },
    time:function() {
        let date = new Date();
        let seperator1 = "-";
        let seperator2 = ":";
        let month = date.getMonth() + 1;
        let strDate = date.getDate();
        if (month >= 1 && month <= 9) {
            month = "0" + month;
        }
        if (strDate >= 0 && strDate <= 9) {
            strDate = "0" + strDate;
        }
        let currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
            + " " + date.getHours() + seperator2 + date.getMinutes()
            + seperator2 + date.getSeconds();
        return currentdate;
    },
    mkPagination: function (p,pages,url){
        //最多显示多少个页码
        let _pageNum = 7;
        let rows = sys.getCookie("rows");
        //当前页面小于1 则为1
        p = p * 1 ;
        if (typeof p !== "number" || p <= 0){
            p = 1;
        }
        //当前页大于总页数 则为总页数
        if (p>pages){
            p = pages;
        }
        //计算开始页
        let _start = p - Math.floor(_pageNum/2);
        if (_start < 1) {
            _start = 1
        }
        //计算结束页
        let _end = p + Math.floor(_pageNum/2);
        if (_end > pages){
            _end = pages;
        }
        //当前显示的页码个数不够最大页码数，在进行左右调整
        let _curPageNum = _end - _start + 1;
        //左调整
        if(_curPageNum<_pageNum && _start>1){
            _start = _start - (_pageNum - _curPageNum);
            if (_start < 1) {
                _start = 1
            }
            _curPageNum = _end - _start+1;
        }
        //右边调整
        if(_curPageNum<_pageNum && _end<pages){
            _end = _end + (_pageNum-_curPageNum);
            if (_end > pages){
                _end = pages;
            }
        }
        let _pageHtml = '<ul>';
        if(p>1){
            _pageHtml += `<li onclick="customer.do.switchPage(customer.page,1,${rows})">|<<</li>`;
            _pageHtml += `<li onclick="customer.do.switchPage(customer.page,${p-1},${rows})">上页</li>`;
        }
        for (let i = _start; i <= _end; i++) {
            if(i === p){
                _pageHtml += `<li class="active">${i}</li>`;
            }else{
                _pageHtml += `<li onclick="customer.do.switchPage(customer.page,${i},${rows})">${i}</li>`;
            }
        }
        if(p<_end){
            _pageHtml += `<li onclick="customer.do.switchPage(customer.page,${p+1},${rows})">下页</li>`;
            _pageHtml += `<li onclick="customer.do.switchPage(customer.page,${pages},${rows})">>>|</li>`;
        }
        _pageHtml += '</ul>';
        return _pageHtml;
    },
    //电脑端bootstrap轮播图
    mkSlide:function(pic_group){
        let active = "active",
        indicators_html = "",
        inner_html = "",
        html = "",
        img,
        i = 0;
        for (let pic of pic_group){
            if (typeof pic === "string"){
                img = pic;
                pic = {img:img};
            }
            let title_html = "",onclick_html = "";
            if (pic.title){
                title_html = `<div class='swiper-title'>${pic.title}</div>`;
            }
            if (pic.url){
                onclick_html = `go('${pic.url}');`;
            }
            if (pic.img){
                indicators_html += `
                <li data-target="#slide" data-slide-to="${i}" class="${active}"> </li>
                `;
                inner_html += `
                <div class="carousel-item ${active}">
                    <img src="${pic.img}" onclick="${onclick_html}" style="width: 100%;height: 100%">
                        <div class="carousel-caption">
                            <h3>${title_html}</h3>
                            <p>${title_html}</p>
                        </div>
                </div>`;
                active = "";
                i++;
            }
        }
        if (inner_html){
            html = `
                <div id="slide" class="carousel slide" data-ride="carousel">
                    <!-- 指示符 -->
                    <ul class="carousel-indicators">
                        ${indicators_html}
                    </ul>
                    <!-- 轮播图片 -->
                    <div class="carousel-inner">
                        ${inner_html}
                    </div>
                    <!-- 左右切换按钮 -->
                    <a class="carousel-control-prev" href="#slide" data-slide="prev">
                        <span class="carousel-control-prev-icon"> </span>
                    </a>
                    <a class="carousel-control-next" href="#slide" data-slide="next">
                        <span class="carousel-control-next-icon"> </span>
                    </a>
                </div>`;
        }
        return html;
    },
    //手机端swiper轮播图
    mkSwiper:function (pic_group) {
        let slide_html = "";
        let img;
        for (let pic of pic_group){
            if (typeof pic === "string"){
                img = pic;
                pic = {img:img};
            }
            let title_html = "",onclick_html = "";
            if (pic.title){
                title_html = `<div class='swiper-title'>${pic.title}</div>`;
            }
            if (pic.url){
                onclick_html = `go('${pic.url}');`;
            }
            if (pic.img){
                slide_html += `<div class="swiper-slide" style="width: 100%">
                    <img data-src="${pic.img}" class="swiper-lazy" onclick="${onclick_html}">
                        <div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div>
                        ${title_html}
                    </div>`;
            }
        }
        return `<div class="swiper-container">
                <div class="swiper-wrapper">${slide_html}</div>
                <!-- 如果需要分页器 -->
                <div class="swiper-pagination"></div>
            </div>
            <script>
            let mySwiper = new Swiper ('.swiper-container', {
                lazy:true,
                // 如果需要分页器
                pagination: {
                    el: '.swiper-pagination',
                },
            })
            </script>`;
    },
    /*打印任何部分*/
    easyPrint:function (ele){
        //let tb = $('#'+tableid);
        //let newFrame = "print-frame";
        ele.parent("div").append('<iframe id="print-frame" src="" frameborder="0" hidden></iframe>');
        let printFrame = $('#print-frame');

        let format = function (s, c) {
            return s.replace(/{(\w+)}/g,
                function (m, p) {
                    return c[p];
                });
        };
        let template = '<html>'+
            '<head>'+
            '<style type="text/css">'+
            'table{'+
            'width: 100%;'+
            'border-collapse:collapse;'+
            'background-color: #FFF;'+
            'text-align: center;'+
            '}'+
            'table td {'+
            'border: 0.5px solid #999;'+
            'background-color: #FFF;'+
            'font-size: 14px;'+
            'line-height: 22px;'+
            'color: #333;'+
            '}'+
            'table th{'+
            'color: #FFF;'+
            'background-color: #4B6E96;'+
            'font-size: 16px;'+
            'line-height: 26px;'+
            'min-width: 50px;'+
            'border: 0.5px solid #999;'+
            '}'+
            '</style></head><body><table>{table}</table></body></html>';
        var ctx = {table: tb.html()};
        var newHtml = format(template, ctx);
        printFrame.contents().find("body").html(newHtml);
        printFrame.contents().find(":hidden").show();
        printFrame.contents().find(".no-export").remove();
        document.getElementById(newFrame).contentWindow.print();
    }
};
let flag;

function e(e) {
    console.log(e)
}



//通用侧边栏展开二级菜单代码
$(function () {
    $('.left-item a').on('click',function(){
        if($(this).next('ul').is(':hidden')){
            //未展开
            $('.left-item').children('ul').slideUp(300);
            $(this).next('ul').slideDown(300);
            $(this).parent('li').addClass('active').siblings('.left-item').removeClass('active');
            $(this).parent('li').siblings('li').children('a').children('img').css({'animation':'rotate-90 0.3s','animation-fill-mode': 'forwards'});
            $(this).children('img').css({'animation':'rotate90 0.3s','animation-fill-mode': 'forwards'});
        }else{
            //收缩已展开
            $(this).next('ul').slideUp(300);
            $(this).parent('li').removeClass('active');
            $(this).children('img').css({'animation':'rotate-90 0.3s','animation-fill-mode': 'forwards'});
        }
    });
});

function go(url) {
    window.location.href = url;
}
function warn(info) {
    clearTimeout(flag);
    $(".body-alert-img").html("<img src='image/warn.png'/>");
    $(".body-alert-text").html(info);
    $(".body-alert").show();
    flag = setTimeout(function () {
        $(".body-alert").stop().slideUp(300);
    },1500);
}
function msg(info) {
    $(".body-alert-img").html("<img src='image/yes.png'/>");
    $(".body-alert-text").html(info);
    clearTimeout(flag);
    if ($(".body-alert").is(":hidden")){
        $(".body-alert").slideDown(200);
    }
    flag = setTimeout(function () {
        $(".body-alert").slideUp(300);
    },1200);

}
function ask(msg,yesCallBack,noCallBack) {
    $(".body-ask-img").html("<img src='image/warn.png'/>");
    $(".body-ask-text").html(msg);
    $(".full-gray").show();
    $(".body-ask").show(200);
    // 确定按钮
    if (yesCallBack) {
        $('.body-ask-true').click(function(){
            yesCallBack();
            $(".body-ask-true").unbind();
        });
    }
    // 取消按钮
    if (noCallBack) {
        $('.body-ask-false').click(function(){
            noCallBack();
            $(".body-ask-false").unbind();
        });
    }

}
function Frame(title,html) {
    $(".body-frame-title").html(title);
    $(".body-frame").show(200);
    $(".cover-10").show();
    $(".body-frame-html").html(html);
}
function closeFrame() {
    $(".body-frame-title").html("");
    $(".body-frame-html").html("");
    $(".body-frame").hide(200);
    $(".cover-10").hide();
    $(".cover-90").hide();
}
function closeAsk() {
    $(".body-ask").hide(200);
    $(".full-gray").hide();
}
function loading() {
    $(".loading").show();
    $(".cover-90").show();
}
function endLoading() {
    $(".loading").slideUp(500);
    $(".cover-90").hide();
}

function secToTime(second){
    let date_time = "",days,hours,min,sec;
    //秒转换为x天x时x分x秒的形式
    days = Math.floor(second / 86400);
    hours = Math.floor((second / 3600) % 24);
    min = Math.floor((second/60) % 60);
    sec = second % 60;
    if (days>0) {
        date_time += days + "天";
    }
    if (hours>0||date_time) {
        date_time += hours + "时";
    }
    if (min>0||date_time) {
        date_time += min + "分";
    }
    if (sec>0||date_time) {
        date_time += sec + "秒";
    }
    if (!date_time) {
        date_time = "已到期";
    }
    return date_time;
}
function makeTimeCtDwn(element,second) {
    let left_time = second;
    let date_time = secToTime(left_time);
    $(element).html(date_time);
    setInterval(function(){
        if(left_time > 0){
            left_time--;
            date_time = secToTime(left_time);
            $(element).html(date_time);
        }else{
            location.reload();
        }
    },1000);
}

function tableToExcel(tableid, sheetName) {
    var tb = $('#'+tableid);
    var oldHtml = tb.html();
    $(".no-export").remove();
    //base64转码
    var base64 = function (s) {
        return window.btoa(unescape(encodeURIComponent(s)));
    };
    //替换table数据和worksheet名字
    var format = function (s, c) {
        return s.replace(/{(\w+)}/g,
            function (m, p) {
                return c[p];
            });
    };
    var uri = 'data:application/vnd.ms-excel;base64,';
    var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"'+
        'xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>'+
        '<x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets>'+
        '</x:ExcelWorkbook></xml><![endif]-->'+
        '<style type="text/css">'+
        'table td {'+
        'border: 0.5px solid #666;'+
        'width: auto;'+
        'height: 26px;'+
        'text-align: center;'+
        'background-color: #FFF;'+
        'font-size: 14px;'+
        'color: #333;'+
        '}'+
        'table th{'+
        'color: #FFF;'+
        'background-color: #4B6E96;'+
        'font-size: 16px;'+
        'text-align: center;'+
        'height: 30px;'+
        'border: 0.5px solid #FFF;'+
        '}'+
        '</style>'+
        '</head><body><table>{table}</table></body></html>';

    var ctx = {worksheet: sheetName || 'Worksheet', table: tb.html()};
    var excelHtml = format(template, ctx);
    $("#excelOut").attr("href",uri + base64(excelHtml));
    tb.html(oldHtml);
}

function printTable(tableid)
{
    var tb = $('#'+tableid);
    var newFrame = "print-frame";
    tb.parent("div").append('<iframe id="'+newFrame+'" src="" frameborder="0" hidden></iframe>');
    var printFrame = $('#'+newFrame);

    var format = function (s, c) {
        return s.replace(/{(\w+)}/g,
            function (m, p) {
                return c[p];
            });
    };
    var template = '<html>'+
        '<head>'+
        '<style type="text/css">'+
        'table{'+
        'width: 100%;'+
        'border-collapse:collapse;'+
        'background-color: #FFF;'+
        'text-align: center;'+
        '}'+
        'table td {'+
        'border: 0.5px solid #999;'+
        'background-color: #FFF;'+
        'font-size: 14px;'+
        'line-height: 22px;'+
        'color: #333;'+
        '}'+
        'table th{'+
        'color: #FFF;'+
        'background-color: #4B6E96;'+
        'font-size: 16px;'+
        'line-height: 26px;'+
        'min-width: 50px;'+
        'border: 0.5px solid #999;'+
        '}'+
        '</style></head><body><table>{table}</table></body></html>';
    var ctx = {table: tb.html()};
    var newHtml = format(template, ctx);
    printFrame.contents().find("body").html(newHtml);
    printFrame.contents().find(":hidden").show();
    printFrame.contents().find(".no-export").remove();
    document.getElementById(newFrame).contentWindow.print();
}


/**
 *对Date的扩展，将 Date 转化为指定格式的String
 *月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
 *年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
 *例子：
 *(new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
 *(new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
 */
Date.prototype.format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "H+": this.getHours(), //小时
        "i+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
};

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

Math.formatFloat = function (f, digit) {
    let m = Math.pow(10, digit);
    return Math.round(f * m, 10) / m;
};