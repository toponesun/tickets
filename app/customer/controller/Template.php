<?php

class Template
{
    static function getTemp($tmp_sty,$data,$p=1,$ept_msg = "没有找到内容"){
        if (empty($data)) return self::addTailHtml("",$ept_msg);
        $html = "";
        $i = ($p-1) * ROWS_PER_PAGE;
        $uid = UID;
        switch ($tmp_sty){
            case "tickets":
                foreach ($data as $row){
                    $i++;
                    //$row["title"] = mb_substr($row["title"],0,16,"UTF-8")."...";
                    $img_url = Actions::getThumb("ticket",$row['tid']);
                    if($row["tic_type"]==3) {
                        $sql_sec = "select a.father_tid,b.* from a_tickets_child a,a_tickets b where a.father_tid = '$row[tid]' and a.child_tid = b.tid";
                        $data_sec = Mysql::query($sql_sec,1);
                        $html_sec = self::getTemp("ticSec",$data_sec,"");
                        $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="pictures/tic_group.png" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]');">查看</a></td>
                    <td>套票</td>
                    <td colspan="4"><a onclick="$('.$row[tid]').toggle()">查看子票券</a></td>
                    <td>$row[price]</td>
                    <td>$row[stock]</td>
                    <td>
                        <img src="image/cart-small.png" style="cursor: pointer;width: 28px" onclick="changeGoodsByNum('$row[tid]','+1');"/>
                    </td>
                </tr>
                $html_sec
LL;
                    }elseif ($row["tic_type"]==2){
                        $html .= <<<LL
            
                <tr>
                    <td>$i</td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]');">查看</a></td>
                    <td>计时票</td>
                    <td>购买后 $row[valid_days] 天内有效</td>
                    <td>不限</td>
                    <td>$row[type]</td>
                    <td>$row[city]</td>
                    <td>$row[price]</td>
                    <td>$row[stock]</td>
                    <td>
                        <img src="image/cart-small.png" style="cursor: pointer;width: 28px" onclick="changeGoodsByNum('$row[tid]','+1');"/>
                    </td>
                </tr>
LL;
                    }else{
                        $html .= <<<LL
            
                <tr>
                    <td>$i</td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]');">查看</a></td>
                    <td>常规票</td>
                    <td>起：$row[begin_time]<br/>止：$row[end_time]</td>
                    <td>$row[times]</td>
                    <td>$row[type]</td>
                    <td>$row[city]</td>
                    <td>$row[price]</td>
                    <td>$row[stock]</td>
                    <td>
                        <img src="image/cart-small.png" style="cursor: pointer;width: 28px" onclick="changeGoodsByNum('$row[tid]','+1');"/>
                    </td>
                </tr>
LL;
                    }
                }

                $html = <<<LL
                    <table class="my-table">
                        <tr>
                            <th>序号</th>
                            <th>预览</th>
                            <th width="30%">票名</th>
                            <th>类型</th>
                            <th width="21%">有效时间</th>
                            <th>次数</th>
                            <th>种类</th>
                            <th>城市</th>
                            <th>价格/元</th>
                            <th>库存</th>
                            <th>操作</th>
                        </tr>
                        $html
                    </table>
