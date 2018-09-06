<?php

class Tickets
{
    //获取所有票务信息
    static function getAllTickets($GET)
    {
        $sql_head = <<<LL
select a.*,b.name as scenic_name,c.sale_name,e.name as business_name 
from a_tickets a 
left join a_scenic b on a.pid = b.pid 
LEFT JOIN a_sale c on a.sale_id = c.sale_id 
LEFT JOIN a_business e on a.bid = e.bid
LL;
        //sql语句加入筛选条件
        $sql_body = empty($GET["stock"])?"":" AND stock > 0";
        $sql_body.= empty($GET["title"])?"":" AND title LIKE '%$GET[title]%'";
        $sql_body.= empty($GET["type"])?"":" AND type = '$GET[type]'";
        $sql_body.= empty($GET["area"])?"":" AND area = '$GET[area]'";
        //分离次数
        $times = empty($GET["times"])?"":explode("-",$GET["times"],2);
        $sql_body.= empty($times[1])?"":" AND times between '$times[0]' AND '$times[1]'";
        //分离价格
        $price = empty($GET["price"])?"":explode("-",$GET["price"],2);
        $sql_body.= empty($price)?"":" AND price between '$price[0]' AND '$price[1]'";
        //判断日期是否合法
        if (!empty($GET["start_time"]) && !empty($GET["end_time"])){
            $GET["start_time"].=" 00:00:00";
            $GET["end_time"].=" 23:59:59";
            $sql_body.= !(strtotime($GET["start_time"]) && strtotime($GET["end_time"]))?"":" AND begin_time <= '$GET[start_time]' AND end_time >= '$GET[end_time]'";
        }
        //按update_time倒序排列
        $sql_body.=" order by update_time DESC";
        $sql = $sql_head.$sql_body;
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("all-tickets",$data,"没有此类票券");
        return $html;
    }

