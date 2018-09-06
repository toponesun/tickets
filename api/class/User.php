<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/20 0020
 * Time: 14:03
 */

class User
{
    //用户登录
    static function login(&$result,$POST){
        //判断端口是否为空
        if (empty($POST["client"])){
            $result["msg"] = "请求登录的端口为空！";
            return;
        }
        //判断数据是否为空
        if (empty($POST["username"])||empty($POST["password"])||empty($POST["captcha"])){
            $result["msg"] = "用户名密码和验证码不允许为空！";
            return;
        }
        //复制需要的参数
        $username = $POST["username"];
        $password = $POST["password"];
        $captcha = $POST["captcha"];
        $client = $POST["client"];
        //判断验证码是否正确
        if ($_SESSION["captcha"] != $captcha){
            $result["msg"] = "验证码输入错误！";
            return;
        }
        //分离客户端
        switch ($client){
            case "admin":
                $tb_name = "user_admin";
                $index = "admin.php";
                break;
            case "customer":
                $tb_name = "user_customer";
                $index = "index.html";
                break;
            case "conductor":
                $tb_name = "user_conductor";
                $index = "conductor.html";
                break;
            case "business":
                $tb_name = "user_business";
                $index = "business.html";
                break;
            default:
                $result["msg"] = "端口名称不合法！";
                return;
        }
        //查找用户名密码信息
        $sql = "select password from $tb_name where username = '$username'";
        $data = Mysql::query($sql,1);
        //未找到用户信息返回提示
        if (empty($data)){
            $result["msg"] = "用户名不存在，请确认后重试！";
            return;
        }
        //计算MD5加密后的密码
        $md5_pass = md5($data[0]["password"].$_SESSION["captcha"]);
        //判断密码验证是否通过
        if ($md5_pass != $password){
            $result["msg"] = "密码输入错误，请确认后重试！";
            return;
        }
        //成功登陆！
        $result["code"] = "1";
        $result["msg"] = "登录成功！";
        $result["data"]["url"] = $index;
        //设置用户session
        self::setUserInfo($username,$client,1);
        return;
    }


    //设置用户session信息，根据new判断登录还是刷新
    static function setUserInfo($username,$client,$new = false){
        switch ($client){
            case "admin":
                $tb_name = "user_admin";
                $sql = "select * from $tb_name where username = '$username'";
                $data = Mysql::query($sql,1);
                if(!empty($data)){
                    $_SESSION['admin'] = [];
                    $_SESSION['admin']['username'] = $username;
                    $_SESSION['admin']['nickname'] = $data[0]["nickname"];
                    $_SESSION['admin']['uid'] = $data[0]['uid'];
                    $_SESSION['admin']['last_login'] = empty($data[0]["last_login"])?"":$data[0]["last_login"];
                    $_SESSION['admin']['active_time'] = time();
                }
                break;
            case "customer":
                $tb_name = "user_customer";
                $sql = "select * from $tb_name where username = '$username'";
                $data = Mysql::query($sql,1);
                if(!empty($data)){
                    $_SESSION['customer'] = [];
                    $_SESSION['customer']['username'] = $username;
                    $_SESSION['customer']['nickname'] = $data[0]["nickname"];
                    $_SESSION['customer']['uid'] = $data[0]['uid'];
                    $_SESSION['customer']['money'] = $data[0]["money"];
                    $_SESSION['customer']['last_login'] = empty($data[0]["last_login"])?"":$data[0]["last_login"];
                    $_SESSION['customer']['active_time'] = time();
                }
                break;
            case "conductor":
                $tb_name = "user_conductor";
                $sql = "select * from $tb_name where username = '$username'";
                $data = Mysql::query($sql,1);
                if(!empty($data)){
                    $_SESSION['conductor'] = [];
                    $_SESSION['conductor']['username'] = $username;
                    $_SESSION['conductor']['nickname'] = $data[0]["nickname"];
                    $_SESSION['conductor']['uid'] = $data[0]['uid'];
                    $_SESSION['conductor']['last_login'] = empty($data[0]["last_login"])?"":$data[0]["last_login"];
                    $_SESSION['conductor']['active_time'] = time();
                }
                break;
            case "business":
                $tb_name = "user_business";
                $sql = "select * from $tb_name where username = '$username'";
                $data = Mysql::query($sql,1);
                if(!empty($data)){
                    $_SESSION['business'] = [];
                    $_SESSION['business']['username'] = $username;
                    $_SESSION['business']['nickname'] = $data[0]["nickname"];
                    $_SESSION['business']['uid'] = $data[0]['uid'];
                    $_SESSION['business']['last_login'] = empty($data[0]["last_login"])?"":$data[0]["last_login"];
                    $_SESSION['business']['active_time'] = time();
                }
                break;
            default:
                return false;
                break;
        }
        if ($new){
            @Mysql::query("update $tb_name set last_login = now() where username = '$username'");
        }
        return true;
    }


    static function getUserInfo(&$result,$GET){
        $client = empty($GET["client"])?"":$GET["client"];
        //未传入端口名称
        if (empty($client)){
            $result["msg"] = "获取用户信息失败，需要传入端口名称！eg.customer,admin...";
            return;
        }
        //端口session不存在
        if (empty($_SESSION[$client]["uid"])){
            $result["msg"] = "尚未登录，获取用户信息失败！";
            return;
        }
        //端口session已过期的处理
        if (time() - $_SESSION[$client]["active_time"] > SESSION_LIVE_TIME){
            $result["msg"] = "您长时间未操作，系统已自动退出！";
            self::logout($result,$GET);
        }else{
            self::setUserInfo($_SESSION[$client]["username"],$client);
            $result["code"] = 1;
            $result["msg"] = "成功";
            $result["data"] = $_SESSION[$client];
        }
    }

    static function logout(&$result,$GET){
        $client = empty($GET["client"])?"":$GET["client"];
        //未传入端口名称
        if (empty($client)){
            $result["code"] = 0;
            $result["msg"] = "请求登出需要传入端口名称！eg.customer,admin...";
            return;
        }
        //端口session不存在
        if (empty($_SESSION[$client]["uid"])){
            $result["code"] = 0;
            $result["msg"] = "尚未登录，登出操作失败！";
            return;
        }
        //清空端口session，返回成功
        $_SESSION[$client] = [];
        $result["code"] = 1;
        $result["msg"] = "登出成功！";
    }

}