LL;
                break;
            case "ticSec":
                foreach ($data as $row) {
                    $img_url = Actions::getThumb("ticket",$row['tid']);
                    if ($row["tic_type"] == 1) {
                        $html .= <<<LL
            
                <tr class="$row[father_tid]" style="background-color: #fefed7" hidden>
                    <td></td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td onclick="showDetail('$row[tid]');">$row[title]</td>
                    <td>常规票</td>
                    <td>起：$row[begin_time]<br/>止：$row[end_time]</td>
                    <td>$row[times]</td>
                    <td>$row[type]</td>
                    <td>$row[city]</td>
                    <td colspan="3"></td>
                </tr>
LL;
                    } else {
                        $html .= <<<LL
            
                <tr class="$row[father_tid]" style="background-color: #fefed7" hidden>
                    <td></td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td onclick="showDetail('$row[tid]');">$row[title]</td>
                    <td>计时票</td>
                    <td>购买后 $row[valid_days] 天内有效</td>
                    <td>不限</td>
                    <td>$row[type]</td>
                    <td>$row[city]</td>
                    <td colspan="3"></td>
                </tr>
LL;
                    }
                }
                return $html;
                break;
            case "cartList":
                foreach ($data as $row){
                    $options = Actions::getOpt($row["num"],99);
                    $img_url = Actions::getThumb("ticket",$row['tid']);
                    $checked = empty($row["state"])?"":"checked";
                    var_dump($row["state"]);
                    $stock_left = $row["stock"] - $row["num"];
                    if ($stock_left < 0){
                        $cart_tip = "<br/><span class='cart-tip'>库存不足</span>";
                    }elseif($stock_left < 10){
                        $cart_tip = "<br/><span class='cart-tip'>库存紧张</span>";
                    }else{
                        $cart_tip = "";
                    }

                    if($row["tic_type"] == 3) {
                        $sql_sec = "select a.father_tid,b.* from a_tickets_child a,a_tickets b where a.father_tid = '$row[tid]' and a.child_tid = b.tid";
                        $data_sec = Mysql::query($sql_sec,1);
                        $html_sec = self::getTemp("ticSec",$data_sec,"");
                        $html .= <<<LL
                <tr>
                    <td>
                        <label for="$row[tid]">
                            <input id="$row[tid]" onchange="changeGoodsByNum('$row[tid]','*')" class="$row[tid]" type="checkbox" name="tickets" value="$row[num]" $checked/>
                            <span></span>
                        </label>
                    </td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="pictures/tic_group.png" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]');">查看</a></td>
                    <td>套票</td>
                    <td colspan="4"><a onclick="$('.$row[tid]').toggle()">查看子票券</a></td>
                    <td>$row[price]</td>
                    <td>
                        <select style="width:50px;font-size: 20px;font-family: '黑体',serif" onchange="changeGoodsByNum('$row[tid]','~'+$(this).val())">
                            $options
                        </select>         
                        $cart_tip
                    </td>
                    <td>
                        <button class="btn btn-danger" onclick="ask('您确定要删除吗？','changeGoodsByNum(\'$row[tid]\',0)');">删除</button>
                    </td>
                </tr>
                $html_sec
LL;
                    }elseif ($row["tic_type"] == 2){
                        $html .= <<<LL
                <tr>
                    <td>
                        <label for="$row[tid]">
                            <input id="$row[tid]" onchange="changeGoodsByNum('$row[tid]','*')" class="$row[tid]" type="checkbox" name="tickets" value="$row[num]" $checked/>
                            <span></span>
                        </label>
                    </td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]');">查看</a></td>
                    <td>计时票</td>
                    <td>购买后$row[valid_days]天内有效</td>
                    <td>不限</td>
                    <td>$row[type]</td>
                    <td>$row[city]</td>
                    <td>$row[price]</td>
                    <td>
                        <select style="width:50px;font-size: 20px;font-family: '黑体',serif" onchange="changeGoodsByNum('$row[tid]','~'+$(this).val())">
                            $options
                        </select>         
                        $cart_tip
                    </td>
                    <td>
                        <button class="btn btn-danger" onclick="ask('您确定要删除吗？','changeGoodsByNum(\'$row[tid]\',0)');">删除</button>
                    </td>
                </tr>
LL;
                    }else{
                        $html .= <<<LL
                <tr>
                    <td>                        
                        <label for="$row[tid]">
                            <input id="$row[tid]" onchange="changeGoodsByNum('$row[tid]','*')" class="$row[tid]" type="checkbox" name="tickets" value="$row[num]" $checked/>
                            <span></span>
                        </label> 
                    </td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]');">查看</a></td>
                    <td>常规票</td>
                    <td>起：$row[begin_time]<br/>止：$row[end_time]</td>
                    <td>$row[times]</td>
                    <td>$row[type]</td>
                    <td>$row[city]</td>
                    <td>$row[price]</td>
                    <td>
                        <select style="width:50px;font-size: 20px;font-family: '黑体',serif" onchange="changeGoodsByNum('$row[tid]','~'+$(this).val())">
                            $options
                        </select>         
                        $cart_tip
                    </td>
                    <td> 
                        <button class="btn btn-danger" onclick="ask('您确定要删除吗？','changeGoodsByNum(\'$row[tid]\',0)');">删除</button>
                    </td>
                </tr>
