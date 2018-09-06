let ajax_url = "app/admin/controller/Ajax.php";

function commonAjax(data) {
    $.ajax({
        url: ajax_url,
        type: 'POST',
        data: data,                    // 上传formdata封装的数据
        //dataType: 'JSON',
        cache: false,                      // 不缓存
        processData: false,                // jQuery不要去处理发送的数据
        contentType: false,                // jQuery不要去设置Content-Type请求头
        success:function (result) {           //成功回调
            //console.log(result);
            msg(result);
            setTimeout(function () {
                window.location.reload();
            },1000)

        }
    });
}

function getFrame(frame_name,xid) {
    switch (frame_name){
        case "AddTicket":
            title = "新增票券";
            break;
        case "EditTicket":
            title = "修改票券";
            break;
        case "AddTicType":
            title = "新增票券种类";
            break;
        case "EditTicType":
            title = "修改票券种类";
            break;
        case "AddScenic":
            title = "新增景点";
            break;
        case "EditScenic":
            title = "修改景点";
            break;
        case "AddBusiness":
            title = "新增商家";
            break;
        case "EditBusiness":
            title = "修改商家";
            break;
        case "AddSale":
            title = "新增折扣";
            break;
        case "EditSale":
            title = "修改折扣";
            break;
        case "AddDevice":
            title = "新增设备";
            break;
        case "EditDevice":
            title = "修改设备";
            break;
        case "AddWebSet":
            title = "新增网站设置";
            break;
        case "EditWebSet":
            title = "修改网站设置";
            break;
        default:
            break;
    }
    $.post(ajax_url,
        {
            frame_name : frame_name,
            xid : xid,
            action_key : "getFrame"
        },function (result) {
            Frame(title,result);
        });
}

function delInfo(info,xid) {
    $.post(ajax_url,
        {
            info : info,
            xid : xid,
            action_key : "delInfo"
        },function (result) {
            if (result){
                msg(result);
            }
            setTimeout(function () {
                window.location.reload();
            },1000);
        });
}


function postTicket(tid) {
    var child_tic_obj = {};
    child_tic_trs = $("#group-arr .child-tic");
    for (let i=0;i<child_tic_trs.length;i++){
        let key = child_tic_trs.eq(i).children(".tid").html();
        let val = child_tic_trs.eq(i).children("td").children(".child-price").val();
        child_tic_obj[key] = val;
    }
    var group_arr_json = JSON.stringify(child_tic_obj);

    var data = {
        tid : tid,
        title : $("#title").val(),
        orig_price : $("#orig-price").val(),
        price : $("#price").val(),
        stock : $("#stock").val(),


        up_begin_time : $("#up-begin-time").val(),
        up_end_time : $("#up-end-time").val(),
        up_time_span : $("#up-span-day").val() * 86400
        + $("#up-span-hour").val() * 3600
        + $("#up-span-min").val() * 60
        + $("#up-span-sec").val() * 1,
        up_stock : $("#up-stock").val(),

        tic_type : $("#tic-type").val(),

        times : $("#times").val(),
        begin_end_time : $("#begin-end-time").val(),

        valid_days : $("#valid-days").val(),

        group_arr_json : group_arr_json,

        pid : $("#scenic").val(),
        bid : $("#business").val(),
        city : $("#city").val(),
        sale_id : $("#sale").val(),
        type : $(".type:visible").val(),
        detail : $("#detail").val()
    };
    var formData = new FormData();
    var files = document.getElementById("upfile").files;
    if (data.title == ""||data.price ==""){
        warn("请填写所有表单！");
        return 0;
    }
    if (data.tid == ""){
        data.action_key = "newTicketData";
    }else{
        data.action_key = "updateTicketData";
    }
    if ($("#have-stop-time").val() == 0){
        data.up_end_time = "";
    }


    for (let i = 0 ; i < files.length ; i++ ){
        formData.append('file'+ i, files[i]);
        formData.append('file'+ i, files[i]);
    }
    if(data.tic_type == 1){
        data.valid_days = null;
        data.group_arr_json = null;
    }else if(data.tic_type == 2){
        data.times = null;
        data.begin_end_time = null;
        data.group_arr_json = null;
    }else if(data.tic_type == 3){
        data.times = null;
        data.valid_days = null;
        data.type = "";
    }
    if ($("#is-auto-update").is(":checked")) {
        formData.append('auto_update', true);
    }else{
        formData.append('auto_update', "");
    }

    for(key in data){
        formData.append(key, data[key]);
    }

    commonAjax(formData)
}


function postScenic(pid) {
    var data = {
        pid : pid,
        name : $("#name").val(),
        city : $("#city").val(),
        address : $("#address").val(),
        info : $("#info").val()
    };
    var formData = new FormData();
    var files = document.getElementById("upfile").files;

    if (data.name == ""||data.city ==""||data.address == ""||data.info ==""){
        warn("请填写所有表单！");
        return 0;
    }
    if (data.pid == ""){
        data.action_key = "newScenicData";
    }else{
        data.action_key = "updateScenicData";
    }
    for (var i = 0 ; i < files.length ; i++ ){
        formData.append('file'+ i, files[i]);
    }

    for(key in data){
        formData.append(key, data[key]);
    }

    commonAjax(formData);
}