    //获取票券编辑页面
    static function getEditTicket($tid)
    {
        $sql = "select a.*,b.begin_time as up_begin_time,b.end_time as up_end_time,b.up_stock,b.up_time_span from a_tickets a left join a_uptickets b on a.tid = b.tid where a.tid = '$tid'";
        $data = Mysql::query($sql,1);
        if(empty($data)) return "找不到数据！";

        $begin_time = str_replace("-","/",$data[0]["begin_time"]);
        $end_time = str_replace("-","/",$data[0]["end_time"]);
        $begin_end_time = $begin_time." - ".$end_time;

        $scenic_options = Actions::getOpts("scenic",$data[0]["pid"]);
        $business_options = Actions::getOpts("business",$data[0]["bid"]);
        $sale_options = Actions::getOpts("sale",$data[0]["sale_id"]);
        $type_options = Actions::getOpts("type",$data[0]["type"]);
        $city_options = Actions::getOpts("city",$data[0]["city"]);
        $ticket_options = Actions::getOpts("ticket","","and begin_time>='$begin_time' and end_time<='$end_time'");
        $pic_html = Template::getPicTemp("tid",$tid);
        if (empty($data[0]["up_stock"])){
            $auto_up_checked = "";
            $auto_up_hidden = "hidden";
            $days = 0;
            $hours = 0;
            $min = 0;
            $sec = 0;
            $up_end_sel = empty($data[0]["up_end_time"])?"":"selected";
            $up_end_hidden = empty($data[0]["up_end_time"])?"hidden":"";
        }else{
            $auto_up_checked = "checked";
            $auto_up_hidden = "";
            $days = floor($data[0]["up_time_span"] / 86400);
            $hours = floor(($data[0]["up_time_span"] % 86400)/3600);
            $min = floor(($data[0]["up_time_span"] % 3600)/60);
            $sec = floor($data[0]["up_time_span"] % 60);
            $data[0]["up_begin_time"] = str_replace("-","/",$data[0]["up_begin_time"]);
            $data[0]["up_end_time"] = str_replace("-","/",$data[0]["up_end_time"]);
            $up_end_sel = empty($data[0]["up_end_time"])?"":"selected";
            $up_end_hidden = empty($data[0]["up_end_time"])?"hidden":"";
        }

        if ($data[0]["tic_type"] == 1){
            $sel_tic_type = ["selected","",""];
            $type_html = <<<LL
                <div class="date-area" style="border: solid 1px #FFF;padding: 10px;margin: 10px 0">
                    有效时间：<input id="begin-end-time" type="text" style="width: 350px" value="$begin_end_time" placeholder="请选择有效时间段"/>
                    （修改有效时间会清空套票的子票券）
                </div>
                <div class="normal-tic" style="border: solid 1px #FFF;padding: 10px;margin: 10px 0">
                    <label for="times">可用次数：</label>
                    <input id="times" type="number" style="width: 150px" placeholder="单位：（次）" value="{$data[0]["times"]}"/>（次）
                    <label>票券种类：</label>
                    <select class="type" style="color: #363636;width: 120px">
                        $type_options
                    </select>
                </div>
LL;
        }elseif ($data[0]["tic_type"] == 2){
            $sel_tic_type = ["","selected",""];
            $type_html = <<<LL
                <div class="time-tic" style="border: solid 1px #FFF;padding: 10px;margin: 10px 0">
                    <label for="valid-days">有效时长：</label>
                    <input id="valid-days" type="number" style="width: 150px" placeholder="单位：（天）" value="{$data[0]["valid_days"]}"/>（天）
                    <label>票券种类：</label>
                    <select class="type" style="color: #363636;width: 120px">
                        $type_options
                    </select>
                </div>
LL;
        }elseif ($data[0]["tic_type"] == 3){
            $sel_tic_type = ["","","selected"];
            $child_sql = "select a.child_tid,a.child_price,b.title,b.price as orig_price from a_tickets_child a,a_tickets b where a.father_tid = '$tid' and a.child_tid = b.tid";
            $child_data = Mysql::query($child_sql,1);
            $tb_html = "";
            foreach ($child_data as $row){
                $tb_html.= <<<LL
                <tr class="child-tic $row[child_tid]"> 
                    <td class="tid">$row[child_tid]</td>
                    <td class="title">$row[title]</td>
                    <td class="single-price">$row[orig_price]</td>
                    <td> 
                        <input class="child-price" type="number" value="$row[child_price]" style="width: 80px"/>
                    </td>
                    <td> 
                        <button type="button" class="btn btn-sm btn-danger" onclick='ask("移除此套票下的子票券【$row[title]】？","removeTid(\"$row[child_tid]\")")'>删除</button>
                    </td>
                </tr>
LL;
            }
            $type_html = <<<LL
                <div class="date-area" style="border: solid 1px #FFF;padding: 10px;margin: 10px 0">
                    有效时间：<input id="begin-end-time" type="text" style="width: 350px" value="$begin_end_time" placeholder="请选择有效时间段"/>
                    （修改有效时间会清空套票的子票券）
                </div>
                <div class="group-tic" style="border: solid 1px #FFF;padding: 10px;margin: 10px 0">

                    <label for="child-tid">添加子票券：</label>
                    <select id="child-tid" style="color: #363636;width: 400px">
                        $ticket_options
                    </select>
                    
                    <button type="button" class="btn btn-default" onclick="addTid();">添加</button><br/>
                    <label>套票包含的子票券：</label>
                    <table id="group-arr" class="my-table">
                        <tr> 
                            <th>tid</th>
                            <th>票名</th>
                            <th>单独售价</th>
                            <th>分成金额</th>
                            <th>操作</th>
                        </tr>
                        $tb_html
                    </table>
                    <script> 
                        $(document).on("change",".child-price",function() {
                            let price = 0;
                            $(".child-price").each(function() {
                                if (!isNaN($(this).val())){
                                    price = price + $(this).val() * 1;
                                }
                            });
                            $("#price").val(price);
                        })
                    </script>
                </div>
LL;
        }else{
            return "还没有设计这种票";
        }

        $html = <<<LL
        <div class="form-area">
            <form id="add-ticket" class="layui-form">
                <label>TID：$tid</label><br/>
                <label for="title">票名： </label>
                <input id="title" type="text" style="width: 600px" placeholder="请填写票券标题" value="{$data[0]["title"]}"/><br/>
                <label for="orig-price">原价：</label>
                <input id="orig-price" type="number" style="width: 120px" placeholder="单位：（元）" value="{$data[0]["orig_price"]}"/>（元）
                <label for="price">现价：</label>
                <input id="price" type="number" style="width: 120px" placeholder="单位：（元）" value="{$data[0]["price"]}"/>（元）<br/>
                <label for="stock">库存：</label>
                <input id="stock" type="number" style="width: 120px" placeholder="单位：（张）" value="{$data[0]["stock"]}"/>（张）
                <label><input id="is-auto-update" $auto_up_checked type="checkbox" onchange="autoUpTgl($(this).is(':checked'))"/><span></span>自动更新库存</label><br/>
                <div class="auto-update-area" style="border: solid 1px #FFF;padding: 10px;margin: 10px 0" $auto_up_hidden>
                    从 <input id="up-begin-time" type="text" value="{$data[0]['up_begin_time']}" placeholder="请选择开始时间" style="width: 180px"/>
                    开始，每过
                    <input id="up-span-day" type="number" value="$days" style="width: 50px"/>天
                    <input id="up-span-hour" type="number" value="$hours" style="width: 50px"/>时
                    <input id="up-span-min" type="number" value="$min" style="width: 50px"/>分
                    <input id="up-span-sec" type="number" value="$sec" style="width: 50px"/>秒，
                    <br/>更新库存到
                    <input id="up-stock" type="number" style="width: 120px" value="{$data[0]['up_stock']}" placeholder="单位：（张）"/>
                    （张）
                    <select id="have-stop-time" onchange="$('.stop-auto-update').toggle();" style="color: #2E2D3C"> 
                        <option value="0">无过期时间</option>
                        <option value="1" $up_end_sel>自动结束更新</option>
                    </select>
                    <label class="stop-auto-update" $up_end_hidden>于日期 <input id="up-end-time" type="text" value="{$data[0]['up_end_time']}" placeholder="请选择结束时间" style="width: 180px"/></label>
                    <br/>
                </div>
                <label for="tic-type">票券类型：</label>
                <select id="tic-type" disabled onchange="setTicType()" style="color: #363636;width: 300px">
                    <option value="1" $sel_tic_type[0]>次数票（常规票）</option>
                    <option value="2" $sel_tic_type[1]>时长票（周卡/月卡/年卡）</option>
                    <option value="3" $sel_tic_type[2]>组合票（套票）</option>
                </select>（不可修改）<br/>
                $type_html
                
                <label for="city">所属城市：</label>
                <select id="city" style="color: #363636;width: 300px">
                    <option value="">无</option>
                    $city_options
                </select>
                <label for="scenic">所属景区：</label>
                <select id="scenic" style="color: #363636;width: 300px;">
                    <option value="">无</option>
                    $scenic_options
                </select><br/>
                <label for="business">所属商家：</label>
                <select id="business" style="color: #363636;width: 300px">
                    <option value="">无</option>
                    $business_options
                </select>
                <label for="sale">参与优惠：</label>
                <select id="sale" style="color: #363636;width: 300px">
                    <option value="">无</option>
                    $sale_options
                </select><br/>
                
                <label style="vertical-align: 35px">有效图片：</label>
                <label>$pic_html</label><br/>
                <label style="vertical-align: 35px">上传图片：</label>
                <label for="upfile">
                    <div class="upload-div upload"></div>
                </label>
                <input type="file" id="upfile" class="hidden" multiple onchange="checkFile(this)"/><br/>

                <label for="detail">票券介绍：</label><br/>
                <textarea id="detail" placeholder="请编辑票券介绍" rows="5" cols="80" style="color: black">{$data[0]["detail"]}</textarea><br/>
                <button type="button" onclick="postTicket('$tid');" class="btn" style="color: #363636">保存</button>
            </form>
            <script>
                var begin_end_time = $("#begin-end-time").val();
                setInterval(function() {
                    if (begin_end_time !== $("#begin-end-time").val()){
                        begin_end_time = $("#begin-end-time").val();
                        $("#group-arr .child-tic").remove();
                        if (!begin_end_time) {
                            $("#child-tid").html("<option value=''>请先选择有效时间！</option>");
                            return;
                        }
                        $.post(ajax_url,
                        {
                            begin_end_time : begin_end_time,
                            action_key : "getChildTic"
                        },function (result) {
                            if (result) {
                                $("#child-tid").html(result);
                            }else{
                                $("#child-tid").html("<option value=''>此时间段内没有合适的票券！</option>");
                            }
                        })
                    }
                },500);
                laydate.render({
                    elem: '#up-begin-time'
                    ,type: "datetime",
                    format:"yyyy/MM/dd HH:mm:ss",
                    min:0
                });
                laydate.render({
                    elem: '#up-end-time'
                    ,type: "datetime",
                    format:"yyyy/MM/dd HH:mm:ss",
                    min:0
                });
                laydate.render({
                    elem: '#begin-end-time'
                    ,type: "datetime",
                    format:"yyyy/MM/dd HH:mm:ss",
                    range:true
                });
            </script>
        </div>
LL;

        return $html;
    }