LL;
                    }
                }
                return $html;
                break;
            case "cart":
                foreach ($data as $row){
                    //$img_dir = Actions::getPicUrl($row['tid']);
                    $options = Actions::getOpt($row["num"],99);
                    $checked = empty($row["state"])?"":"checked";
                    $html .= <<<LL

            <div class="goods">
                <div style="position: absolute;"> 
                    <label for="$row[tid]">
                        <input id="$row[tid]" onchange="changeGoodsByNum('$row[tid]','*')" type="checkbox" $checked/>
                        <span></span>
                    </label>
                </div>
                <div class="ticket-title">
                    $row[title]
                </div>
                <div style="height: 24px;float: left;margin-right: 5px">
                    <img src="image/-.png" style="cursor:pointer;vertical-align:top;height: 100%"  onclick="changeGoodsByNum('$row[tid]','-1')"/>
                </div>
                <div style="height: 24px;float: left;margin-right: 5px;">
                    <select style="width:50px;font-size: 20px;font-family: '黑体',serif" onchange="changeGoodsByNum('$row[tid]','~'+$(this).val())">
                        $options
                    </select>         
                </div>
                <div style="height: 24px;float: left;margin-right: 5px">
                    <img src="image/+.png" style="cursor:pointer;vertical-align:top;height: 100%" onclick="changeGoodsByNum('$row[tid]','+1')"/>
                </div>
                
                <label style="color: #F00;line-height: 15px">￥$row[price]</label><br>
                <div class="del-cart">
                    <img width="25px" src="image/delete.png" onclick="ask('您确定要删除吗？','changeGoodsByNum(\'$row[tid]\',0)');"/>
                </div>
            </div>
LL;
                }

                return $html;
                break;
            case "myOrders":
                foreach ($data as $row) {
                    $i++;
                    switch($row["state"]){
                        case -1:
                            $create_time = $row["create_time"];
                            $second = strtotime($create_time) + ORDER_LIVE_TIME - time();
                            if ($second > 0) {
                                $row["state_name"] .=<<<LL
                                (<span class='left-time-$i'></span>)
                                <script>makeTimeCtDwn('.left-time-$i','$second');</script>
LL;
                            }else{
                               $row["state_name"] .="(正在取消)";
                            }

                            $btn_arr = array("支付"=>"go('?file=cashier&oid=$row[oid]');",
                                "取消"=>"ask('确定要取消这个订单吗？','cancel(\'$row[oid]\')')");
                            break;
                        case 1:
                            $btn_arr = array("退票"=>"refund('$row[oid]');");
                            break;
                        default:
                            $btn_arr = ($row['visibility']==1)?array("删除"=>"ask('是否确认删除此订单？','delOrder(\'$row[oid]\')')")
                                :array("还原订单"=>"ask('是否确认还原此订单？','recover(\'$row[oid]\')')");
                            break;
                    }
                    $btn_html = self::btnTemp($btn_arr);
                    $html .= <<<LL
            
                <tr>
                    <td>$i</td>
                    <td>$row[oid] <a onclick="view_order('$row[oid]');">查看</a></td>
                    <td>$row[price] 元</td>
                    <td>$row[num] 张</td>
                    <td>$row[create_time]</td>
                    <td>$row[state_name]</td>
                    <td>$btn_html</td>
                </tr>
LL;
                }
                $html = <<<LL
                <table class="my-table">
                    <tr>
                        <th>序号</th>
                        <th>订单号</th>
                        <th>总金额</th>
                        <th>总票数</th>
                        <th>下单时间</th>
                        <th>订单状态</th>
                        <th>操作</th>
                    </tr>
                    $html
                </table>
            
