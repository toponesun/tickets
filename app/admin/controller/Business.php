<?php

class Business
{
    static function getAllBusiness(){
        $sql = "select * from a_business order by update_time DESC";
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("all-business",$data);
        return $html;
    }

    //获取商家编辑页面
    static function getEditBusiness($bid)
    {
        $sql = "select * from a_business where bid = '$bid'";
        $data = Mysql::query($sql,1);
        $html = "没有获取到商家信息！";
        $city_options = Actions::getOpts("city",$data[0]["city"]);
        $pic_html = Template::getPicTemp("bid",$bid);

        if (!empty($data)){
            $html = <<<LL
            
        <div class="form-area">
            <form id="add-business">
                <label for="name">商家名称：</label>
                <input id="name" type="text" style="width: 200px" placeholder="请填写商家名称" value="{$data[0]["name"]}"/>
                <label for="city">所属城市：</label>
                <select id="city" style="color: #363636;width: 120px">
                    <option>无</option>
                    $city_options
                </select><br/>
                <label for="phone">联系方式：</label>
                <input id="phone" type="text" style="width: 300px" placeholder="请填写联系方式" value="{$data[0]["phone"]}"/><br/>
                <label for="address">详细地址：</label>
                <input id="address" type="text" style="width: 600px" placeholder="请填写详细地址" value="{$data[0]["address"]}"/><br/>
                <label style="vertical-align: 35px">有效图片：</label>
                <label>$pic_html</label><br/>
                <label style="vertical-align: 35px">上传图片：</label>
                <label for="upfile">
                    <div class="upload-div upload"></div>
                </label>
                <input type="file" id="upfile" class="hidden" multiple onchange="checkFile(this)"/><br/>

                <label for="info">票券介绍：</label><br/>
                <textarea id="info" placeholder="请编辑票券介绍" rows="5" cols="80" style="color: black">{$data[0]["info"]}</textarea><br/>
                <button type="button" onclick="postBusiness('$bid');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        }

        return $html;
    }

    //获取新增票券页面
    static function getAddBusiness()
    {
        $city_options = Actions::getOpts("city");
        $html = <<<LL

        <div class="form-area">
            <form id="add-business">
                <label for="name">商家名称：</label>
                <input id="name" type="text" style="width: 200px" placeholder="请填写商家名称"/>
                <label for="city">所属城市：</label>
                <select id="city" style="color: #363636;width: 120px">
                    <option>无</option>
                    $city_options
                </select><br/>
                <label for="phone">联系方式：</label>
                <input id="phone" type="text" style="width: 300px" placeholder="请填写联系方式"/><br/>
                <label for="address">详细地址：</label>
                <input id="address" type="text" style="width: 600px" placeholder="请填写详细地址"/><br/>
                <label style="vertical-align: 35px">上传图片：</label>
                <label for="upfile">
                    <div class="upload-div upload"></div>
                </label>
                <input type="file" id="upfile" multiple class="hidden" onchange="checkFile(this)"/><br/>
                <label for="info">商家介绍：</label><br/>
                <textarea id="info" placeholder="请编辑商家介绍" rows="5" cols="80" style="color: black"></textarea><br/>
                <button type="button" onclick="postBusiness('');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        return $html;
    }

    static function addBusiness($data,$files){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";
        $new_bid = Actions::makeID("bid","B");
        $sql = "insert into a_business(bid,name,city,phone,address,info,update_time) values('$new_bid','$name','$city','$phone','$address','$info',now())";
        Mysql::query($sql);

        File::makeDir("pictures/business/$new_bid");
        File::uploadFile("pictures/business/$new_bid",$files);
        return "商家添加成功";
    }

    static function updateBusiness($data,$files){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";

        $sql = "update a_business set name = '$name',city = '$city',phone = '$phone',address = '$address',info = '$info',update_time = now() where bid = '$bid'";
        Mysql::query($sql);
        File::makeDir("pictures/business/$bid");
        File::uploadFile("pictures/business/$bid",$files);

        return "商家修改成功";
    }

    static function delBusiness($bid){
        if (empty($bid))
            return "不存在的bid";
        $sql = "delete from a_business where bid = '$bid'";
        Mysql::query($sql);
        File::delDir("pictures/business/$bid");
        return "商家删除成功";
    }


}