    //获取新增票券页面
    static function getAddTicket()
    {
        $scenic_options = Actions::getOpts("scenic");
        $business_options = Actions::getOpts("business");
        $sale_options = Actions::getOpts("sale");
        $type_options = Actions::getOpts("type");
        $city_options = Actions::getOpts("city");

        $html = <<<LL
        <div class="form-area">
            <form id="add-ticket">
                <label for="title">票名：</label>
                <input id="title" type="text" style="width: 600px" placeholder="请填写票券标题"/><br/>
                <label for="orig-price">原价：</label>
                <input id="orig-price" type="number" style="width: 100px" placeholder="单位：（元）"/>（元）
                <label for="price">现价：</label>
                <input id="price" type="number" style="width: 100px" placeholder="单位：（元）"/>（元）<br/>
                <label for="stock">初始库存：</label>
                <input id="stock" type="number" style="width: 100px" placeholder="单位：（张）"/>（张）               
                <label><input id="is-auto-update" type="checkbox" onchange="autoUpTgl($(this).is(':checked'))"/><span></span>自动更新库存</label><br/>
                <div class="auto-update-area" style="border: solid 1px #FFF;padding: 10px;margin: 10px 0" hidden>
                    从 <input id="up-begin-time" type="text" placeholder="请选择开始时间" style="width: 180px"/>
                    开始，每过
                    <input id="up-span-day" type="number" value="0" style="width: 50px"/>天
                    <input id="up-span-hour" type="number" value="0" style="width: 50px"/>时
                    <input id="up-span-min" type="number" value="0" style="width: 50px"/>分
                    <input id="up-span-sec" type="number" value="0" style="width: 50px"/>秒，
                    <br/>更新库存到
                    <input id="up-stock" type="number" style="width: 100px" placeholder="单位：（张）"/>
                    （张）
                    <select id="have-stop-time" onchange="$('.stop-auto-update').toggle();" style="color: #2E2D3C"> 
                        <option value="0" selected>无过期时间</option>
                        <option value="1">自动结束更新</option>
                    </select>
                    <label class="stop-auto-update" hidden>于日期 <input id="up-end-time" type="text" placeholder="请选择结束时间" style="width: 180px"/></label>
                    <br/>
                </div>
                <label for="tic-type">票券类型：</label>
                <select id="tic-type" onchange="setTicType()" style="color: #363636;width: 300px">
                    <option value="1">次数票（常规票）</option>
                    <option value="2">时长票（周卡/月卡/年卡）</option>
                    <option value="3">组合票（套票）</option>
                </select><br/>
                <div class="date-area" style="border: solid 1px #FFF;padding: 10px;margin: 10px 0">
                    有效时间：<input id="begin-end-time" type="text" style="width: 350px" placeholder="请选择有效时间段"/>
                    （修改有效时间会清空套票的子票券）
                </div>

                <div class="normal-tic" style="border: solid 1px #FFF;padding: 10px;margin: 10px 0">
                    <label for="times">可用次数：</label>
                    <input id="times" type="number" style="width: 150px" placeholder="单位：（次）"/>（次）
                    <label>票券种类：</label>
                    <select class="type" style="color: #363636;width: 120px">
                        $type_options
                    </select>
                </div>
                <div class="time-tic" style="display:none;border: solid 1px #FFF;padding: 10px;margin: 10px 0">
                    <label for="valid-days">有效时长：</label>
                    <input id="valid-days" type="number" style="width: 150px" placeholder="单位：（天）"/>（天）
                    <label>票券种类：</label>
                    <select class="type" style="color: #363636;width: 120px">
                        $type_options
                    </select>
                </div>
                <div class="group-tic" style="display:none;border: solid 1px #FFF;padding: 10px;margin: 10px 0">
                    <label for="child-tid">添加子票券：</label>
                    <select id="child-tid" style="color: #363636;width: 400px">
                        <option value="">请先选择有效时间</option>
                    </select>
                    <button type="button" class="btn btn-warning" onclick="addTid();">添加</button><br/>
                    <label>套票包含的子票券（点击可删除）：</label>
                    <table id="group-arr" class="my-table">
                        <tr> 
                            <th>tid</th>
                            <th>票名</th>
                            <th>价格</th>
                            <th>分成价格</th>
                            <th>操作</th>
                        </tr>
                    </table>
                </div>
                <label for="city">所属城市：</label>
                <select id="city" style=";width: 250px">
                    <option value="">无</option>
                    $city_options
                </select>
                
                <label for="scenic">所属景区：</label>
                <select id="scenic" style=";width: 250px;">
                    <option value="">无</option>
                    $scenic_options
                </select>
                <br/>
                <label for="business">所属商家：</label>
                <select id="business" style="color: #363636;width: 250px">
                    <option value="">无</option>
                    $business_options
                </select>
                <label for="sale">参与优惠：</label>
                <select id="sale" style="color: #363636;width: 250px">
                    <option value="">无</option>
                    $sale_options
                </select><br/>

                <label style="vertical-align: 35px">上传图片：</label>
                <label for="upfile" style="overflow: hidden">
                    <div class="upload-div upload"></div>
                </label>
                <input type="file" id="upfile" multiple class="hidden" onchange="checkFile(this)"/><br/>

                <label for="detail">票券介绍：</label><br/>
                <textarea id="detail" placeholder="请编辑票券介绍" rows="5" cols="80" style="color: black"></textarea><br/>
                <button type="button" onclick="postTicket('');" class="btn" style="color: #363636">保存</button>
            </form>
            <script>
                var begin_end_time = $("#begin-end-time").val();
                setInterval(function() {
                    if (begin_end_time !== $("#begin-end-time").val()){
                        begin_end_time = $("#begin-end-time").val();
                        $("#group-arr .child-tic").remove();
                        if (!begin_end_time) {
                            $("#child-tid").html("<option value=''>请先选择有效时间！</option>");
                            return;
                        }
                        $.post(ajax_url,
                        {
                            begin_end_time : begin_end_time,
                            action_key : "getChildTic"
                        },function (result) {
                            if (result) {
                                $("#child-tid").html(result);
                            }else{
                                $("#child-tid").html("<option value=''>此时间段内没有合适的票券！</option>");
                            }
                        })
                    }
                },500);
                laydate.render({
                    elem: '#up-begin-time'
                    ,type: "datetime",
                    format:"yyyy/MM/dd HH:mm:ss",
                    min:0
                });
                laydate.render({
                    elem: '#up-end-time'
                    ,type: "datetime",
                    format:"yyyy/MM/dd HH:mm:ss",
                    min:0
                });
                laydate.render({
                    elem: '#begin-end-time'
                    ,type: "datetime",
                    format:"yyyy/MM/dd HH:mm:ss",
                    range:true
                });
                
                $(document).on("change",".child-price",function() {
                    let price = 0;
                    $(".child-price").each(function() {
                        if (!isNaN($(this).val())){
                            price = price + $(this).val() * 1;
                        }
                    });
                    $("#price").val(price);
                })
            </script>
        </div>
LL;
        return $html;
    }

