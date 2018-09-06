<?php

class WebSet
{
    static function getAllWebSet(){
        $sql = "select * from sys_web_set order by update_time desc";
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("all-web-set",$data);
        return $html;
    }

    //获取设置编辑页面
    static function getEditWebSet($set_id)
    {
        $pic_html = Template::getLogo();
        $sql = "select * from sys_web_set where id = '$set_id'";
        $data = Mysql::query($sql,1);
        $html = "没有找到数据！";
        if (!empty($data)){
            if ($data[0]["need_out_valid"] == 1){
                $need_out = <<<LL
                    <option value="1" selected>是</option>
                    <option value="0">否</option>
LL;
            }else{
                $need_out = <<<LL
                    <option value="1">是</option>
                    <option value="0" selected>否</option>
LL;
            }
            $html = <<<LL
        <div class="form-area">
            <form id="add-web-set">
                <label for="title-suffix">网站标题后缀：</label>
                <input id="title-suffix" type="text" style="width: 400px" placeholder="请填写网站标题后缀" value="{$data[0]["title_suffix"]}"/><br/>
                <label for="footer-text">网站底部文字：</label>
                <input id="footer-text" type="text" style="width: 400px" placeholder="请填写网站底部文字" value="{$data[0]["footer"]}"/><br/>
                <label for="session-live-time">SESSION存活时间：</label>
                <input id="session-live-time" type="number" style="width: 100px" placeholder="单位（秒）" value="{$data[0]["session_live_time"]}"/>（秒）<br/>
                <label for="order-live-time">未支付订单保留时间：</label>
                <input id="order-live-time" type="number" style="width: 100px" placeholder="单位（秒）" value="{$data[0]["order_live_time"]}"/>（秒）<br/>
                <label for="rnd-key-live-time">随机码有效时间：</label>
                <input id="rnd-key-live-time" type="number" style="width: 100px" placeholder="单位（秒）" value="{$data[0]["rnd_key_live_time"]}"/>（秒）<br/>
                <label for="rows-per-page">每页显示数据行数：</label>
                <input id="rows-per-page" type="number" style="width: 100px" placeholder="单位（行）" value="{$data[0]["rows_per_page"]}"/>（行）<br/>
                <label for="valid-interval">票券多次验证间隔：</label>
                <input id="valid-interval" type="number" style="width: 100px" placeholder="单位（秒）" value="{$data[0]["valid_interval"]}"/>（秒）<br/>
                <label for="need-out-valid">月票不刷出口无法使用：</label>
                <select id="need-out-valid" style="width: 100px"> 
                    $need_out
                </select><br/>        
                <label style="vertical-align: 35px">当前Logo：</label>
                <label>$pic_html</label>
                <label style="vertical-align: 35px">替换Logo：</label>
                <label for="upfile">
                    <div class="upload-div upload"></div>
                    $pic_html
                </label>
                <input type="file" id="upfile" class="hidden" onchange="checkFile(this)"/><br/>
                <button type="button" onclick="postWebSet('$set_id');" class="btn btn-default" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        }
        return $html;
    }

    static function updateWebSet($data){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";
        $sql = <<<LL
  update sys_web_set set title_suffix = '$title_suffix',
  footer = '$footer',session_live_time = '$session_live_time',
  order_live_time = '$order_live_time',rnd_key_live_time = '$rnd_key_live_time',
  rows_per_page = '$rows_per_page',valid_interval = '$valid_interval',
  need_out_valid = '$need_out_valid',update_time = now()
LL;
        Mysql::query($sql);
        return "设置修改成功";
    }

    static function delWebSet($set_id){
        if (empty($set_id))
            return "设置不存在";
        $sql = "delete from sys_web_set where id = '$set_id'";
        Mysql::query($sql);
        return "设置删除成功";
    }


}