LL;
                break;
            case "myTickets":
                foreach ($data as $row){
                    $i++;
                    $img_url = Actions::getThumb("ticket",$row['tid']);
                    //$row["title"] = mb_substr($row["title"],0,18,"UTF-8")."...";
                    if($row["tic_type"] == 3){
                        $sql_sec = "select a.*,c.state_name from a_mytickets a left join app_ticket_state c on c.state_id = a.state where a.father_KEY = '$row[ticket_KEY]'";
                        $data_sec = Mysql::query($sql_sec,1);
                        $html_sec = self::getTemp("myTicSec",$data_sec,1,"");
                        $html.=<<<LL
                <tr>
                    <td>$i</td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="pictures/tic_group.png" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]')">查看</a></td>
                    <td>$row[ticket_KEY]</td>
                    <td colspan="4"><a onclick="$('.$row[ticket_KEY]').toggle();">查看子票券</a></td>
                    <td>$row[create_time]</td>
                    <td>
                    <button class="btn btn-info" onclick="view_ticket('$row[ticket_KEY]');">
	                    查看
                    </button>
                    </td>
                </tr>
                $html_sec
LL;
                    }elseif($row["tic_type"] == 2){
                        $html.=<<<LL
                <tr>
                    <td>$i</td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]')">查看</a></td>
                    <td>$row[ticket_KEY]</td>
                    <td>起：$row[begin_time]<br/>止：$row[end_time]</td>
                    <td>不限次数</td>
                    <td>$row[type]</td>
                    <td>$row[state_name]</td>
                    <td>$row[create_time]</td>
                    <td>
                    <button class="btn btn-info" onclick="view_ticket('$row[ticket_KEY]')">
	                    查看
                    </button>
                    </td>
                </tr>
LL;
                    }else{
                        $html.=<<<LL
                <tr>
                    <td>$i</td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]')">查看</a></td>
                    <td>$row[ticket_KEY]</td>
                    <td>起：$row[begin_time]<br/>止：$row[end_time]</td>
                    <td>$row[times]次(共$row[orig_times]次)</td>
                    <td>$row[type]</td>
                    <td>$row[state_name]</td>
                    <td>$row[create_time]</td>
                    <td>
                    <button class="btn btn-info" onclick="view_ticket('$row[ticket_KEY]');">
	                    查看
                    </button>
                    </td>
                </tr>
LL;
                    }
                }
                $html = <<<LL
                <table class="my-table">
                    <tr>
                        <th>序号</th>
                        <th>预览</th>
                        <th>票名</th>
                        <th>券码</th>
                        <th width="220px">有效时间</th>
                        <th>次数</th>
                        <th>种类</th>
                        <th>状态</th>
                        <th width="100px">购买日期</th>
                        <th>操作</th>
                    </tr>
                    $html
                </table>
LL;
                break;
            case "myTicSec":
                foreach ($data as $row){
                    $img_url = Actions::getThumb("ticket",$row['tid']);
                    //$row["title"] = mb_substr($row["title"],0,18,"UTF-8")."...";
                    if ($row["tic_type"] == 1){
                        $html.= <<<LL
                <tr class="$row[father_KEY]" style="background-color: #FEFED7" hidden>
                    <td></td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]')">查看</a></td>
                    <td>$row[ticket_KEY]</td>
                    <td>起：$row[begin_time]<br/>止：$row[end_time]</td>
                    <td>$row[times]次(共$row[orig_times]次)</td>
                    <td>$row[type]</td>
                    <td>$row[state_name]</td>
                    <td>-</td>
                    <td>
                    <button class="btn btn-info" onclick="view_ticket('$row[ticket_KEY]');">
	                    查看
                    </button>
                    </td>
                </tr>
LL;
                    }elseif($row["tic_type"] == 2){
                        $html.= <<<LL
                <tr class="$row[father_KEY]" style="background-color: #FEFED7" hidden>
                    <td></td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td>$row[title] <a onclick="showDetail('$row[tid]')">查看</a></td>
                    <td>$row[ticket_KEY]</td>
                    <td>起：$row[begin_time]<br/>止：$row[end_time]</td>
                    <td>不限次数</td>
                    <td>$row[type]</td>
                    <td>$row[state_name]</td>
                    <td>-</td>
                    <td>
                    <button class="btn btn-info" onclick="view_ticket('$row[ticket_KEY]');">
	                    查看
                    </button>
                    </td>
                </tr>
LL;
                    }

                }
                break;
            case "ticDetail":
                $html = <<<LL