    static function addTicket($data,$files){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
            //return "数控提交不完整";
        if ($tic_type == 3){
            $new_tid = Actions::makeID("tid","G");
        }else{
            $new_tid = Actions::makeID("tid","T");
        }
        $date_arr = explode(" - ",$begin_end_time);
        $begin_time = $end_time = "";
        if (!empty($date_arr[1])){
            $begin_time = $date_arr[0];
            $end_time = $date_arr[1];
        }
        $sql = <<<LL
        insert into a_tickets(tid,pid,bid,title,orig_price,price,tic_type,valid_days,times,
        stock,begin_time,end_time,type,city,detail,sale_id,update_time) 
        values('$new_tid','$pid','$bid','$title','$orig_price','$price','$tic_type','$valid_days',
        '$times','$stock','$begin_time','$end_time','$type','$city','$detail','$sale_id',now())
LL;
        Mysql::query($sql);

        if ($tic_type == 3){
            $group_arr = json_decode($group_arr_json);
            $insert = "";
            foreach ($group_arr as $child_tid=>$child_price){
                $insert.=",('$new_tid','$child_tid','$child_price')";
            }
            $insert = substr($insert,1);
            $insert_sql = "insert into a_tickets_child(father_tid,child_tid,child_price) values$insert";
            Mysql::query($insert_sql);
        }

        File::makeDir("pictures/ticket/$new_tid");
        File::uploadFile("pictures/ticket/$new_tid",$files);

        $data["tid"] = $new_tid;
        self::setAutoUpdate($data);

        return "票券添加成功";
    }

