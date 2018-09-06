<?php

class Sale
{
    static function getAllSale(){
        $sql = "select * from a_sale order by update_time desc";
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("all-sale",$data);
        return $html;
    }

    //获取优惠编辑页面
    static function getEditSale($sale_id)
    {
        $sql = "select * from a_sale where sale_id = '$sale_id'";
        $data = Mysql::query($sql,1);
        $html = "没有获取到折扣信息！";
        if (!empty($data)){
            $sale_sel = $discount_sel = "";
            if ($data[0]["sale_type"] == "满减"){
                $sale_sel = "selected";
            }elseif ($data[0]["sale_type"] == "折扣"){
                $discount_sel = "selected";
            }
            $html = <<<LL
        <div class="form-area">
            <form id="add-sale">
                <label for="sale-name">优惠名称：</label>
                <input id="sale-name" type="text" style="width: 200px" placeholder="请填写折扣名称" value="{$data[0]['sale_name']}"/><br/>
                <label for="sale-type">优惠类型：</label>
                <select id="sale-type" style="color: #363636;width: 120px">
                    <option $sale_sel>满减</option>
                    <option $discount_sel>折扣</option>
                </select><br/>
                <label for="term-price">优惠条件1（满足金额）：</label>
                <input id="term-price" type="number" style="width: 100px" placeholder="（元）" value="{$data[0]['term_price']}"/><br/>
                <label for="term-num">优惠条件2（满足张数）：</label>
                <input id="term-num" type="number" style="width: 100px" placeholder="（张）" value="{$data[0]['term_num']}"/><br/>
                <label for="save-percent">优惠内容1（总价*此数）：</label>
                <input id="save-percent" type="number" style="width: 100px" placeholder="默认为1" value="{$data[0]['save_percent']}"/><br/>
                <label for="save-money">优惠内容2（总价-此数）：</label>
                <input id="save-money" type="number" style="width: 100px" placeholder="（元）" value="{$data[0]['save_money']}"/><br/>
                <label for="begin-time">开始时间：</label>
                <input id="begin-time" type="date" style="width: 200px"/><br/>
                <label for="end-time">结束时间：</label>
                <input id="end-time" type="date" style="width: 200px"/><br/>
                <button type="button" onclick="post_sale('$sale_id');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        }

        return $html;
    }

    //获取新增票券页面
    static function getAddSale()
    {
        $html = <<<LL
        <div class="form-area">
            <form id="add-sale">
                <label for="sale-name">优惠名称：</label>
                <input id="sale-name" type="text" style="width: 200px" placeholder="请填写折扣名称"/><br/>
                <label for="sale-type">优惠类型：</label>
                <select id="sale-type" style="color: #363636;width: 120px">
                    <option>满减</option>
                    <option>折扣</option>
                </select><br/>
                <label for="term-price">优惠条件1（满足金额）：</label>
                <input id="term-price" type="number" style="width: 100px" placeholder="（元）" value="0"/><br/>
                <label for="term-num">优惠条件2（满足张数）：</label>
                <input id="term-num" type="number" style="width: 100px" placeholder="（张）" value="0"/><br/>
                <label for="save-percent">优惠内容1（总价*此数）：</label>
                <input id="save-percent" type="number" style="width: 100px" placeholder="默认为1" value="1"/><br/>
                <label for="save-money">优惠内容2（总价-此数）：</label>
                <input id="save-money" type="number" style="width: 100px" placeholder="（元）" value="0"/><br/>
                <label for="begin-time">开始时间：</label>
                <input id="begin-time" type="date" style="width: 200px"/><br/>
                <label for="end-time">结束时间：</label>
                <input id="end-time" type="date" style="width: 200px"/><br/>
                <button type="button" onclick="post_sale('');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        return $html;
    }

    static function addSale($data){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";
        $new_sale_id = Actions::makeID("sale_id","S");
        $sql = "insert into a_sale(sale_id,sale_name,sale_type,term_price,term_num,save_percent,save_money,begin_time,end_time,update_time) values('$new_sale_id','$sale_name','$sale_type','$term_price','$term_num','$save_percent','$save_money','$begin_time','$end_time',now())";
        Mysql::query($sql);
        return "优惠添加成功";
    }

    static function updateSale($data){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";

        $sql = "update a_sale set sale_name = '$sale_name',sale_type = '$sale_type',term_price = '$term_price',term_num = '$term_num',save_percent = '$save_percent',save_money = '$save_money',begin_time = '$begin_time',end_time = '$end_time',update_time = now() where sale_id = '$sale_id'";
        Mysql::query($sql);
        return "优惠修改成功";
    }

    static function delSale($sale_id){
        if (empty($sale_id))
            return "不存在的sale_id";
        $sql = "delete from a_sale where sale_id = '$sale_id'";
        Mysql::query($sql);
        return "优惠删除成功";
    }

}