<div class="ticket-detail">
    <div id="qrcode"></div>
    <div class="ticket-detail-text">
        标题：{$data[0]['title']}<br/>
        券码：{$data[0]['ticket_KEY']}<br/>
        状态：{$data[0]['state_name']}（{$data[0]['times']}次）<br/>
        购买日期：{$data[0]['create_time']}<br/>
        可用日期：{$data[0]['begin_time']}<br/>
        失效日期：{$data[0]['end_time']}<br/>
LL;
                $html.= empty($data[0]["valid_time"])?"":<<<LL
            
        使用时间：{$data[0]['valid_ime']}<br/>
        使用地点：{$data[0]['device_id']}<br/>
LL;
                $html.= <<<LL
            
    </div>
</div>
LL;
                break;
            case "confirm":
                $list_html = self::getTemp("confirm_tb",$data);
                $pay_info = Cart::getSaleInfo();
                $html = <<<LL
            <div class="title-lg">
                请确认您的订单！
            </div>
            <div style="position:absolute;top:65px;bottom:90px;left: 20px;right: 20px">
                <div style="background-color:#FFF;position:absolute;color: #363636;left:0;right: 280px;height: 100%;overflow-y:auto;">
                    <table class="my-table">
                        <tr>
                        <th>序号</th>
                        <th>预览</th>
                        <th width="400px" >票名</th>
                        <th width="210px">有效期</th>
                        <th>种类</th>
                        <th>单价</th>
                        <th>数量</th>
                        </tr>
                        $list_html
                    </table>
                </div>
                 <div style="padding:50px 20px 20px 20px;position:absolute;font-family:'黑体',serif;background-color:#FFF;font-size:18px;width:270px;right: 0;line-height:32px;overflow-y: auto;margin-left:10px;height:100%;float: left;color: #363636;">
                    <div class="title-lg" style="width:100%;left: 0;top:0;min-width: 0;line-height: 36px;font-size: 20px">订单信息</div>
                    发票：不开发票<br/>
                    支付方式：在线支付<br/>
                    总票数：<b>$pay_info[total_num]</b> 张<br/>
                    原票价：<b>$pay_info[total_price]</b> 元 <br/>
                    满减折扣：<b>$pay_info[save_money]</b> 元<br/>
                    折后价：<b>$pay_info[final_price]</b> 元<br/>
                    代金券：暂无代金券<br/>
                    实付款：<b style="color: red">$pay_info[final_price]</b> 元<br/>
                </div>
            </div>
            <div style="height:70px;bottom:10px;padding:10px;position: absolute;background-color: #FFF;left: 20px;right: 20px;overflow: hidden">
                <div style="width: 50%;height: 100%;position: relative;float: left">
                    <div class="ready-pay" style="background-color:#35B2C6;right: 20px" onclick="go('?file=tickets')">
                        我再看看
                    </div>
                </div>
                <div style="width: 50%;height: 100%;position: relative;float: left">
                    <div class="ready-pay" style="left: 20px" onclick="go('?file=createOrder')">
                        提交订单
                    </div>
                </div>
            </div>
