<?php

class Device
{
    static function getAllDevice(){
        $sql = "select * from b_device order by update_time desc";
        $data = Mysql::query($sql,1);
        $html = Template::getTemp("all-device",$data);
        return $html;
    }

    //获取设备编辑页面
    static function getEditDevice($device_id)
    {
        $sql = "select * from b_device where device_id = '$device_id'";
        $data = Mysql::query($sql,1);
        $html = "没有获取到设备信息！";
        if (!empty($data)){
            $ticket_options = Actions::getOpts("ticket");
            $term = Actions::jsonToTerm($data[0]["device_tid_json"]);
            $tid_sql = "select tid,title from a_tickets where tid in $term";
            $tid_data = Mysql::query($tid_sql,1);
            $exit_select = empty($data[0]["device_is_entrance"])?"selected":"";
            $span_html = "";
            foreach ($tid_data as $row){
                $span_html.= <<<LL
                <span title="$row[tid]" class="$row[tid]" onclick='ask("解除此闸机对票券【$row[title]】的验证？","removeTid(\"$row[tid]\")")'>$row[title]</span>
LL;
            }
            $html = <<<LL
        <div class="form-area">
            <form id="add-device">
                <label for="device-name">设备名称：</label>
                <input id="device-name" type="text" style="width: 300px" placeholder="请填写设备名称" value="{$data[0]['device_name']}"/><br/>
                <label for="device-address">设备地址：</label>
                <input id="device-address" type="text" style="width: 400px" placeholder="请填写设备地址" value="{$data[0]['device_address']}"/><br/>
                <label for="is-entrance">设备位置：</label>
                <select id="is-entrance" style="color: #363636;width: 200px">
                    <option value="1">入口</option>
                    <option value="0" $exit_select>出口</option>
                </select><br/>
                <label for="device-tid">添加可验票券：</label>
                <select id="device-tid" style="color: #363636;width: 400px">
                    <option value="">暂不添加</option>
                    $ticket_options
                </select>
                <button type="button" class="btn btn-default" onclick="addDeviceTid();">添加</button><br/>
                <label for="device-tid-list">设备可验票券（点击可删除）：</label>
                <br/>
                <div id="device-tid-list" style="overflow: hidden">
                    $span_html
                </div><br/>
                <button type="button" onclick="postDevice('$device_id');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        }
        return $html;
    }

    //获取新增设备页面
    static function getAddDevice()
    {
        $ticket_options = Actions::getOpts("ticket");
        $html = <<<LL
        <div class="form-area">
            <form id="add-device">
                <label for="device-name">设备名称：</label>
                <input id="device-name" type="text" style="width: 200px" placeholder="请填写设备名称"/><br/>
                <label for="device-address">设备地址：</label>
                <input id="device-address" type="text" style="width: 400px" placeholder="请填写设备地址"/><br/>
                <label for="is-entrance">设备位置：</label>
                <select id="is-entrance" style="color: #363636;width: 200px">
                    <option value="1">入口</option>
                    <option value="0">出口</option>
                </select><br/>                
                <label for="device-tid">设备可识别的tid：</label>
                <select id="device-tid" style="color: #363636;width: 400px">
                    <option value="">暂不添加</option>
                    $ticket_options
                </select>
                <button type="button" class="btn btn-default" onclick="addDeviceTid();">添加</button><br/>
                <label for="device-tid-list">设备可验票券（点击可删除）：</label>
                <br/>
                <div id="device-tid-list" style="overflow: hidden">
                    
                </div><br/>
                <button type="button" onclick="postDevice('');" class="btn" style="color: #363636">保存</button>
            </form>
        </div>
LL;
        return $html;
    }

    static function addDevice($data){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";
        $new_device_id = Actions::makeID("device_id","D");
        $sql = "insert into b_device(device_id,device_name,device_address,device_is_entrance,device_tid_json,update_time) values('$new_device_id','$device_name','$device_address','$device_is_entrance','$device_tid_json',now())";
        Mysql::query($sql);
        return "设备添加成功";
    }

    static function updateDevice($data){
        foreach ($data as $key=>$value){
            $$key = $value;
        }
        //if (empty($title)||empty($price)||empty($stock)||empty($begin_time)||empty($end_time))
        //return "数控提交不完整";

        $sql = "update b_device set device_name = '$device_name',device_address = '$device_address',device_is_entrance = '$device_is_entrance',device_tid_json = '$device_tid_json',update_time = now() where device_id = '$device_id'";
        Mysql::query($sql);
        return "设备修改成功";
    }

    static function delDevice($device_id){
        if (empty($device_id))
            return "设备id不存在";
        $sql = "delete from b_device where device_id = '$device_id'";
        Mysql::query($sql);
        return "设备删除成功";
    }


}