    static function updateTicket($data,$files){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";
        $date_arr = explode(" - ",$begin_end_time);
        $begin_time = $end_time = "";
        if (!empty($date_arr[1])){
            $begin_time = $date_arr[0];
            $end_time = $date_arr[1];
        }

        $sql = <<<LL
        update a_tickets set pid = '$pid',bid = '$bid',title = '$title',orig_price = '$orig_price',
        price = '$price',tic_type = '$tic_type',valid_days = '$valid_days',
        times = '$times',stock = '$stock',begin_time = '$begin_time',end_time = '$end_time',
        type = '$type',city = '$city',detail = '$detail',sale_id = '$sale_id',update_time = now() 
        where tid = '$tid'
LL;

        Mysql::query($sql);
        if ($tic_type == 3){
            $group_arr = json_decode($group_arr_json);
            $insert = "";
            foreach ($group_arr as $child_tid=>$child_price){
                $insert.=",('$tid','$child_tid','$child_price')";
            }
            $insert = substr($insert,1);
            $delete_sql = "delete from a_tickets_child where father_tid = '$tid'";
            $insert_sql = "insert into a_tickets_child(father_tid,child_tid,child_price) values$insert";
            Mysql::query($delete_sql);
            Mysql::query($insert_sql);
        }
        File::makeDir("pictures/ticket/$tid");
        File::uploadFile("pictures/ticket/$tid",$files);
        self::setAutoUpdate($data);

        return "票券修改成功";
    }