LL;
                break;

            case "confirm_tb":
                foreach ($data as $row){
                    $i++;
                    $img_url = Actions::getThumb("ticket",$row['tid']);
                    switch ($row["tic_type"]){
                        case 1:
                            $tic_type = "常规票";
                            $date_td = "起：$row[begin_time]<br/>止：$row[end_time]";
                            break;
                        case 2:
                            $tic_type = "时长票";
                            $date_td = "购买后$row[valid_days]天内";
                            break;
                        case 3:
                            $tic_type = "组合票";
                            $date_td = "以子票券为准";
                            break;
                        default:
                            $tic_type = "";$date_td="";
                            break;
                    }
                    if ($row["tic_type"] == 1 || $row["tic_type"] == 2) {
                        $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td><span class="mini-span">$tic_type</span>$row[title]</td>
                    <td>$date_td</td>
                    <td>$row[type]</td>
                    <td>$row[price]元</td>
                    <td>$row[num]</td>
                </tr>
LL;
                    }elseif ($row["tic_type"] == 3){
                        $sql_sec = "select a.father_tid,b.*,'$row[num]' as num from a_tickets_child a,a_tickets b where a.father_tid = '$row[tid]' and a.child_tid = b.tid";
                        $data_sec = Mysql::query($sql_sec,1);
                        $html_sec = self::getTemp("confirm_tbSec",$data_sec,"");
                        $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td><span class="mini-span">$tic_type</span>$row[title]</td>
                    <td><a onclick="$('.$row[tid]').toggle()">查看子票券</a></td>
                    <td>套票</td>
                    <td>$row[price]元</td>
                    <td>$row[num]</td>
                </tr>
                $html_sec
LL;
                    }

                }
                break;
            case "confirm_tbSec":
                foreach ($data as $row){
                    switch ($row["tic_type"]){
                        case 1:
                            $tic_type = "常规票";
                            $date_td = "起：$row[begin_time]<br/>止：$row[end_time]";
                            break;
                        case 2:
                            $tic_type = "时长票";
                            $date_td = "购买后$row[valid_days]天内";
                            break;
                        case 3:
                            $tic_type = "组合票";
                            $date_td = "以子票券为准";
                            break;
                        default:
                            $tic_type = "";$date_td="";
                            break;
                    }
                    $img_url = Actions::getThumb("ticket",$row['tid']);
                    $html .= <<<LL
                <tr class="$row[father_tid]" style="background-color: #fefed7" hidden>
                    <td></td>
                    <td><a onclick="showDetail('$row[tid]');"><img src="$img_url" width="40px" height="40px"/></a></td>
                    <td onclick="showDetail('$row[tid]');"><span class="mini-span">$tic_type</span>$row[title]</td>
                    <td>$date_td</td>
                    <td>$row[type]</td>
                    <td></td>
                    <td>$row[num]</td>
                </tr>
LL;
                }
                break;
            case "cashier":
                $save_price = $data[0]["orig_price"] - $data[0]["price"];
                $order_time = ORDER_LIVE_TIME - time() + strtotime($data[0]["create_time"]);
                $order_min = ceil(($order_time - 59 ) / 60);
                $order_sec = $order_time % 60;
                $html = <<<LL

            <div class="title-lg" style="background-color: #FF5656">
                订单已提交，请及时完成支付！
            </div>
            <div class="cashier">
                <span class="cashier-span">订单总览</span>
                <script>
                    let order_time = $order_time,
                    order_min = 0,order_sec = 0;
                    if (order_time > 0){
                        setInterval(function() {
                            if (order_time <= 0){
                                warn("订单支付已超时！");
                                setTimeout(function() {
                                    go("?file=myOrders");
                                },1000);
                                return 0;
                            }
                            order_time--;
                            order_min = Math.floor(order_time / 60);
                            order_sec = order_time % 60;
                            $(".order-min").html(order_min);
                            $(".order-sec").html(order_sec);
                        },1000)
                    }
                </script>
                <label class="cashier-tip">
                    请在
                    <span class="order-left-time order-min">$order_min</span>
                    分
                    <span class="order-left-time order-sec">$order_sec</span>
                    秒内完成支付，超时订单将自动取消！
                </label>
                <table class="not-table">
                    <tr>
                        <td>订单号：{$data[0]['oid']}</td>
                        <td>票数：{$data[0]['num']} 张</td>
                    </tr>
                    <tr>
                        <td>原价：{$data[0]['orig_price']} 元</td>
                        <td>优惠：$save_price 元</td>
                    </tr>
                    <tr>
                        <td>应付：{$data[0]['price']} 元</td>
                        <td>代金券：无代金券</td>
                    </tr>
                </table>
                <ul></ul>
                <span class="cashier-span">支付方式</span>
                <label class="cashier-tip">
                    请点击以选中支付方式！
                </label>
                <div class="payment-area">
                    <div style="width: 100%"> 
                        <label><input type="radio" value="balance" name="payment"/><span><img src="image/balance.png" style="height: 30px;vertical-align: -8px;margin: 0 5px">余额支付</span></label>
                        <label><input type="radio" value="wechat" name="payment"/><span><img src="image/wechat.png" style="height: 30px;vertical-align: -8px;margin: 0 5px">微信支付</span></label>
                        <label><input type="radio" value="alipay" name="payment"/><span><img src="image/alipay.png" style="height: 30px;vertical-align: -8px;margin: 0 5px">支付宝</span></label>
                    </div>
                    <div style="width: 100%;padding: 30px 0 0 0">
                        <span class="cashier-span" onclick="payOrder('{$data[0]['oid']}')" style="margin-right:100px;padding: 0 30px;float: left;cursor: pointer;background-color: #31D43D"> 
                            立即支付
                        </span>
                        <span class="cashier-span" onclick="go('?file=myOrders')" style="padding: 0 30px;float: left;cursor: pointer;background-color: #FF5656"> 
                            退出支付
                        </span>
                    </div>
                </div>
            </div>
        </div>
