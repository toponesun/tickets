<?php

class Template
{
    static function getTemp($tmp_sty,$data){
        if (empty($data)){
            return self::addTailHtml("");
        }
        $html = "";
        $page_name = Actions::getPageNameByFileName($tmp_sty);
        $excel_file_name = $page_name." ".date("YmdHis");
        $i = 0;
        $export_html = <<<LL
            <div class="export-area"> 
                <a class="btn btn-xs btn-success" onclick="printTable('$tmp_sty');" href="#">打印表格</a>
                <a id="excelOut" class="btn btn-xs btn-success" download="$excel_file_name" onclick="tableToExcel('$tmp_sty', '$page_name');" href="#">导出Excel</a>
            </div>
LL;
        switch ($tmp_sty){
            case "all-tickets":
                foreach ($data as $row){
                    $i++;
                    $state_class = ($row["state"] == 1)?"able":"disable";
                    $state_name = ($row["state"] == 1)?"在售":"下架";
                    switch ($row["tic_type"]){
                        case 1:
                            $tic_type = "次数票";
                            break;
                        case 2:
                            $tic_type = "时长票";
                            break;
                        case 3:
                            $tic_type = "组合票";
                            break;
                        default:
                            $tic_type = "";
                            break;
                    }
                    switch ($row["tic_type"]){
                        case 1:
                            $valid_datetime = "> $row[begin_time]<br/>< $row[end_time]";
                            break;
                        case 2:
                            $valid_datetime = "购买后$row[valid_days]天内可用";
                            break;
                        case 3:
                            $valid_datetime = "> $row[begin_time]<br/>< $row[end_time]";
                            break;
                        default:
                            $valid_datetime = "未知的票券种类";
                            break;
                    }
                    $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td>$row[tid]</td>
                    <td>$row[scenic_name]</td>
                    <td>$row[business_name]</td>
                    <td>$row[title]</td>
                    <td>$row[price]</td>
                    <td>$row[stock]</td>
                    <td>$valid_datetime</td>
                    <td>$tic_type</td>
                    <td>$row[type]</td>
                    <td>$row[city]</td>
                    <td>$row[sale_name]</td>
                    <td>$row[update_time]</td>
                    <td class="$state_class">$state_name</td>
                    <td class="no-export" width="100px">
                        <button class="btn btn-xs btn-info" onclick="getFrame('EditTicket','$row[tid]')">修改</button>
                        <button class="btn btn-xs btn-danger" onclick="ask('警告！此操作将会删除这个票券，是否仍要继续？','delInfo(\'Ticket\',\'$row[tid]\')');">删除</button>
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th>序号</th>
                    <th>TID</th>
                    <th style="width: 80px">所属景点</th>
                    <th>所属商家</th>
                    <th style="width: 150px">标题</th>
                    <th>价格</th>
                    <th>库存</th>
                    <th style="width: 180px">有效时间</th>
                    <th>类型</th>
                    <th>种类</th>
                    <th>城市</th>
                    <th>参与优惠</th>
                    <th style="width: 100px">上次修改</th>
                    <th>状态</th>
                    <th class="no-export">操作</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "all-tic-type":
                foreach ($data as $row){
                    $i++;
                    $state_class = ($row["state"] == 1)?"able":"disable";
                    $state_name = ($row["state"] == 1)?"正常":"失效";
                    $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td>$row[type_name]</td>
                    <td>$row[update_time]</td>
                    <td class="$state_class">$state_name</td>
                    <td class="no-export" width="100px">
                        <button class="btn btn-xs btn-info" onclick="getFrame('EditTicType','$row[id]')">修改</button>
                        <button class="btn btn-xs btn-danger" onclick="ask('警告！此操作将会删除这个种类，是否仍要继续？','delInfo(\'TicType\',\'$row[id]\')');">删除</button>
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th>序号</th>
                    <th>票券种类（名称）</th>
                    <th>上次修改</th>
                    <th>状态</th>
                    <th class="no-export">操作</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "all-scenic":
                foreach ($data as $row){
                    $i++;
                    $state_class = ($row["state"] == 1)?"able":"disable";
                    $state_name = ($row["state"] == 1)?"正常":"下线";
                    $row["info"] = mb_substr($row["info"],0,28,'utf-8')."...";
                    $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td>$row[pid]</td>
                    <td>$row[name]</td>
                    <td>$row[city]</td>
                    <td>$row[address]</td>
                    <td>$row[info]</td>
                    <td>$row[update_time]</td>
                    <td class="$state_class">$state_name</td>
                    <td class="no-export">
                        <button class="btn btn-xs btn-info" onclick="getFrame('EditScenic','$row[pid]')">修改</button>
                        <button class="btn btn-xs btn-danger" onclick="ask('警告！此操作将会删除这个景点，是否仍要继续？','delInfo(\'Scenic\',\'$row[pid]\')');">删除</button>
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th>序号</th>
                    <th>PID</th>
                    <th style="width: 180px">景点名称</th>
                    <th>城市</th>
                    <th>详细地址</th>
                    <th style="width: 300px">景点介绍</th>
                    <th style="width: 100px">上次修改</th>
                    <th>状态</th>
                    <th class="no-export">操作</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "all-business":
                foreach ($data as $row){
                    $i++;
                    $state_class = ($row["state"] == 1)?"able":"disable";
                    $state_name = ($row["state"] == 1)?"正常":"停业";
                    $row["info"] = mb_substr($row["info"],0,28,'utf-8')."...";
                    $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td>$row[bid]</td>
                    <td>$row[name]</td>
                    <td>$row[city]</td>
                    <td>$row[phone]</td>
                    <td>$row[address]</td>
                    <td>$row[info]</td>
                    <td>$row[update_time]</td>
                    <td class="$state_class">$state_name</td>
                    <td class="no-export">
                        <button class="btn btn-xs btn-info" onclick="getFrame('EditBusiness','$row[bid]')">修改</button>
                        <button class="btn btn-xs btn-danger" onclick="ask('警告！将要删除商家“$row[name]”，此操作无法撤销，是否仍要继续？','delInfo(\'Business\',\'$row[bid]\')');">删除</button>
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th>序号</th>
                    <th>BID</th>
                    <th style="width: 180px">商家名称</th>
                    <th>城市</th>
                    <th>联系方式</th>
                    <th>详细地址</th>
                    <th style="width: 300px">商家介绍</th>
                    <th style="width: 100px">上次修改</th>
                    <th>状态</th>
                    <th class="no-export">操作</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "all-sale":
                foreach ($data as $row){
                    $i++;
                    $state_class = ($row["state"] == 1)?"able":"disable";
                    $state_name = ($row["state"] == 1)?"可用":"失效";
                    $html .= <<<LL
            
                <tr>
                    <td>$i</td>
                    <td>$row[sale_id]</td>
                    <td>$row[sale_type]</td>
                    <td>$row[sale_name]</td>
                    <td>$row[term_price]</td>
                    <td>$row[term_num]</td>
                    <td>$row[save_percent]</td>
                    <td>$row[save_money]</td>
                    <td>> $row[begin_time]<br/>< $row[end_time]</td>
                    <td>$row[update_time]</td>
                    <td class="$state_class">$state_name</td>
                    <td class="no-export">
                        <button class="btn btn-xs btn-info" onclick="getFrame('EditSale','$row[sale_id]')">修改</button>
                        <button class="btn btn-xs btn-danger" onclick="ask('警告！此操作将会删除这个优惠，是否仍要继续？','delInfo(\'Sale\',\'$row[sale_id]\')');">删除</button>
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th>序号</th>
                    <th>SALE_ID</th>
                    <th>优惠方式</th>
                    <th>优惠描述</th>
                    <th>优惠条件1(金额)</th>
                    <th>优惠条件2(张数)</th>
                    <th>优惠内容1(折扣)</th>
                    <th>优惠内容2(金额)</th>
                    <th>有效时间</th>
                    <th style="width: 100px">上次修改</th>
                    <th>状态</th>
                    <th class="no-export">操作</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "all-device":
            foreach ($data as $row){
                $i++;
                $state_class = ($row["state"] == 1)?"able":"disable";
                $state_name = ($row["state"] == 1)?"正常":"离线";
                $position = empty($row["device_is_entrance"])?"出口":"入口";
                $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td>$row[device_id]</td>
                    <td>$row[device_name]</td>
                    <td>$row[device_address]</td>
                    <td>进入修改查看</td>
                    <td>$position</td>
                    <td>$row[update_time]</td>
                    <td class="$state_class">$state_name</td>
                    <td class="no-export">
                        <button class="btn btn-xs btn-info" onclick="getFrame('EditDevice','$row[device_id]')">修改</button>
                        <button class="btn btn-xs btn-danger" onclick="ask('警告！此操作将会删除这个设备，是否仍要继续？','delInfo(\'Device\',\'$row[device_id]\')');">删除</button>
                    </td>
                </tr>
LL;
            }
            $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th>序号</th>
                    <th>Device_ID</th>
                    <th>设备名称</th>
                    <th>设备地址</th>
                    <th>设备可识别的tid</th>
                    <th>位置</th>
                    <th style="width: 100px">上次修改</th>
                    <th>状态</th>
                    <th class="no-export">操作</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "all-web-set":
                foreach ($data as $row){
                    $i++;
                    $state_class = ($row["state"] == 1)?"able":"disable";
                    $state_name = ($row["state"] == 1)?"生效":"失效";
                    $need_out_valid = ($row["need_out_valid"] == 1)?"是":"否";
                    $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td><img width="40px" height="40px" src="image/logo/logo.jpg"/></td>
                    <td>$row[title_suffix]</td>
                    <td>$row[footer]</td>
                    <td>$row[session_live_time] 秒</td>
                    <td>$row[order_live_time] 秒</td>
                    <td>$row[rnd_key_live_time] 秒</td>
                    <td>$row[rows_per_page] 行</td>
                    <td>$row[valid_interval] 秒</td>
                    <td>$need_out_valid</td>
                    <td>$row[update_time]</td>
                    <td class="$state_class">$state_name</td>
                    <td class="no-export">
                        <button class="btn btn-xs btn-info" onclick="getFrame('EditWebSet','$row[id]')">修改</button>
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th>序号</th>
                    <th>网站Logo</th>
                    <th>网站标题后缀</th>
                    <th>网站底部文字</th>
                    <th>session存活时间</th>
                    <th>订单支付时间</th>
                    <th>随机码有效时间</th>
                    <th>每页显示</th>
                    <th>验票间隔</th>
                    <th>需要验出站</th>
                    <th style="width: 100px">上次修改</th>
                    <th>状态</th>
                    <th class="no-export">操作</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "auto-update":
                foreach ($data as $row){
                    $i++;
                    $state_class = ($row["state"] == 1)?"able":"disable";
                    $state_name = ($row["state"] == 1)?"生效":"失效";
                    $days = floor($row["up_time_span"] / 86400);
                    $hours = floor(($row["up_time_span"] % 86400)/3600);
                    $min = floor(($row["up_time_span"] % 3600)/60);

                    $html .= <<<LL
                    <tr>
                    <td>$i</td>
                    <td>$row[tid]</td>
                    <td>$row[title]</td>
                    <td>$row[begin_time]</td>
                    <td>$row[end_time]</td>
                    <td>$row[up_last_time]</td>
                    <td>{$days}天{$hours}时{$min}分</td>
                    <td>$row[up_stock]</td>
                    <td class="$state_class">$state_name</td>
                    <td class="no-export"> 
                        <button class="btn btn-xs btn-info" onclick="getFrame('EditTicket','$row[tid]')">修改</button>
                        <button class="btn btn-xs btn-danger" onclick="ask('警告！此操作将会删除这条设置，是否仍要继续？','delInfo(\'AutoUpdate\',\'$row[id]\')');">删除</button>
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th>序号</th>
                    <th>TID</th>
                    <th>票名</th>
                    <th>开始更新</th>
                    <th>结束更新</th>
                    <th>最近更新</th>
                    <th>更新间隔（时长）</th>
                    <th>设置库存</th>
                    <th>状态</th>
                    <th class="no-export">操作</th>
                </tr>
                $html
            </table>
        </div>
LL;

                break;
            case "order-form";
                foreach ($data as $row){
                    $i++;
                    $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td>$row[date]</td>
                    <td>$row[order_num]</td>
                    <td>$row[success_num]</td>
                    <td>$row[paying_num]</td>
                    <td>$row[cancel_num]</td>
                    <td>$row[refund_num]</td>
                    <td>$row[total_orig_price]</td>
                    <td>$row[total_price]</td>
                    <td>
                        <span class="pc-span">$row[pc_buy]</span><span hidden>(电脑)</span>
                        <span class="mobile-span">$row[mobile_buy]</span><span hidden>(手机)</span>
                        <span class="conductor-span">$row[conductor_buy]</span><span hidden>(窗口)</span>
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th colspan="10">订单报表 时间：{$data[0]["begin_time"]} - {$data[0]["end_time"]}<span id="term-text"></span></td>
                </tr>
                <tr>
                    <th>序号</th>
                    <th>日期</th>
                    <th>产生订单</th>
                    <th>成交</th>
                    <th>待支付</th>
                    <th>取消</th>
                    <th>退单</th>
                    <th>总原价</th>
                    <th>总金额</th>
                    <th>下单渠道</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "ticket-form";
                foreach ($data as $row){
                    $i++;
                    $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td>$row[date]</td>
                    <td>$row[ticket_num]</td>
                    <td>$row[notuse_num]</td>
                    <td>$row[used_num]</td>
                    <td>$row[refund_num]</td>
                    <td>$row[outdate_num]</td>
                    <td>$row[normal_tic_num]</td>
                    <td>$row[time_tic_num]</td>
                    <td>$row[group_tic_num]</td>
                    <td>
                        <span class="pc-span">$row[pc_buy]</span><span hidden>(电脑)</span>
                        <span class="mobile-span">$row[mobile_buy]</span><span hidden>(手机)</span>
                        <span class="conductor-span">$row[conductor_buy]</span><span hidden>(窗口)</span>
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr id="thead">
                    <th colspan="15">票券报表 时间：{$data[0]["begin_time"]} - {$data[0]["end_time"]}<span id="term-text"></span></td>
                </tr>
                <tr>
                    <th>序号</th>
                    <th>日期</th>
                    <th>售出票券</th>
                    <th>有效</th>
                    <th>已用</th>
                    <th>退票</th>
                    <th>过期</th>
                    <th>常规票</th>
                    <th>计时票</th>
                    <th>套票</th>
                    <th>购票渠道</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "trade-form";
                foreach ($data as $row){
                    $i++;
                    $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td>$row[date]</td>
                    <td>$row[total_pay_money]元（$row[total_pay_num]笔）</td>
                    <td>$row[total_ref_money]元（$row[total_ref_num]笔）</td>
                    <td>$row[balance_pay_money]元（$row[balance_pay_num]笔）</td>
                    <td>$row[wechat_pay_money]元（$row[wechat_pay_num]笔）</td>
                    <td>$row[ali_pay_money]元（$row[ali_pay_num]笔）</td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
        $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th colspan="7">交易报表 时间：{$data[0]["begin_time"]} - {$data[0]["end_time"]}<span id="term-text"></span></th>
                </tr>
                <tr>
                    <th>序号</th>
                    <th>日期</th>
                    <th>总支付</th>
                    <th>总退款</th>
                    <th>余额支付</th>
                    <th>微信支付</th>
                    <th>支付宝</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            case "record-form";
                foreach ($data as $row){
                    $i++;
                    switch ($row["buy_way"]){
                        case "pc":
                            $buy_way = "<span class='pc-span'></span><span hidden>(电脑)</span>";
                            break;
                        case "mobile":
                            $buy_way = "<span class='mobile-span'></span><span hidden>(手机)</span>";
                            break;
                        case "conductor":
                            $buy_way = "<span class='conductor-span'></span><span hidden>(窗口)</span>";
                            break;
                        default:
                            $buy_way = "-";
                            break;
                    }

                    $html .= <<<LL
                <tr>
                    <td>$i</td>
                    <td>$row[create_time]</td>
                    <td>$row[is_child]$row[title]</td>
                    <td>$row[now_price]</td>
                    <td>$row[price]</td>
                    <td>$row[save_money]</td>
                    <td>$row[pay]</td>
                    <td>$row[state_name]</td>
                    <td>
                        $buy_way
                    </td>
                </tr>
LL;
                }
                $html = <<<LL
        <div class="table-box">
            $export_html
            <table id="$tmp_sty" class="my-table">
                <tr>
                    <th colspan="9">流水报表 时间：{$data[0]["begin_time"]} - {$data[0]["end_time"]}<span id="term-text"></span></th>
                </tr>
                <tr>
                    <th>序号</th>
                    <th>购买时间</th>
                    <th>票名</th>
                    <th>现价</th>
                    <th>购买价</th>
                    <th>折扣</th>
                    <th>实付</th>
                    <th>状态</th>
                    <th>购票渠道</th>
                </tr>
                $html
            </table>
        </div>
LL;
                break;
            default:
                break;
            }
        $html = self::addTailHtml($html);
        return $html;
    }

    static function getPicTemp($xid_name,$xid){
        switch ($xid_name){
            case "tid":
                $dir_name = "ticket";
                $frame_key = "EditTicket";
                break;
            case "pid":
                $dir_name = "scenic";
                $frame_key = "EditScenic";
                break;
            case "bid":
                $dir_name = "business";
                $frame_key = "EditBusiness";
                break;
            case "set_id":
                $dir_name = "logo";
                $frame_key = "EditWenSet";
                break;
            default:
                return false;
        }
        $dir = iconv("UTF-8", "GBK", BASE_PATH."pictures/$dir_name/$xid");
        $pic_html = <<<LL
        <script> 
            $(".upload-div").mouseenter(function() {
                $("#" + $(this).attr("id") + " div").show()
            });
            $(".upload-div").mouseleave(function() {
                $("#" + $(this).attr("id") + " div").hide()
            });
        </script>
LL;
        if (is_dir($dir)){
            if (is_file($dir."/index.jpg")){
                $version = date("YmdHis");
                $pic_html.= <<<LL
        <div class="upload-div"> 
            <img class="upload-img" src="pictures/$dir_name/$xid/index.jpg?v=$version"/>
            <div class="image-menu" style="width: 100%"> 
                当前主图
            </div>
        </div>
LL;
            }
            $files = scandir($dir);
            foreach ($files as $file){
                if (strstr($file,".jpg") && $file != "index.jpg"){
                    $div_id = str_replace(".jpg","",$file);
                    $pic_html.= <<<LL
        <div class="upload-div" id="$div_id"> 
            <img class="upload-img" src="pictures/$dir_name/$xid/$file"/>
            <div onclick='ask("将此文件设为主图？","renameFile(\"pictures/$dir_name/$xid\",\"$file\",\"index.jpg\");getFrame(\"$frame_key\",\"$xid\")")' class="image-menu" style="display: none"> 
                主图
            </div>
            <div onclick='ask("真的要删除这个文件么？","delFile(\"pictures/$dir_name/$xid/$file\");getFrame(\"$frame_key\",\"$xid\")")' class="image-menu" style="left:51%;display: none"> 
                删除
            </div>
        </div>
LL;
                }
            }
        }
        return $pic_html;
    }

    static function getLogo(){
        $dir = iconv("UTF-8", "GBK", BASE_PATH."image/logo");
        $pic_html = <<<LL
        <script> 
            $(".upload-div").mouseenter(function() {
                $("#" + $(this).attr("id") + " div").show()
            });
            $(".upload-div").mouseleave(function() {
                $("#" + $(this).attr("id") + " div").hide()
            });
        </script>
LL;
        if (is_file($dir."/logo.jpg") || is_file($dir."/logo.png")){
            $version = date("YmdHis");
            $pic_html.= <<<LL
        <div class="upload-div"> 
            <img class="upload-img" src="image/logo/logo.jpg?v=$version"/>
            <div class="image-menu" style="width: 100%"> 
                修改Logo
            </div>
        </div>
LL;
        }
        return $pic_html;
    }

    //html加工，添加没有更多或提示为空信息
    static function addTailHtml($html){
        if (empty($html)){
            $html = <<<LL
            <script>
                warn("没有更多了...");
            </script>
LL;
        }else{
            $html.=<<<LL
            
            <div class="no-more"> 
                没有更多了...
            </div>
LL;
        }
        return $html;
    }
}