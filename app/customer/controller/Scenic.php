<?php

class Scenic
{
    static function getScenic($GET){
        $pid = empty($GET["pid"])?"":$GET["pid"];
        $sql = "select a.* from a_tickets a where a.pid = '$pid'";
        $data = Mysql::query($sql,1);
        $ticket_html = empty($data)?<<<LL
<div class="main-ticket-detail">
    此景点暂无票券售卖
</div>
LL
:<<<LL
<div class="main-ticket-detail">
    这个景区有以下票券在售：
</div>
LL;
        $ticket_html .= MobileTemplate::getTemp("tickets",$data,"","此景点暂无票券售卖");

        $sql = "select * from a_scenic where pid = '$pid'";
        $data = Mysql::query($sql,1);
        $html = "";

        if($data){
            $pic_group = Actions::getPicUrl("scenic",$pid);
            $swiper_html = Template::getSwiper($pic_group);
            $html = <<<LL
            <div class="card-list" style="padding-bottom: 70px">
                $swiper_html
                <div class="main-title">
                    {$data[0]["name"]}
                </div>
                <div class="main-ticket-info">
                    <div style="position:absolute;height: 22px;width:22px;"><img src="image/address.png" height="100%"/></div>
                    <div style="color:#02879B;padding: 0 0 0 30px">{$data[0]['address']}</div>
                </div>
                $ticket_html
                <div class="main-ticket-detail">
                    景点信息：<br/>
                    <span style="padding: 0 10px"></span>{$data[0]['info']}
                </div>
            </div>
LL;
        }
        return $html;
    }

    static function getScenicList(){
        $sql = "select * from a_scenic";
        $data = Mysql::query($sql,1);

        return $data;
    }

}