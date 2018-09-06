<?php

class MobileTemplate extends Template
{
    static function getTemp($tmp_sty,$data,$p=1,$ept_msg = "没有更多了..."){
        if (empty($data)){
            return self::addTailHtml("",$ept_msg);
        }
        $html = "";
        $i = ($p-1) * ROWS_PER_PAGE;
        $uid = UID;
        switch ($tmp_sty){
            case "tickets":
                foreach ($data as $card){
                    $card['begin_time'] = date("Y/m/d",strtotime($card['begin_time']));
                    $card['end_time'] = date("Y/m/d",strtotime($card['end_time']));
                    $img_url = Actions::getThumb("ticket",$card['tid']);
                    $sale_span = empty($card["sale_id"])?"":"<span class='ticket-title-span'>优惠</span>";
                    if($card["tic_type"]==3) {
                        $html .= <<<LL

        <div class="card">
            <div class="ticket-img" style="width: 100px;height: 100px" onclick="go('?file=detail&tid=$card[tid]')">
                <img src="pictures/tic_group.png"/>
            </div>
            <div class="ticket-title" onclick="go('?file=detail&tid=$card[tid]')">
                $sale_span <span class="ticket-title-span" style="background-color: #02879B">套票</span> $card[title]
            </div>
            <div class="ticket-info" onclick="go('?file=detail&tid=$card[tid]')">
                限时售卖<br/>
                有效时间及类型请查看子票券<br/>
                仅剩 $card[stock] 份 <b style="color: red;font-size: 18px">￥$card[price]</b>
            </div>
            <div class="ticket-cart" onclick="addToCart('$card[tid]');">
                <img src="image/cart-small.png" style="height: 100%" />
            </div>
        </div>
LL;
                    }elseif ($card["tic_type"]==2){
                        $html .= <<<LL

        <div class="card">
            <div class="ticket-img" style="width: 100px;height: 100px" onclick="go('?file=detail&tid=$card[tid]')">
                <img src="$img_url"/>
            </div>
            <div class="ticket-title" onclick="go('?file=detail&tid=$card[tid]')">
                $sale_span <span class="ticket-title-span" style="background-color: #F49800">计时票</span> $card[title]
            </div>
            <div class="ticket-info" onclick="go('?file=detail&tid=$card[tid]')">
                购买后$card[valid_days]天内有效<br/>每天限1次（$card[type]）<br/>
                余票 $card[stock] 张 <b style="color: red;font-size: 18px">￥$card[price]</b>
            </div>
            <div class="ticket-cart" onclick="addToCart('$card[tid]');">
                <img src="image/cart-small.png" style="height: 100%" />
            </div>
        </div>
LL;
                    }else{
                        $html .= <<<LL
            
        <div class="card">
            <div class="ticket-img" style="width: 100px;height: 100px" onclick="go('?file=detail&tid=$card[tid]')">
                <img src="$img_url"/>
            </div>
            <div class="ticket-title" onclick="go('?file=detail&tid=$card[tid]')">
                $sale_span $card[title]
            </div>
            <div class="ticket-info" onclick="go('?file=detail&tid=$card[tid]')">
                $card[begin_time] 至 $card[end_time]<br/>
                期间可用 $card[times] 次（$card[type]）<br/>
                余票 $card[stock] 张 <b style="color: red;font-size: 18px">￥$card[price]</b>
            </div>
            <div class="ticket-cart" onclick="addToCart('$card[tid]');">
                <img src="image/cart-small.png" style="height: 100%" />
            </div>
        </div>
LL;
                    }
                }
                return $html;
                break;
            case "child-tic":
                foreach ($data as $card){
                    $card['begin_time'] = date("Y/m/d",strtotime($card['begin_time']));
                    $card['end_time'] = date("Y/m/d",strtotime($card['end_time']));
                    $img_url = Actions::getThumb("ticket",$card['tid']);
                    $sale_span = empty($card["sale_id"])?"":"<span class='ticket-title-span'>优惠</span>";
                    if($card["tic_type"]==3) {
                        $html .= <<<LL

        <div class="card">
            <div class="ticket-img" style="width: 100px;height: 100px" onclick="go('?file=detail&tid=$card[tid]')">
                <img src="pictures/tic_group.png"/>
            </div>
            <div class="ticket-title" onclick="go('?file=detail&tid=$card[tid]')">
                $sale_span <span class="ticket-title-span" style="background-color: #02879B">套票</span> $card[title]
            </div>
            <div class="ticket-info" onclick="go('?file=detail&tid=$card[tid]')">
                限时售卖<br/>
                有效时间及类型请查看子票券<br/>
                仅剩 $card[stock] 份 <b style="color: red;font-size: 18px">￥$card[price]</b>
            </div>
        </div>
LL;
                    }elseif ($card["tic_type"]==2){
                        $html .= <<<LL

        <div class="card">
            <div class="ticket-img" style="width: 100px;height: 100px" onclick="go('?file=detail&tid=$card[tid]')">
                <img src="$img_url"/>
            </div>
            <div class="ticket-title" onclick="go('?file=detail&tid=$card[tid]')">
                $sale_span <span class="ticket-title-span" style="background-color: #F49800">计时票</span> $card[title]
            </div>
            <div class="ticket-info" onclick="go('?file=detail&tid=$card[tid]')">
                购买后$card[valid_days]天内有效<br/>每天限1次（$card[type]）<br/>
                <b style="color: red;font-size: 18px">￥$card[price]</b>
            </div>
        </div>
LL;
                    }else{
                        $html .= <<<LL
            
        <div class="card">
            <div class="ticket-img" style="width: 100px;height: 100px" onclick="go('?file=detail&tid=$card[tid]')">
                <img src="$img_url"/>
            </div>
            <div class="ticket-title" onclick="go('?file=detail&tid=$card[tid]')">
                $sale_span $card[title]
            </div>
            <div class="ticket-info" onclick="go('?file=detail&tid=$card[tid]')">
                $card[begin_time] 至 $card[end_time]<br/>
                期间可用 $card[times] 次（$card[type]）<br/>
                <b style="color: red;font-size: 18px">￥$card[price]</b>
            </div>
        </div>
LL;
                    }
                }
                return $html;
                break;
            case "find":
                foreach ($data as $card){
                    $span_html = "";
                    $span_arr = json_decode($card["spans"]);
                    $span_arr = empty($span_arr)?array():$span_arr;
                    $img_url = Actions::getThumb("scenic",$card["pid"]);
                    foreach ($span_arr as $span){
                        $span_html .= "<span>$span</span>";
                    }
                    $html .= <<<LL
            
        <div class="card" onclick="go('?file=scenic&pid=$card[pid]')">
            <div class="ticket-img" style="width:120px;height: 100px">
                <img src="$img_url"/>
            </div>
            <div class="ticket-title" style="height: 26px;font-size: 18px">
                $card[name]
            </div>
            <div class="ticket-info" style="padding-right:0;height: 72px;font-size: 14px;line-height: 18px;">
                $span_html<br/>
                综合人气 $card[pop_sco] &nbsp; 人均消费 ￥$card[avg_pay]<br/>
                <font color="#02879B">$card[address]</font><br/>
                $card[info]
            </div>
        </div>
LL;
                }
                break;
            case "cart":
                foreach ($data as $card){
                    $card['begin_time'] = date("Y/m/d",strtotime($card['begin_time']));
                    $card['end_time'] = date("Y/m/d",strtotime($card['end_time']));
                    $img_dir = Actions::getThumb("ticket",$card['tid']);
                    $options = MobileActions::getOpt($card["num"],99);
                    $checked = empty($card["state"])?"":"checked";
                    if ($card["tic_type"]==3){
                        $html .= <<<LL
                
            <div class="card">
                <div class="ticket-checkbox"> 
                    <label for="$card[tid]"><input id="$card[tid]" onchange="changeGoodsByNum('$card[tid]','*')" type="checkbox" $checked/><span></span></label>
                </div>
                <div class="ticket-title" style="margin:0 25px" onclick="go('?file=detail&tid=$card[tid]')">
                    <span class="ticket-title-span" style="background-color: #02879B">套票</span>$card[title]
                </div>
                <div class="ticket-img" onclick="go('?file=detail&tid=$card[tid]')">
                    <img src="pictures/tic_group.png"/>
                </div>
                <div class="ticket-cart-del" onclick="ask('您确定要删除吗？','changeGoodsByNum(\'$card[tid]\',0)');">
                    <img src="image/cart-del.png" style="height: 100%"/>
                </div>
                <div class="ticket-info">
                    仅剩 $card[stock] 份<br/>
                    有效时间及类型请查看子票券<br/>
                    限时售卖 <label style="color: red;font-size: 16px">￥$card[price]</label>
                </div>
                <div class="ticket-cart-ctrl">
                    <img src="image/-.png" style="cursor:pointer;vertical-align:top;height: 100%"  onclick="changeGoodsByNum('$card[tid]','-1')"/>
                    <select onchange="changeGoodsByNum('$card[tid]','~'+$(this).val())">
                        $options
                    </select>
                    <img src="image/+.png" style="cursor:pointer;vertical-align:top;height: 100%"  onclick="changeGoodsByNum('$card[tid]','+1')"/>
                </div>

            </div>
LL;
                    }elseif($card["tic_type"]==2){
                        $html .= <<<LL
                
            <div class="card">
                <div class="ticket-checkbox">
                    <label for="$card[tid]"><input id="$card[tid]" onchange="changeGoodsByNum('$card[tid]','*')" type="checkbox" $checked/><span></span></label>
                </div>
                <div class="ticket-title" style="margin:0 25px" onclick="go('?file=detail&tid=$card[tid]')">
                    <span class="ticket-title-span" style="background-color: #F49800">计时票</span> $card[title]
                </div>
                <div class="ticket-img" onclick="go('?file=detail&tid=$card[tid]')">
                    <img src="$img_dir"/>
                </div>
                <div class="ticket-cart-del" onclick="ask('您确定要删除吗？','changeGoodsByNum(\'$card[tid]\',0)');">
                    <img src="image/cart-del.png" style="height: 100%"/>
                </div>
                <div class="ticket-info">
                    剩余 $card[stock] 张<br/>
                    购买后 $card[valid_days] 天内有效<br/>
                    $card[type] <label style="color: red;font-size: 16px">￥$card[price]</label>
                </div>
                <div class="ticket-cart-ctrl">
                    <img src="image/-.png" style="cursor:pointer;vertical-align:top;height: 100%"  onclick="changeGoodsByNum('$card[tid]','-1')"/>
                    <select onchange="changeGoodsByNum('$card[tid]','~'+$(this).val())">
                        $options
                    </select>
                    <img src="image/+.png" style="cursor:pointer;vertical-align:top;height: 100%"  onclick="changeGoodsByNum('$card[tid]','+1')"/>
                </div>
            </div>
LL;
                    }else{
                        $html .= <<<LL
                
            <div class="card">
                <div class="ticket-checkbox">
                    <label for="$card[tid]"><input id="$card[tid]" onchange="changeGoodsByNum('$card[tid]','*')" type="checkbox" $checked/><span></span></label>
                </div>
                <div class="ticket-title" style="margin:0 25px" onclick="go('?file=detail&tid=$card[tid]')">
                    <span class="ticket-title-span" style="background-color: #337AB7">次数票</span> $card[title]
                </div>
                <div class="ticket-img" onclick="go('?file=detail&tid=$card[tid]')">
                    <img src="$img_dir"/>
                </div>
                <div class="ticket-cart-del" onclick="ask('您确定要删除吗？','changeGoodsByNum(\'$card[tid]\',0)');">
                    <img src="image/cart-del.png" style="height: 100%"/>
                </div>
                <div class="ticket-info">
                    剩余 $card[stock] 张<br/>
                    $card[begin_time] 至 $card[end_time] 期间可用$card[times]次<br/>
                    $card[type] <label style="color: red;font-size: 16px">￥$card[price]</label>
                </div>
                <div class="ticket-cart-ctrl">
                    <img src="image/-.png" style="cursor:pointer;vertical-align:top;height: 100%"  onclick="changeGoodsByNum('$card[tid]','-1')"/>
                    <select onchange="changeGoodsByNum('$card[tid]','~'+$(this).val())">
                        $options
                    </select>
                    <img src="image/+.png" style="cursor:pointer;vertical-align:top;height: 100%"  onclick="changeGoodsByNum('$card[tid]','+1')"/>
                </div>
            </div>
LL;
                    }
                }
                return $html;
                break;
            case "myOrders":
                foreach ($data as $card) {
                    $i++;
                    switch($card["state"]){
                        case -1:
                            $create_time = $card["create_time"];
                            $second = strtotime($create_time) + ORDER_LIVE_TIME - time();
                            $btn_arr = array("支付(<span class='left-time-$i'></span><script>makeTimeCtDwn('.left-time-$i','$second');</script>)"=>"go('?file=cashier&oid=$card[oid]');",
                                "取消"=>"ask('确定要取消这个订单吗？','cancel(\'$card[oid]\')')");
                            break;
                        case 1:
                            $btn_arr = array("退票"=>"refund('$card[oid]');");
                            break;
                        default:
                            $btn_arr = ($card['visibility']==1)?array("删除"=>"ask('是否确认删除此订单？','delOrder(\'$card[oid]\')')")
                                :array("还原订单"=>"ask('是否确认还原此订单？','recover(\'$card[oid]\')')");
                            break;
                    }
                    $btn_html = self::btnTemp($btn_arr);
                    $html .= <<<LL
            
        <div class="card">
            <div class="card-text" onclick="go('?file=orderDetail&oid=$card[oid]')">
                订单号：$card[oid]<br/>
                订单金额：￥$card[price] <span>$card[state_name]</span>（共$card[num]张）<br/>
                创建日期：$card[create_time]
            </div>
            <div class="card-btn">
                $btn_html
            </div>
        </div>
LL;
                }
                return $html;
                break;
            case "myTickets":
                foreach ($data as $card){
                    $img_dir = Actions::getThumb("ticket",$card['tid']);
                    $state_png = Actions::getStatePng($card["state_name"]);
                    //$card["state_png"] = ($card["state"] == "已使用")?"<img src='image/used.png'>":"";
                    if ($card["tic_type"] == 3) {
                        $sql = "select a.*,b.state_name,c.type_name from a_mytickets a left join app_ticket_state b on a.state = b.state_id left join app_ticket_type c on a.type = c.id where a.uid = '".UID."' and a.father_KEY = '$card[ticket_KEY]'";
                        $data_sec = Mysql::query($sql, 1);
                        $html_sec = self::getTemp("myTicSec", $data_sec);
                        $html .= <<<LL

        <div class="card">
            <div class="card-left">
                <div class="card-left-i">
                    <img src="$img_dir"/>
                </div>
                套票
            </div>
            <div class="card-text" onclick="go('?file=ticketDetail&ticket_key=$card[ticket_KEY]')">
                <span style="background-color: #02879B">套票</span> $card[title]<br/>
                券码：$card[ticket_KEY]<br/>
                购买日期：$card[create_time]
            </div>
            <div class="card-right">
                $state_png
            </div>
            <div id="pull-$card[ticket_KEY]" class="card-pull" onclick="$('#$card[ticket_KEY]').show(300);$(this).hide();">
                <img src="image/pull.png" height="16px"/>展开子票券
            </div>
        </div>
        <div hidden id="$card[ticket_KEY]" style="position:relative;padding:0 10px 10px 10px;margin: -0.5px 0 10px 15px">
            $html_sec
            <div id="pack-$card[ticket_KEY]" class="card-pack" onclick="$('#$card[ticket_KEY]').hide(300);$('#pull-$card[ticket_KEY]').show();">
                <img src="image/pack.png" height="16px"/> 收起子票券
            </div>
        </div>
LL;
                    }elseif($card["tic_type"] == 2){
                        $html .= <<<LL

        <div class="card">
            <div class="card-left">
                <div class="card-left-i">
                    <img src="$img_dir"/>
                </div>
                $card[type]
            </div>
            <div class="card-text" onclick="go('?file=ticketDetail&ticket_key=$card[ticket_KEY]')">
                <span style="background-color: #F49800">计时票</span> $card[title]<br/>
                券码：$card[ticket_KEY]<br/>
                购买日期：$card[create_time]<br/>
                失效日期：$card[end_time]
            </div>
            <div class="card-right">
                $state_png
            </div>
        </div>
LL;
                    }else{
                        $html .= <<<LL

        <div class="card">
            <div class="card-left">
                <div class="card-left-i">
                    <img src="$img_dir"/>
                </div>
                $card[type]
            </div>
            <div class="card-text" onclick="go('?file=ticketDetail&ticket_key=$card[ticket_KEY]')">
                $card[title]<br/>
                券码：$card[ticket_KEY]<br/>
                购买日期：$card[create_time]<br/>
                失效日期：$card[end_time]
            </div>
            <div class="card-right">
                $state_png
            </div>
        </div>
LL;
                    }
                }
                return $html;
                break;
            case "myTicSec":
                foreach ($data as $card){
                    $img_dir = Actions::getThumb("ticket",$card['tid']);
                    $state_png = Actions::getStatePng($card["state_name"]);
                    //$card["state_png"] = ($card["state_name"] == "已使用")?"<img src='image/used.png'>":"";
                    $html .= <<<LL

        <div class="card">
            <div class="card-left">
                <div class="card-left-i">
                    <img src="$img_dir"/>
                </div>
                $card[type]
            </div>
            <div class="card-text" onclick="go('?file=ticketDetail&ticket_key=$card[ticket_KEY]')">
                $card[title]<br/>
                券码：$card[ticket_KEY]<br/>
                失效日期：$card[end_time]
            </div>
            <div class="card-right">
                $state_png
            </div>
        </div>
LL;
                }
                return $html;
                break;
            case "ticDetail":
                $re_rnd_time = RND_KEY_LIVE_TIME - time() + strtotime($data['rnd_time']);
                $data['rnd_key'] = "<span class='rnd-key'>$data[rnd_key]</span>(<span class='rnd-sec'>{$re_rnd_time}</span>秒内有效)";
                if ($data["tic_type"] == 3){
                    $sql = "select a.*,b.state_name,c.type_name from a_mytickets a left join app_ticket_state b on a.state = b.state_id left join app_ticket_type c on a.type = c.id where a.uid = '".UID."' and a.father_KEY = '$data[ticket_KEY]'";
                    $data_sec = Mysql::query($sql, 1);
                    $html_sec = self::getTemp("myTicSec", $data_sec);
                    $html = <<<LL
<div class="ticket-detail">
    <div id="qrcode"></div>
    <div style="text-align: center;line-height: 20px">
        <span class='rnd-sec'>{$re_rnd_time}</span> 秒后刷新
    </div>
    <div class="main-ticket-detail" style="text-align: center;line-height: 28px">
        {$data['title']}<br/>
        套票券码：{$data['ticket_KEY']}<br/>
        购买日期：{$data['create_time']}<br/>
        此票券包含以下子票券：
    </div>
    $html_sec
    <script>
    ticCtDwn($re_rnd_time,"$data[ticket_KEY]");
    </script>
</div>
LL;
                }else{
                    if($data["tic_type"] == 2){
                        $left_sec = strtotime($data["end_time"])-time();
                        $left_hours = floor($left_sec%86400/3600);
                        $left_days = floor($left_sec/86400);
                        $state_add = ($left_sec > 0)?" (剩余 $left_days 天 $left_hours 小时)":"(已过期)";
                    }else{
                        $state_add = " (共{$data['orig_times']}次，剩{$data['times']}次)";
                    }
                    $data['state_name'] .= ($data['state'] == "3")?"":$state_add;
                    $html = <<<LL
<div class="ticket-detail">
    <div id="qrcode"></div>
    <div style="text-align: center;line-height: 20px">
        <span class='rnd-sec'>{$re_rnd_time}</span> 秒后刷新
    </div>
    <div class="main-ticket-detail" style="text-align: center;line-height: 28px">
        {$data['title']}<br/>
        券码：{$data['ticket_KEY']}<br/>
        状态：{$data['state_name']} <br/>
        购买日期：{$data['create_time']}<br/>
        可用日期：{$data['begin_time']}<br/>
        失效日期：{$data['end_time']}<br/>
LL;
                    $html.= empty($data["valid_time"])?"":<<<LL

        使用时间：{$data['valid_time']}<br/>
        使用地点：{$data['device_name']}<br/>
LL;
                    $html.= <<<LL
    </div>
    <script>
    ticCtDwn($re_rnd_time,"$data[ticket_KEY]");
    </script>
</div>
LL;
                }
                return $html;
                break;
            case "coupon":
                foreach ($data as $card){
                    $html .= <<<LL
            <div class="card" style="padding: 0">
                <div style="border-radius:5px;height:86px;text-align:center;width: 120px;line-height: 30px;color:#FFF;font-size: 14px;background-color: #02879B">
                    <div style="height: 55px;line-height: 65px;width: auto">
                        ￥<span style="font-size: 40px;font-weight: bold">$card[save]</span>
                    </div>
                    满￥$card[term]可用
                </div>
                <div style="border-radius:5px;line-height:25px;position:absolute;top:0;right:0;left:120px;padding: 5px 10px">
                    全商城票券可用<br/>
                    生效日期：$card[begin_time]<br/>
                    失效日期：$card[end_time]<br/>
                </div>
                <div onclick="msg('删除优惠券功能')" class="ticket-cart-del" style="top: 5px;right: 10px">
                    <img src="image/cart-del.png" style="height: 100%">
                </div>

            </div>
LL;
                }
                break;
            case "confirm":
                $list_html = self::getTemp("confirm_list",$data);
                $pay_info = Cart::getSaleInfo();
                $html = <<<LL
        <div class="card-list" style="padding-bottom: 200px">
            $list_html
        </div>
        <div class="info-bottom" style="bottom: 45px">
            总数量：<b>$pay_info[total_num]</b> 份<br/>
            原票价：<b>$pay_info[total_price]</b> 元<br/>
            满减折扣：<b>$pay_info[save_money]</b> 元<br/>
            折后价：<b>$pay_info[final_price]</b> 元<br/>
            代金券：暂无代金券<br/>
        </div>
        <div class="cart-price" style="bottom: 0">
            实付款:￥<b style="color: red">$pay_info[final_price]</b>元
        </div>
        <div class="cart-gocash" style="bottom: 0" onclick="go('?file=createOrder')">
            提交订单($pay_info[total_num])
        </div>
LL;
                return $html;
                break;
            case "buy":
                $options = Actions::getOpt($data[0]["num"],99);
                $list_html = self::getTemp("confirm_list",$data);
                $cart_arr = [$data[0]['tid']=>$data[0]['num']];
                $cart_json = json_encode($cart_arr);
                $sale_data = Cart::getSaleInfoByJson($cart_json);
                $html = <<<LL
        <div class="card-list" style="padding-bottom: 200px">
            $list_html
        </div>
        <div class="info-bottom" style="bottom: 45px">
            数量：
            <select style="width:50px;font-size: 20px;font-family: '黑体',serif" onchange="go('?file=buy&tid={$data[0]["tid"]}&num='+$(this).val())">
                $options
            </select>
            <br/>
            原票价：<b>$sale_data[total_price]</b> 元<br/>
            满减折扣：<b>$sale_data[save_money]</b> 元<br/>
            折后价：<b>$sale_data[final_price]</b> 元<br/>
            代金券：暂无代金券
        </div>
        <div class="cart-price" style="bottom: 0">
            应付款:￥<b style="color: red">$sale_data[final_price]</b>元
        </div>
        <div class="cart-gocash" style="bottom: 0" onclick="go('?file=createOrder&tid={$data[0]["tid"]}&num={$data[0]["num"]}')">
            提交订单($sale_data[total_num])
        </div>
LL;
                return $html;
                break;
            case "confirm_list":
                foreach ($data as $row){
                    $img_dir = Actions::getThumb("ticket",$row['tid']);
                    if ($row["tic_type"] == 3){
                        $sql = "select a.father_tid,b.*,'$row[num]' as num from a_tickets_child a,a_tickets b where a.father_tid = '$row[tid]' and a.child_tid = b.tid";
                        $data_sec = Mysql::query($sql,1);
                        $html_sec = self::getTemp("confirm_list",$data_sec,$p);
                        $html .= <<<LL
            
        <div class="card" style="border-top: solid 0.5px #02879B;border-bottom: solid 0.5px #02879B">
            <div class="card-left">
                <div class="card-left-i" style="line-height: 45px">
                    <img src="$img_dir"/>
                </div>
                套票
            </div>
            <div class="card-text">
                $row[title]<br/>
                组合价：$row[price]元<br/>
                数量：$row[num]份（详情见列表）
            </div>
            <div id="pull-$row[tid]" class="card-pull" onclick="$('#$row[tid]').show(300);$(this).hide();">
                <img src="image/pull.png" height="16px"/>展开列表
            </div>
        </div>
        <div hidden id="$row[tid]" style="position:relative;padding:0 10px 10px 10px;margin: -0.5px 0 10px 15px">
            $html_sec
            <div id="pack-$row[tid]" class="card-pack" onclick="$('#$row[tid]').hide(300);$('#pull-$row[tid]').show();">
                <img src="image/pack.png" height="16px"/> 收起列表
            </div>
        </div>
LL;
                    }elseif($row["tic_type"] == 2){
                        $html .= <<<LL
            
        <div class="card" style="border-top: solid 0.5px #02879B;border-bottom: solid 0.5px #02879B">
            <div class="card-left">
                <div class="card-left-i" style="line-height: 45px">
                    <img src="$img_dir"/>
                </div>
                $row[type]
            </div>
            <div class="card-text">
                $row[title]<br/>
                次数：不限次数<br/>
                有效时间：$row[valid_days]天
            </div>
        </div>
LL;
                    }else{
                        $html .= <<<LL
            
        <div class="card" style="border-top: solid 0.5px #02879B;border-bottom: solid 0.5px #02879B">
            <div class="card-left">
                <div class="card-left-i" style="line-height: 45px">
                    <img src="$img_dir"/>
                </div>
                $row[type]
            </div>
            <div class="card-text">
                $row[title]<br/>
                次数：$row[times]次<br/>
                有效时间：$row[end_time]前
            </div>
        </div>
LL;
                    }
                }
                return $html;
                break;
            case "cashier":
                $save_price = $data[0]["orig_price"] - $data[0]["price"];
                $order_time = ORDER_LIVE_TIME - time() + strtotime($data[0]["create_time"]);
                $order_min = ceil(($order_time - 59 ) / 60);
                $order_sec = $order_time % 60;
                $html = <<<LL

            <div class="cashier">
                <script>
                    var order_time = $order_time,
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
                            order_min = Math.ceil(order_time / 60) - 1;
                            order_time = order_time - 1;
                            order_sec = order_time % 60;
                            $(".order-min").html(order_min);
                            $(".order-sec").html(order_sec);
                        },1000)
                    }
                </script>
                <div class="cashier-tip">
                    请在
                    <span class="order-left-time order-min">$order_min</span>
                    分
                    <span class="order-left-time order-sec">$order_sec</span>
                    秒内完成支付，超时订单将自动取消！
                </div>
                <div class="pay-info">
                    订单号：{$data[0]["oid"]}<br/>
                    总票数：{$data[0]['num']} 张<br/>
                    原票价：{$data[0]['orig_price']} 元<br/>
                    满减折扣：$save_price 元<br/>
                    折后价：{$data[0]['price']} 元<br/>
                    代金券：未使用代金券<br/>
                    实际支付：{$data[0]['price']} 元
                </div>
                <div class="pay-area">
                    <div class="payment" style="background-color: #F59701" onclick="payOrder('{$data[0]["oid"]}')">
                        <img src="image/money.png" height="30px"/> 余额支付
                    </div>
                    <div class="payment" style="background-color: #30C430">
                        <img src="image/wechat.png" style="border: solid 2px #FFF;border-radius: 5px" height="30px"/> 微信支付
                    </div>
                    <div class="payment" style="background-color: #00AAEF">
                        <img src="image/alipay.png" style="border: solid 2px #FFF;border-radius: 5px" height="30px"/> 支付宝
                    </div>
                    <div class="payment" style="background-color:#EA2A29" onclick="go('?file=myOrders');">
                        <img src="image/no-pay.png" height="30px"/> 退出支付
                    </div>
                </div>              
            </div>