    static function delTicket($tid){
        if (empty($tid))
            return "tid为空";
        $sql = "delete from a_tickets where tid = '$tid'";
        Mysql::query($sql);
        File::delDir("pictures/ticket/$tid");
        return "票券删除成功！";
    }


    static function getAutoUpdate(){
        $sql = "select a.title,b.* from a_tickets a,a_uptickets b where a.tid = b.tid";
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("auto-update",$data);
        return $html;
    }

    static function setAutoUpdate($data){
        foreach ($data as $key=>$value){
            $$key = $value;
        }

        $sql_find = "select tid from a_uptickets where tid = '$tid'";
        $data_find = Mysql::query($sql_find,1);
        $up_end_time = empty($up_end_time)?"null":"'$up_end_time'";
        if (empty($auto_update)){
            if (!empty($data_find)){
                $sql = "delete from a_uptickets where tid = '$tid'";
                Mysql::query($sql);
                return 1;
            }
            return 0;
        }else{
            if (!empty($data_find)){
                $sql = <<<LL
            update a_uptickets set begin_time = '$up_begin_time',end_time = $up_end_time,
            up_stock = '$up_stock',up_time_span = '$up_time_span',update_time = now() where tid = '$tid'
LL;
            }else{
                $sql = <<<LL
            insert into a_uptickets(tid,begin_time,end_time,up_stock,up_time_span,update_time,state)
            values('$tid','$up_begin_time',$up_end_time,'$up_stock','$up_time_span',now(),1)
LL;
            }
            Mysql::query($sql);
            return 1;
        }

    }

