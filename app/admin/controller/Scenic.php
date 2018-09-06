<?php

class Scenic
{
    static function getAllScenic(){
        $sql = "select * from a_scenic order by update_time DESC";
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("all-scenic",$data);
        return $html;
    }

    //获取景点编辑页面
    static function getEditScenic($pid)
    {
        $sql = "select * from a_scenic where pid = '$pid'";
        $data = Mysql::query($sql,1);
        $html = "没有获取到景点信息！";
        $city_options = Actions::getOpts("city",$data[0]["city"]);
        $pic_html = Template::getPicTemp("pid",$pid);

        if (!empty($data)){
            $html = <<<LL
            
        <div class="form-area">
            <form id="add-scenic">
                <label for="name">景点名称：</label>
                <input id="name" type="text" style="width: 200px" placeholder="请填写景点名称" value="{$data[0]["name"]}"/>
                <label for="city">所属城市：</label>
                <select id="city" style="color: #363636;width: 120px">
                    <option>无</option>
                    $city_options
                </select><br/>
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
                <textarea id="info" placeholder="请编辑景点介绍" rows="5" cols="80" style="color: black">{$data[0]["info"]}</textarea><br/>
                <button type="button" onclick="postScenic('$pid');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        }

        return $html;
    }

    //获取新增票券页面
    static function getAddScenic()
    {
        $city_options = Actions::getOpts("city");
        $html = <<<LL

        <div class="form-area">
            <form id="add-scenic">
                <label for="name">景点名称：</label>
                <input id="name" type="text" style="width: 200px" placeholder="请填写景点名称"/>
                <label for="city">所属城市：</label>
                <select id="city" style="color: #363636;width: 120px">
                    <option>无</option>
                    $city_options
                </select><br/>
                <label for="address">详细地址：</label>
                <input id="address" type="text" style="width: 600px" placeholder="请填写详细地址"/><br/>
                <label style="vertical-align: 35px">上传图片：</label>
                <label for="upfile">
                    <div class="upload-div upload"></div>
                </label>
                <input type="file" id="upfile" multiple class="hidden" onchange="checkFile(this)"/><br/>
                <label for="info">票券介绍：</label><br/>
                <textarea id="info" placeholder="请编辑景点介绍" rows="5" cols="80" style="color: black"></textarea><br/>
                <button type="button" onclick="postScenic('');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        return $html;
    }

    static function addScenic($data,$files){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";
        $new_pid = Actions::makeID("pid","P");
        $sql = "insert into a_scenic(pid,name,city,address,info,update_time) values('$new_pid','$name','$city','$address','$info',now())";
        Mysql::query($sql);

        File::makeDir("pictures/scenic/$new_pid");
        File::uploadFile("pictures/scenic/$new_pid",$files);
        return "景点添加成功";
    }

    static function updateScenic($data,$files){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";

        $sql = "update a_scenic set name = '$name',city = '$city',address = '$address',info = '$info',update_time = now() where pid = '$pid'";
        Mysql::query($sql);
        File::makeDir("pictures/scenic/$pid");
        File::uploadFile("pictures/scenic/$pid",$files);

        return "景点修改成功";
    }

    static function delScenic($pid){
        if (empty($pid))
            return "不存在的pid";
        $sql = "delete from a_scenic where pid = '$pid'";
        Mysql::query($sql);
        File::delDir("pictures/scenic/$pid");
        return "景点删除成功";
    }


}