<?php

class Tickets
{
    static $tickets_page;
    //获取可购买的票务信息分页版
    static function getTicketsInfo($GET)
    {
        $sql_head = "select a.* from a_tickets a where a.state = 1";
        //sql语句加入筛选条件
        $sql_body = empty($GET["stock"])?" AND a.stock >= 0":"a.stock > 0";
        $sql_body.= empty($GET["title"])?"":" AND a.title LIKE '%$GET[title]%'";
        $sql_body.= empty($GET["type"])?"":" AND a.type = '$GET[type]'";
        $sql_body.= empty($GET["city"])?"":" AND a.city = '$GET[city]'";
        //分离次数
        $times = empty($GET["times"])?"":explode("-",$GET["times"],2);
        $sql_body.= empty($times[1])?"":" AND a.times between '$times[0]' AND '$times[1]'";
        //分离价格
        $price = empty($GET["price"])?"":explode("-",$GET["price"],2);
        $sql_body.= empty($price)?"":" AND a.price between '$price[0]' AND '$price[1]'";
        //判断日期是否合法
        if (!empty($GET["start_time"]) && !empty($GET["end_time"])){
            $GET["start_time"].=" 00:00:00";
            $GET["end_time"].=" 23:59:59";
            $sql_body.= !(strtotime($GET["start_time"]) && strtotime($GET["end_time"]))?"":" AND a.begin_time <= '$GET[start_time]' AND a.end_time >= '$GET[end_time]'";
        }
        //按update_time倒序排列
        $sql_body.=" order by a.update_time DESC";

        //计算页码
        $sql_count = "select count(*) from a_tickets a where a.state = 1".$sql_body;
        $num = Mysql::query($sql_count,1);
        self::$tickets_page = ceil($num[0]["count(*)"] / ROWS_PER_PAGE);
        //获取范围内数据
        $begin_i = empty($GET["p"]) ? 0 : ($GET["p"]-1) * ROWS_PER_PAGE;
        $sql = $sql_head.$sql_body." limit $begin_i,".ROWS_PER_PAGE;
        $data = Mysql::query($sql,1);
        return $data;
    }

    //PC获取票券详细介绍
    static function getTicketsDetail($GET)
    {
        $tid = empty($GET["tid"])?"":$GET["tid"];
        $detail_html = "";

        $sql = "select a.*,b.sale_name,b.begin_time as sale_begin_time,b.end_time as sale_end_time from a_tickets a left join a_sale b on a.sale_id = b.sale_id where a.tid = '$tid' ";
        if ($data = Mysql::query($sql,1)){
            $data[0]['begin_time'] = date("Y/m/d",strtotime($data[0]['begin_time']));
            $data[0]['end_time'] = date("Y/m/d",strtotime($data[0]['end_time']));
            //$img_dir = Actions::getThumb("ticket",$tid);

            //这一段动态输出商品详情页面折扣信息
            $sale_area = "";
            if(!empty($data[0]["sale_id"])&&strtotime($data[0]["sale_end_time"])>time()){
                if (strtotime($data[0]["sale_begin_time"])<time()){
                    $second = strtotime($data[0]["sale_end_time"]) - time();
                    $sale_area = <<<LL
                <div style="background-color:#F22;color:#FFF;padding: 1px 10px 1px 10px;height: 28px;line-height:28px;font-size: 16px">
                    {$data[0]['sale_name']}（剩余 <a class="timespan" style="color: #FFF"></a>）
                </div>
                <script>
                  makeTimeCtDwn(".timespan","$second");
                </script>
LL;
                }else{
                    $second = time() - strtotime($data[0]["sale_begin_time"]);
                    $sale_area = <<<LL
                <div style="background-color:#3B707B;color:#FFF;padding: 0 10px 0 10px;height: 28px;line-height:28px;font-size: 16px">
                    {$data[0]['sale_name']}（距开始 <a class="timespan" style="color: #FFF"></a>）
                </div>
                <script>
                  makeTimeCtDwn(".timespan","$second");
                </script>
LL;
                }
            }

            //这里拼接商品详情页总的html
            $pic_group = Actions::getPicUrl("ticket",$tid);
            $swiper_html = Template::picGroupTemp($pic_group);

            if ($data[0]["tic_type"] == 3){
                $sql = "select a.father_tid,b.* from a_tickets_child a,a_tickets b where a.father_tid = '{$data[0]["tid"]}' and a.child_tid = b.tid";
                $data_group = Mysql::query($sql,1);
                $group_html = MobileTemplate::getTemp("child-tic",$data_group);
                $detail_html = <<<LL
                
            <div class="card-list">
                <div class="swiper"> 
                    $swiper_html
                    
                </div>
                
                $sale_area
                <div class="main-title">
                    <span style="background-color: #02879B">套票</span> {$data[0]['title']}
                </div>
                <div class="main-ticket-info">
                    <div style="position:absolute;bottom:35px;right:10px;color: #8c8c8c;font-size: 14px;line-height: 20px">原价 <del>￥{$data[0]['orig_price']}</del></div>
                    <div style="position:absolute;bottom:5px;right:10px;color: #F00;font-size: 28px;line-height: 30px">￥{$data[0]['price']}</div>
                    有效时间和类型请查看子票券<br/>
                    限时售卖<br/>
                    仅剩 {$data[0]['stock']} 份<br/>
                </div>
                <div class="main-ticket-detail">
                    此套餐已包含以下票券各1张：
                </div>
                $group_html
                <div class="main-ticket-detail">
                    {$data[0]['detail']}
                </div>
            </div>
LL;
            }elseif($data[0]["tic_type"] == 2){
                $detail_html = <<<LL
                
            <div class="card-list">
                $swiper_html
                $sale_area
                <div class="main-title">
                    <span style="background-color: #F49800">计时票</span> {$data[0]['title']}
                </div>
                <div class="main-ticket-info">
                    <div style="position:absolute;bottom:35px;right:10px;color: #8c8c8c;font-size: 14px;line-height: 20px">原价 <del>￥{$data[0]['orig_price']}</del></div>
                    <div style="position:absolute;bottom:5px;right:10px;color: #F00;font-size: 28px;line-height: 30px">￥{$data[0]['price']}</div>
                    购买后{$data[0]["valid_days"]}天内有效，每天限1次<br/>
                    节假日可正常使用<br/>
                    {$data[0]["type"]} / 余票 {$data[0]['stock']} 张
                </div>
                <div class="main-ticket-detail">
                    {$data[0]['detail']}
                </div>
            </div>
LL;
            }else{
                $detail_html = <<<LL
                
            <div class="card-list">
                $swiper_html
                $sale_area
                <div class="main-title">
                    {$data[0]['title']}
                </div>
                <div class="main-ticket-info">
                    <div style="position:absolute;bottom:35px;right:10px;color: #8c8c8c;font-size: 14px;line-height: 20px">原价 <del>￥{$data[0]['orig_price']}</del></div>
                    <div style="position:absolute;bottom:5px;right:10px;color: #F00;font-size: 28px;line-height: 30px">￥{$data[0]['price']}</div>
                    {$data[0]['begin_time']} 至 {$data[0]['end_time']}<br/>
                    期间可用 {$data[0]['times']} 次<br/>
                    {$data[0]['type']} / 余票 {$data[0]['stock']} 张
                </div>
                <div class="main-ticket-detail">
                    {$data[0]['detail']}
                </div>
            </div>
LL;
            }
        }
        return $detail_html;
    }