LL;

                break;
            case "refund":
                $list_html = self::getTemp("myTickets",$data);
                $sql = "select a.*,b.state_name from a_orders a left join app_order_state b on b.state_id = a.state where a.oid = '{$data[0]['oid']}' and a.uid = '$uid'";
                $data = Mysql::query($sql,1);
                $html = <<<LL
                
            <div class="title-lg">
                以下是你的退单信息，请确认！
            </div>
            <div style="position:absolute;top:55px;bottom:80px;left: 30px;right: 30px">
                <div style="background-color:#FFF;position:absolute;color: #363636;left:0;right: 300px;height: 100%;overflow-y:auto;">
                    $list_html
                </div>
                 <div style="padding:50px 20px 20px 20px;position:absolute;font-family:'黑体',serif;background-color:#FFF;font-size:18px;width:290px;right: 0;line-height:30px;overflow-y: auto;margin-left:10px;height:100%;float: left;color: #363636;">
                    <div class="title-lg" style="width: 100%;left: 0;top:0;min-width: 20px;line-height: 40px">退单信息</div>
                    订单号：<br/>{$data[0]["oid"]} <br/>
                    支付金额：{$data[0]["price"]} 元<br/>
                    票数：{$data[0]["num"]} 张<br/>
                    当前状态：{$data[0]["state_name"]}<br/>
                    支付方式：余额支付<br/>
                    提示：退款将按原支付方式退回<br/><br/>
                </div>
            </div>
            
            <div style="height:70px;bottom:0;padding:10px;position: absolute;background-color: #FFF;left: 30px;right: 30px;overflow: hidden">
                <div style="width: 50%;height: 100%;position: relative;float: left">
                    <div class="ready-pay" style="background-color:#35B2C6;right: 20px" onclick="go('?file=myOrders')">
                        暂时不退
                    </div>
                </div>
                <div style="width: 50%;height: 100%;position: relative;float: left">
                    <div class="ready-pay" style="left: 20px" onclick="refund_true('{$data[0]["oid"]}');">
                        一定要退
                    </div>
                </div>
            </div>
LL;
                return $html;
                break;
            case "tradeRec":
                foreach ($data as $row){
                    $i++;
                    $html .= <<<LL
                        <tr>
                        <td>$i</td>
                        <td>$row[create_time]</td>
                        <td>$row[oid]</td>
                        <td>$row[trade_num]</td>
                        <td>$row[type]</td>
                        <td>$row[payment]</td>
                        <td>$row[money]</td>
                        </tr>
LL;
                }
                $html = <<<LL
                <table class="my-table">
                    <tr>
                        <th>序号</th>
                        <th>交易时间</th>
                        <th>订单号</th>
                        <th>流水号</th>
                        <th>交易类型</th>
                        <th>交易方式</th>
                        <th>交易金额</th>
                    </tr>
                    $html
                </table>