LL;
                return $html;
                break;
            case "myTicList":
                foreach ($data as $row){
                    $img_dir = Actions::getThumb("ticket",$row['tid']);
                    if ($row["tic_type"] == 3){
                        $sql_sec = "select a.*,$row[num] as num from a_mytickets a where a.father_KEY = '$row[ticket_KEY]'";
                        $data_sec = Mysql::query($sql_sec,1);
                        $html_sec = self::getTemp("myTicList",$data_sec);
                        $html .= <<<LL
            
        <div class="card" style="border-top: solid 0.5px #02879B">
            <div class="card-left">
                <div class="card-left-i" style="line-height: 45px">
                    <img src="$img_dir">
                </div>
                套餐
            </div>
            <div class="card-text">
                $row[title]<br/>
                组合价：$row[price]元<br/>
                数量：$row[num]份（详情见列表）
            </div>
            <div id="pull-$row[tid]" class="card-pull" onclick="$('#$row[tid]').show(300);$(this).hide();">
                <img src="image/pull.png" height="16px"/>展开列表
            </div>

        </div>
        <div hidden id="$row[tid]" style="position:relative;padding:0 10px 10px 10px;margin: -0.5px 0 10px 15px">
            $html_sec
            <div id="pack-$row[tid]" class="card-pack" onclick="$('#$row[tid]').hide(300);$('#pull-$row[tid]').show();">
                <img src="image/pack.png" height="16px"/> 收起列表
            </div>
        </div>
LL;
                    }else{
                        $html .= <<<LL
            
        <div class="card">
            <div class="card-left">
                <div class="card-left-i" style="line-height: 45px">
                    <img src="$img_dir">
                </div>
                $row[type]
            </div>
            <div class="card-text">
                $row[title]<br/>
                单价：$row[price]元<br/>
                数量：$row[num]张（$row[type]）
            </div>
        </div>
LL;
                    }

                }
                return $html;
                break;
            case "refund":
                $list_html = self::getTemp("myTickets",$data);
                $sql = "select a.oid,a.price,a.num,b.state_name from a_orders a left join app_order_state b on b.state_id = a.state where a.oid = '{$data[0]['oid']}' and a.uid = '$uid'";

                if ($data = Mysql::query($sql,1)){
                    $html = <<<LL
                <div class="card-list" style="padding:0 0 180px 0">
                    $list_html
                </div>
                <div class="info-bottom" style="bottom: 45px">
                    支付金额：{$data[0]['price']} 元<br/>
                    票数：{$data[0]['num']} 张<br/>
                    当前状态：{$data[0]['state_name']}<br/>
                    支付方式：余额支付<br/>
                    提示：退款将按原支付方式退回<br/>
                </div>
                <div style="text-align:center;font-size:18px;line-height:45px;position:absolute;width:50%;background-color:#FFF;bottom: 0;left: 0;border-top: solid 0.5px #363636" onclick="window.location.href='?file=myOrders'">
                    暂时不退
                </div>
                <div style="text-align:center;font-size:18px;color:#FFF;line-height:45px;position:absolute;width:50%;background-color:#02879B;bottom: 0;right: 0" onclick="refund_true('{$data[0]['oid']}');">
                    一定要退
                </div>
LL;
                }else{
                    $html = <<<LL
                    <script>
                        warn("找不到订单号退票信息！");
                        setTimeout(function() {
                            go("?file=myOrders");
                        },2000)
                    </script>
LL;

                }

                return $html;
                break;
            case "tradeRec":
                foreach ($data as $row){
                    $i++;
                    $html .= <<<LL

        <div class="card" onclick="warn('暂无详细信息！')">
            <div class="card-text">
                交易号：$row[trade_num]<br/>
                订单号：$row[oid]<br/>
                交易金额：$row[money] 元 <span>$row[payment]</span><br/>
                交易时间：$row[create_time]
            </div>
        </div>
LL;
                }
                return $html;
                break;
            case "orderDetail":
                $list_html = self::getTemp("myTickets",$data,$p,"此订单下无票券！");
                $sql = "select * from a_orders where oid = '{$data[0]['oid']}'";
                $data = Mysql::query($sql,1);
                $div_html = "";
                if (!empty($data)){
                    $div_html = "订单号：{$data[0]['oid']}<br/>";
                    $div_html.= "创建时间：{$data[0]['create_time']}<br/>";
                    $div_html.= ($data[0]["state"] == 1)||($data[0]["state"] == 3)?"支付时间：{$data[0]['pay_time']}<br/>":"";
                    $div_html.= ($data[0]["state"] >= 2)?"取消时间：{$data[0]['cancel_time']}<br/>":"";
                }
                $html = <<<LL

    <div class="card-list" style="padding-bottom: 70px">
        <div class="info-top">
            $div_html
        </div>
        $list_html
    </div>
LL;
                return $html;
                break;
            default:
                break;
            }
        $html = self::addTailHtml($html,$ept_msg);
        return $html;
    }

    //html加工，添加没有更多或提示为空信息
    static function addTailHtml($html,$ept_msg){
        if (empty($html)){
            $html = <<<LL
            
            <script>
                warn("$ept_msg");
            </script>
LL;
        }else{
            $html.=<<<LL
            
            <div class="no-more"> 
                <img src="image/no-more.png" height="28px"/><br/>
                没有更多了
            </div>
LL;
        }
        return $html;
    }

}