    //获取票券商品页面详细介绍
    static function getDetail($GET)
    {
        $tid = empty($GET["tid"])?"":$GET["tid"];
        $uid = UID;
        $detail_html = "";

        $sql = "select a.*,b.sale_name,b.begin_time as sale_begin_time,b.end_time as sale_end_time from a_tickets a left join a_sale b on a.sale_id = b.sale_id where a.tid = '$tid' ";
        if ($data = Mysql::query($sql,1)){
            $data[0]['begin_time'] = date("Y/m/d",strtotime($data[0]['begin_time']));
            $data[0]['end_time'] = date("Y/m/d",strtotime($data[0]['end_time']));
            //$img_dir = Actions::getPicUrl($tid);

            //这一段动态输出商品详情页面折扣信息
            $sale_area = "";
            if(!empty($data[0]["sale_id"])&&strtotime($data[0]["sale_end_time"])>time()){
                if (strtotime($data[0]["sale_begin_time"])<time()){
                    $second = strtotime($data[0]["sale_end_time"]) - time();
                    $sale_area = <<<LL
                <div style="background-color:#F22;color:#FFF;padding: 1px 10px 1px 10px;height: 28px;line-height:28px;font-size: 16px">
                    {$data[0]['sale_name']}（剩余 <a class="timespan" style="color: #FFF"></a>）
                </div>
                <script>
                  makeTimeCtDwn(".timespan","$second");
                </script>
LL;
                }else{
                    $second = time() - strtotime($data[0]["sale_begin_time"]);
                    $sale_area = <<<LL
                <div style="background-color:#3B707B;color:#FFF;padding: 0 10px 0 10px;height: 28px;line-height:28px;font-size: 16px">
                    {$data[0]['sale_name']}（距开始 <a class="timespan" style="color: #FFF"></a>）
                </div>
                <script>
                  makeTimeCtDwn(".timespan","$second");
                </script>
LL;
                }
            }

            //这里拼接商品详情页总的html
            $pic_group = Actions::getPicUrl("ticket",$tid);
            $swiper_html = Template::getSwiper($pic_group);

            if ($data[0]["tic_type"]==3){
                $sql = "select a.father_tid,b.* from a_tickets_child a,a_tickets b where a.father_tid = '{$data[0]["tid"]}' and a.child_tid = b.tid";
                $data_group = Mysql::query($sql,1);
                $group_html = MobileTemplate::getTemp("child-tic",$data_group);
                $detail_html = <<<LL
                
            <div class="card-list" style="padding-bottom: 150px">
                $swiper_html
                $sale_area
                <div class="main-title">
                    <span style="background-color: #02879B">套票</span> {$data[0]['title']}
                </div>
                <div class="main-ticket-info">
                    <div style="position:absolute;bottom:35px;right:10px;color: #8c8c8c;font-size: 14px;line-height: 20px">原价 <del>￥{$data[0]['orig_price']}</del></div>
                    <div style="position:absolute;bottom:5px;right:10px;color: #F00;font-size: 28px;line-height: 30px">￥{$data[0]['price']}</div>
                    有效时间和类型请查看子票券<br/>
                    限时售卖<br/>
                    仅剩 {$data[0]['stock']} 份<br/>
                </div>
                <div class="main-ticket-detail">
                    此套餐已包含以下票券各1张：
                </div>
                $group_html
                <div class="main-ticket-detail">
                    {$data[0]['detail']}
                </div>
            </div>
LL;
            }elseif($data[0]["tic_type"]==2){
                $detail_html = <<<LL
                
            <div class="card-list">
                $swiper_html
                $sale_area
                <div class="main-title">
                    <span style="background-color: #F49800">计时票</span> {$data[0]['title']}
                </div>
                <div class="main-ticket-info">
                    <div style="position:absolute;bottom:35px;right:10px;color: #8c8c8c;font-size: 14px;line-height: 20px">原价 <del>￥{$data[0]['orig_price']}</del></div>
                    <div style="position:absolute;bottom:5px;right:10px;color: #F00;font-size: 28px;line-height: 30px">￥{$data[0]['price']}</div>
                    购买后{$data[0]["valid_days"]}天内有效，每天限1次<br/>
                    节假日可正常使用<br/>
                    {$data[0]["type"]} / 余票 {$data[0]['stock']} 张
                </div>
                <div class="main-ticket-detail">
                    {$data[0]['detail']}
                </div>
            </div>
LL;
            }else{
                $detail_html = <<<LL
                
            <div class="card-list">
                $swiper_html
                $sale_area
                <div class="main-title">
                    {$data[0]['title']}
                </div>
                <div class="main-ticket-info">
                    <div style="position:absolute;bottom:35px;right:10px;color: #8c8c8c;font-size: 14px;line-height: 20px">原价 <del>￥{$data[0]['orig_price']}</del></div>
                    <div style="position:absolute;bottom:5px;right:10px;color: #F00;font-size: 28px;line-height: 30px">￥{$data[0]['price']}</div>
                    {$data[0]['begin_time']} 至 {$data[0]['end_time']}<br/>
                    期间可用 {$data[0]['times']} 次<br/>
                    {$data[0]['type']} / 余票 {$data[0]['stock']} 张
                </div>
                <div class="main-ticket-detail">
                    {$data[0]['detail']}
                </div>
            </div>
LL;
            }
        }

        $scenic_onclick = empty($data[0]["pid"])?"warn('此票券所属景点暂未收录！')":"go('?file=scenic&pid={$data[0]["pid"]}')";
        $business_onclick = empty($data[0]["bid"])?"warn('此票券所属商家暂未收录！')":"go('?file=business&bid={$data[0]["bid"]}')";
        $favor_img = "image/favor-true.png";
        $sql_favor = "select * from a_favor where uid = '$uid' and tid = '$tid'";
        $data_favor = Mysql::query($sql_favor,1);
        if (empty($data_favor))
            $favor_img = "image/favor-false.png";
        $detail_html .=<<<LL
            <div class="body-menu">
                <div class="detail-menu" style="background-color: #FFF">
                    <div class="detail-menu-box" onclick="$business_onclick">
                        <img src="image/business.png">
                    </div>
                    <div class="detail-menu-box" onclick="$scenic_onclick">
                        <img src="image/scenic.png"><br/>
                    </div>
                    <div class="detail-menu-box" onclick="favor('$tid');">
                        <img id="favor-img" src="$favor_img">
                    </div>
                </div>
                <div onclick="addToCart('$tid');" style="background-color:#F49800;border-left:solid 0.5px #FFF;position:absolute;right: 90px;font-size:16px;height: 50px;line-height: 50px;width: 110px;color: #FFF;text-align: center;">加入购物车</div>
                <div onclick="go('?file=buy&tid=$tid');" style="background-color:#F00;position:absolute;right: 0;font-size:16px;height: 50px;line-height: 50px;width: 90px;color: #FFF;text-align: center;">立即购买</div>
            </div>
LL;
        return $detail_html;
    }
}