LL;
                break;
            default:
                break;
            }
        $html = self::addTailHtml($html,$ept_msg);
        return $html;
    }

    //生成我的订单按钮区域
    static function btnTemp($data){
        $btn_html = "";
        foreach ($data as $key=>$value){
            switch (mb_substr($key,0,2,"utf8")){
                case "支付":
                    $btn_class = "btn-info";
                    break;
                case "删除":
                    $btn_class = "btn-danger";
                    break;
                case "退票":
                    $btn_class = "btn-warning";
                    break;
                case "取消":
                    $btn_class = "btn-danger";
                    break;
                default:
                    $btn_class = "btn-default";
                    break;
            }
            $btn_html.= <<<LL
                        
                <button class="btn $btn_class" onclick="$value">
                    $key
                </button>
LL;
        }
        return $btn_html;
    }


/*
    生成电脑端轮播图，接收数组类型
    [["title"=>"图片上文字","img"=>"图片url","url"=>"超链接"],...]（img必须，为空则不显示此图）
    或者
    ["图片1url","图片2url","图片3url",...]（纯img数组）
*/
    static function picGroupTemp($pic_group){
        $active = " active";
        $indicators_html = "";
        $inner_html = "";
        $html = "";
        $i = 0;
        foreach ($pic_group as $pic){
            if (!is_array($pic)){
                $img = $pic;
                unset($pic);
                $pic["img"] = $img;
            }
            $title_html = "";$onclick_html = "";
            if (!empty($pic["title"])){
                $title_html = "<div class='swiper-title'>$pic[title]</div>";
            }
            if (!empty($pic["url"])){
                $onclick_html = "go('$pic[url]');";
            }
            if (!empty($pic["img"])){
                $indicators_html .= <<<LL
            <li data-target="#slide" data-slide-to="$i" class="$active"></li>
LL;
                $inner_html .= <<<LL
            <div class="carousel-item$active">
                <img src="$pic[img]" style="width: 100%;height: 100%">
                <div class="carousel-caption">
                    <h3>$title_html</h3>
                    <p>$title_html</p>
                </div>
            </div>
LL;
                $active = "";
                $i++;
            }
        }

        if (!empty($inner_html)){
            $html = <<<LL
            <div id="slide" class="carousel slide" data-ride="carousel">
                <!-- 指示符 -->
                <ul class="carousel-indicators">
                    $indicators_html
                </ul>
                <!-- 轮播图片 -->
                <div class="carousel-inner">
                    $inner_html
                </div>
                <!-- 左右切换按钮 -->
                <a class="carousel-control-prev" href="#slide" data-slide="prev">
                    <span class="carousel-control-prev-icon"> </span>
                </a>
                <a class="carousel-control-next" href="#slide" data-slide="next">
                    <span class="carousel-control-next-icon"> </span>
                </a>
            </div>
LL;
        }
        return $html;
    }

/*
    生成轮播图swiper，接收数组类型
    [["title"=>"图片上文字","img"=>"图片url","url"=>"超链接"],...]（img必须，为空则不显示此图）
    或者
    ["图片1url","图片2url","图片3url",...]（纯img数组）
*/
    static function getSwiper($pic_group){
        $slide_html = "";
        foreach ($pic_group as $pic){
            if (!is_array($pic)){
                $img = $pic;
                unset($pic);
                $pic["img"] = $img;
            }
            $title_html = "";$onclick_html = "";
            if (!empty($pic["title"])){
                $title_html = "<div class='swiper-title'>$pic[title]</div>";
            }
            if (!empty($pic["url"])){
                $onclick_html = "go('$pic[url]');";
            }
            if (!empty($pic["img"])){
                $slide_html .= <<<LL
            <div class="swiper-slide" style="width: 100%">
                <img data-src="$pic[img]" class="swiper-lazy" onclick="$onclick_html">
                <div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div>
                $title_html
            </div>
LL;
            }
        }
        $html = <<<LL
        <div class="swiper-container">
            <div class="swiper-wrapper">$slide_html</div>
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
        </script>
LL;
        return $html;
    }

    //html加工，添加没有更多或提示为空信息
    static function addTailHtml($html,$ept_msg){
        if (empty($html)){
            $html = <<<LL
            
            <div class="no-content"> 
                <a style="font-size: 64px;line-height: 64px">:(</a></br>
                $ept_msg
            </div>
LL;
        }
        return $html;
    }

}