function postBusiness(bid) {
    var data = {
        bid : bid,
        name : $("#name").val(),
        city : $("#city").val(),
        phone : $("#phone").val(),
        address : $("#address").val(),
        info : $("#info").val()
    };
    var formData = new FormData();
    var files = document.getElementById("upfile").files;

    if (data.name == ""||data.city ==""||data.address == ""||data.info ==""){
        warn("请填写所有表单！");
        return 0;
    }
    if (data.bid == ""){
        data.action_key = "newBusinessData";
    }else{
        data.action_key = "updateBusinessData";
    }
    for (var i = 0 ; i < files.length ; i++ ){
        formData.append('file'+ i, files[i]);
    }

    for(key in data){
        formData.append(key, data[key]);
    }

    commonAjax(formData);
}

function post_sale(sale_id) {
    var data = {
        sale_id : sale_id,
        sale_name : $("#sale-name").val(),
        sale_type : $("#sale-type").val(),
        term_price : $("#term-price").val(),
        term_num : $("#term-num").val(),
        save_percent : $("#save-percent").val(),
        save_money : $("#save-money").val(),
        begin_time : $("#begin-time").val(),
        end_time : $("#end-time").val()
    };
    var formData = new FormData();

    if (data.sale_name == ""||data.sale_type ==""||data.term_price == ""
        ||data.term_num ==""||data.save_percent == ""||data.save_money ==""
        ||data.begin_time == ""||data.end_time ==""){
        warn("请填写所有表单！");
        return 0;
    }
    if (data.sale_id == ""){
        data.action_key = "newSaleData";
    }else{
        data.action_key = "updateSaleData";
    }

    for (key in data){
        formData.append(key,data[key]);
    }

    commonAjax(formData);
}

function postDevice(device_id) {
    var arr = [];
    spans = $("#device-tid-list span");
    for (i=0;i<spans.length;i++){
        arr.push(spans.eq(i).attr("class"));
    }
    var device_tid_json = JSON.stringify(arr);
    var data = {
        device_id : device_id,
        device_name : $("#device-name").val(),
        device_address : $("#device-address").val(),
        device_is_entrance : $("#is-entrance").val(),
        device_tid_json : device_tid_json
    };
    var formData = new FormData();

    if (data.device_name == ""||data.device_address ==""||data.device_tid == ""){
        warn("请填写所有表单！");
        return 0;
    }
    if (data.device_id == ""){
        data.action_key = "newDeviceData";
    }else{
        data.action_key = "updateDeviceData";
    }

    console.log(data);

    for (key in data){
        formData.append(key,data[key]);
    }
    commonAjax(formData);
}

function postWebSet(){
    var data = {
        title_suffix : $("#title-suffix").val(),
        footer : $("#footer-text").val(),
        session_live_time : $("#session-live-time").val(),
        order_live_time : $("#order-live-time").val(),
        rnd_key_live_time : $("#rnd-key-live-time").val(),
        rows_per_page : $("#rows-per-page").val(),
        valid_interval: $("#valid-interval").val(),
        need_out_valid: $("#need-out-valid").val()
    };
    var formData = new FormData();
    if (data.title_suffix == ""||data.order_live_time ==""){
        warn("请填写所有表单！");
        return 0;
    }
    if (data.set_id == ""){
        data.action_key = "newWebSetData";
    }else{
        data.action_key = "updateWebSetData";
    }

    for(key in data){
        formData.append(key, data[key]);
    }
    commonAjax(formData)
}

function postTicType(id){
    var data = {
        id : id,
        type_name : $("#type-name").val()
    };
    var formData = new FormData();
    if (data.type_name == ""){
        warn("请填写所有表单！");
        return 0;
    }
    if (data.id == ""){
        data.action_key = "newTicTypeData";
    }else{
        data.action_key = "updateTicTypeData";
    }

    for(key in data){
        formData.append(key, data[key]);
    }
    commonAjax(formData)
}

function addDeviceTid() {
    var tid = $("#device-tid").val();
    var txt = $("#device-tid option:selected").text();
    if(!tid){
        return;
    }
    if($("."+tid).length>0){
        warn("不允许重复添加！！");
    }else{
        $("#device-tid-list").append("<span title='"+tid+"' class='"+tid+"' onclick='removeTid(\""+tid+"\")'>"+txt+"</span>");
    }
}

function addTid() {
    var tid = $("#child-tid").val();
    var txt = $("#child-tid option:selected").text();
    var single_price = $("#child-tid option:selected").attr("title");
    if(!tid){
        return;
    }
    if($(`.${tid}`).length>0){
        warn("不允许重复添加！！");
    }else{
        $("#group-arr").append(`<tr class="child-tic ${tid}"> 
            <td class="tid">${tid}</td>
            <td class="title">${txt}</td>
            <td class="single-price">${single_price}</td>
            <td> 
                <input class="child-price" type="number" style="width: 80px" value=""/>
            </td>
            <td><button type="button" onclick="$(this).parent('td').parent('tr').remove()" class="btn btn-sm btn-danger">删除</button></td>
        </tr>`);
    }
}

