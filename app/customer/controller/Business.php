<?php

class Business
{
    static function getBusiness($GET){
        $bid = empty($GET["bid"])?"":$GET["bid"];
        $sql = "select a.* from a_tickets a where a.bid = '$bid'";
        $data = Mysql::query($sql,1);
        $ticket_html = empty($data)?<<<LL
<div class="main-ticket-detail">
    此商家暂无票券售卖
</div>
LL
:<<<LL
<div class="main-ticket-detail">
    此商家有以下票券在售：
</div>
LL;
        $ticket_html .= MobileTemplate::getTemp("tickets",$data,"","此商家暂无票券售卖");

        $sql = "select * from a_business where bid = '$bid'";
        $data = Mysql::query($sql,1);
        $html = "";

        if($data){
            $pic_group = Actions::getPicUrl("business",$bid);
            $swiper_html = Template::getSwiper($pic_group);
            $html = <<<LL
            <div class="card-list" style="padding-bottom: 70px">
                $swiper_html
                <div class="main-title">
                    {$data[0]["name"]}
                </div>
                <div class="main-ticket-info">
                    <div style="position:absolute;height: 22px;width:22px;"><img src="image/phone.png" height="100%"/></div>
                    <div style="color:#02879B;padding: 0 0 0 30px">{$data[0]['phone']}&nbsp;</div>
                </div>
                <div class="main-ticket-info">
                    <div style="position:absolute;height: 22px;width:22px;"><img src="image/address.png" height="100%"/></div>
                    <div style="color:#02879B;padding: 0 0 0 30px">{$data[0]['address']}&nbsp;</div>
                </div>
                $ticket_html
                <div class="main-ticket-detail">
                    商家信息：<br/>
                    <span style="padding: 0 10px"></span>{$data[0]['info']}
                </div>
            </div>
LL;
        }
        return $html;
    }

    static function getBusinessList(){
        $sql = "select * from a_business";
        $data = Mysql::query($sql,1);
        return $data;
    }

}