    static function delAutoUpdate($tid){
        if (empty($tid)) return "tid为空";
        $sql = "delete from a_uptickets where tid = '$tid'";
        Mysql::query($sql);
        return "设置删除成功！";
    }




    static function getAllTicType(){
        $sql = "select * from app_ticket_type";
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("all-tic-type",$data);
        return $html;
    }

    static function getAddTicType(){
        $html = <<<LL
        <div class="form-area">
            <form id="add-tic-type">
                <label for="type-name">票券种类名称： </label>
                <input id="type-name" type="text" style="width: 600px" placeholder="请填写票券种类"/><br/>
                <button type="button" onclick="postTicType('');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        return $html;
    }

    static function getEditTicType($id){
        $sql = "select * from app_ticket_type where id = '$id'";
        $data = Mysql::query($sql,1);
        $html = <<<LL
        <div class="form-area">
            <form id="add-tic-type">
                <label for="type-name">票券种类名称： </label>
                <input id="type-name" type="text" style="width: 600px" value="{$data[0]['type_name']}" placeholder="请填写票券种类"/><br/>
                <button type="button" onclick="postTicType('$id');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        return $html;
    }

    static function addTicType($data){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        $sql = "insert into app_ticket_type(type_name,update_time,state) values('$type_name',now(),1)";
        if(Mysql::query($sql)){
            return "票券种类添加成功！";
        }else{
            return "发生错误！";
        }
    }

    static function updateTicType($data){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        $sql = "update app_ticket_type set type_name = '$type_name',update_time = now() where id = '$id'";
        if(Mysql::query($sql)){
            return "票券种类添加成功！";
        }else{
            return "操作失败，发生错误！";
        }
    }

    static function delTicType($id){
        $sql = "delete from app_ticket_type where id = '$id'";
        if(Mysql::query($sql)){
            return "票券种类删除成功！";
        }else{
            return "操作失败，发生错误！";
        }
    }





}