function removeTid(tid) {
    $("#device-tid-list ."+tid).remove();
    $("#group-arr ."+tid).remove();
}

function valid_test() {
    let ticket_key = $("#ticket_key").val();
    let device_id = $("#device_id").val();
    $.ajax({
        url:"app/customer/controller/Ajax.php",
        type:"post",
        data:{
            ticket_key : ticket_key,
            device_id : device_id,
            action_key : "ticketValid"
        },
        dataType:"text",
        success:function (result) {
            alert(result);
        },
        error:function () {
            alert("请求的接口没有返回值！")
        }
    });
}

function show_tips() {
    Frame("操作说明","说明文档尚未更新");
}

function setTicType() {
    var tic_type = $("#tic-type").val();

    if(tic_type == 1){
        $(".date-area").show(200);
        $(".normal-tic").show(200);
        $(".time-tic").hide(200);
        $(".group-tic").hide(200);
    }else if(tic_type == 2){
        $(".date-area").hide(200);
        $(".normal-tic").hide(200);
        $(".time-tic").show(200);
        $(".group-tic").hide(200);
    }else if (tic_type == 3){
        $(".date-area").show(200);
        $(".normal-tic").hide(200);
        $(".time-tic").hide(200);
        $(".group-tic").show(200);
    }else{
        return false;
    }
}
function autoUpTgl(b) {
    let checked = $("#is-auto-update").is(":checked");
    if (b) {
        $(".auto-update-area").show(200);
    }else{
        $(".auto-update-area").hide(200);
    }
}

function readFile(el) {
    /*
    var file = el.files[0];
    //限定上传文件的类型，判断是否是图片类型
    if (!/image\/\w+/.test(file.type)) {
        alert("只能选择图片");
        return false;
    }
    */
    var reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function (e) {
        base64Code = this.result;
        //把得到的base64赋值到img标签显示
        $(".fst-img").attr("src",base64Code);
    }
}

function checkFile(el){
    var files = el.files;  //获取选择的文件对象
    var allowTypes = ["image/jpeg","image/png","image/x-png","image/bmp","image/gif"]; //允许上传的文件类型
    var maxFileSize = 2 * 1024 * 1024;  //允许上传的单个文件的大小限制，最大能上传50M
    var maxFileNum = 10;  //允许上传的最大张数
    var allowUpload = true; //经过校验之后是否允许上传
    var errorMessage = "";  //校验文件之后，文件不符合要求的提示信息
    var upload_img;

    if (files.length > maxFileNum){
        errorMessage += "上传的文件数为" + files.length + "！最多只能上传" + maxFileNum + "张";
        allowUpload = false;
    }

    if (allowUpload){
        //$("#upload-imgs").html("<img id='upload-img' class='upload-img' src='image/upload.png'>");
        $(".uploaded").remove();
        for(var i=0; i< files.length; i++){
            var fileName = files[i].name;    //文件名
            var fileType = files[i].type;    //文件类型
            var fileSize = files[i].size;    //文件大小，单位为byte（字节）

            var typeAccepted = false;
            for(var j = 0; j < allowTypes.length; j++){
                if(allowTypes[j] == fileType){
                    typeAccepted = true;
                    break;
                }
            }
            if(typeAccepted != true){
                errorMessage += fileName + "不是图片，只能上传图片！";
                allowUpload = false;
            }

            if(typeAccepted && fileSize > maxFileSize){
                errorMessage += fileName+"的文件大小超出了2M限制！";
                allowUpload = false;
            }
            var file = files[i];
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function (e) {
                base64Code = this.result;
                $(".upload").before("<div class='upload-div uploaded'><img class='upload-img' src="+base64Code+"></div>");
                //把得到的base64赋值到img标签显示
                //$("#fst-img0" ).attr("src",base64Code);
            }
        }
    }
    if(allowUpload != true){
        el.outerHTML = el.outerHTML; //清空选择的文件
        warn(errorMessage);
    }
}

function delFile(url){
    $.post(ajax_url,
        {
            action_key : "DelFile",
            url : url
        },function (result) {
            msg(result);
        })
}
function renameFile(url,old_name,new_name) {
    $.post(ajax_url,
        {
            action_key : "RenameFile",
            old_name : old_name,
            new_name : new_name,
            url : url
        },function (result) {
            msg(result);
        })
}

function goTerm(file_name) {
    let date_range = $("#term-date-range").val();
    let pid = $("#term-scenic").val();
    let bid = $("#term-business").val();
    let state_id = $("#term-tic-state").val();
    let url = "?file=" + file_name;

    if (!date_range&&!pid&&!bid&&!state_id){
        warn("查询条件为空！");
        //return;
    }
    if (date_range) url += "&date_range=" + date_range;
    if (pid) url += "&pid=" + pid;
    if (bid) url += "&bid=" + bid;
    if (state_id) url += "&state_id=" + state_id;
    go(url);
}
