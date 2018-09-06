<?php
include_once "../../../base.php";
require_once "Tickets.php";
require_once "Scenic.php";
require_once "Business.php";
require_once "Sale.php";
require_once "Device.php";
require_once "File.php";
require_once "WebSet.php";
require_once "Actions.php";
require_once "Template.php";
require_once "System.php";

$data = empty($_POST)?"":$_POST;
$ajax = new Ajax($data);

class Ajax
{
    public $data,
        $oid,$tid,$pid,$sale_id,$xid,
        $order_state,
        $action_key;

    function __construct($data){
        $this->data = $data;
        foreach ($data as $key=>$value){
            $this->$key = $value;
        }
        if(!empty($data["data"])){
            foreach ($data["data"] as $key=>$value){
                $this->$key = $value;
            }
        }
        echo $this->switchAct();
    }

    function switchAct(){
        switch ($this->action_key){
            case "login":
                $result = User::ajaxLogin($this->data);
                break;
            //获取frame
            case "getFrame":
                switch ($this->data["frame_name"]){
                    case "AddTicket":
                        $result = Tickets::getAddTicket();
                        break;
                    case "AddTicType":
                        $result = Tickets::getAddTicType();
                        break;
                    case "AddScenic":
                        $result = Scenic::getAddScenic();
                        break;
                    case "AddBusiness":
                        $result = Business::getAddBusiness();
                        break;
                    case "AddSale":
                        $result = Sale::getAddSale();
                        break;
                    case "AddDevice":
                        $result = Device::getAddDevice();
                        break;
                    case "AddWebSet":
                        $result = WebSet::getAddWebSet();
                        break;

                    case "EditTicket":
                        $result = Tickets::getEditTicket($this->xid);
                        break;
                    case "EditTicType":
                        $result = Tickets::getEditTicType($this->xid);
                        break;
                    case "EditScenic":
                        $result = Scenic::getEditScenic($this->xid);
                        break;
                    case "EditBusiness":
                        $result = Business::getEditBusiness($this->xid);
                        break;
                    case "EditSale":
                        $result = Sale::getEditSale($this->xid);
                        break;
                    case "EditDevice":
                        $result = Device::getEditDevice($this->xid);
                        break;
                    case "EditWebSet":
                        $result = WebSet::getEditWebSet($this->xid);
                        break;
                    default:
                        $result = "暂无模板";
                        break;
                }
                break;
            case "delInfo":
                switch ($this->data["info"]){
                    case "Ticket":
                        $result = Tickets::delTicket($this->xid);
                        break;
                    case "Scenic":
                        $result = Scenic::delScenic($this->xid);
                        break;
                    case "Business":
                        $result = Business::delBusiness($this->xid);
                        break;
                    case "Sale":
                        $result = Sale::delSale($this->xid);
                        break;
                    case "Device":
                        $result = Device::delDevice($this->xid);
                        break;
                    case "WebSet":
                        $result = WebSet::delWebSet($this->xid);
                        break;
                    case "AutoUpdate":
                        $result = Ticket::delAutoUpdate($this->xid);
                        break;
                    case "TicType":
                        $result = Ticket::delTicType($this->xid);
                        break;
                    default:
                        $result = "要删除的数据类型不存在";
                }
                break;

            //新增票券
            case "newTicketData":
                //var_dump($this->data);
                $result = Tickets::addTicket($this->data,$_FILES);
                break;
            //更新票券
            case "updateTicketData":
                $result = Tickets::updateTicket($this->data,$_FILES);
                break;

            //新增票券种类
            case "newTicTypeData":
                $result = Tickets::addTicType($this->data);
                break;
            //更新票券种类
            case "updateTicTypeData":
                $result = Tickets::updateTicType($this->data);
                break;

            //新增景点
            case "newScenicData":
                $result = Scenic::addScenic($this->data,$_FILES);
                break;
            //更新景点
            case "updateScenicData":
                $result = Scenic::updateScenic($this->data,$_FILES);
                break;

            //新增商家
            case "newBusinessData":
                $result = Business::addBusiness($this->data,$_FILES);
                break;
            //更新商家
            case "updateBusinessData":
                $result = Business::updateBusiness($this->data,$_FILES);
                break;

            //新增优惠
            case "newSaleData":
                $result = Sale::addSale($this->data);
                break;
            //更新优惠
            case "updateSaleData":
                $result = Sale::updateSale($this->data);
                break;

            //新增设备
            case "newDeviceData":
                $result = Device::addDevice($this->data);
                break;
            //更新设备
            case "updateDeviceData":
                $result = Device::updateDevice($this->data);
                break;

            //新增设备
            case "newWebSetData":
                $result = WebSet::addWebSet($this->data);
                break;
            //更新设备
            case "updateWebSetData":
                $result = WebSet::updateWebSet($this->data);
                break;

            //获取时间段内的子票券列表
            case "getChildTic":
                $date_arr = explode("-",$this->data["begin_end_time"]);
                if (!empty($date_arr[1])){
                    $begin_time = $date_arr[0];
                    $end_time = $date_arr[1];
                }
                $sql = "select tid,title,price from a_tickets where (begin_time>='$begin_time' and end_time<='$end_time') or tic_type = 2";
                $data = Mysql::query($sql,1);
                $result = "";
                foreach ($data as $row){
                    $result .= <<<LL
                    <option title="$row[price]" value="$row[tid]">$row[title]</option>
LL;
                }
                break;

            //测试
            case "test":
                foreach ($_FILES as $file){
                    move_uploaded_file($file["tmp_name"], "../../tickets/pictures/scenic/" . $file["name"]);
                }

                var_dump($this->data);
                var_dump($_FILES);
                $result = "成功";
                break;
            case "DelFile":
                $result = File::delFile($this->data['url']);
                break;
            case "RenameFile":
                $result = File::renameFile($this->data['url'],$this->data['old_name'],"index.jpg");
                break;


            default:
                $result = false;
                break;
        }
        return $result;

    }

}

