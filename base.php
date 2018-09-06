<?php
header("Content-type: text/html; charset=utf-8");
session_start();
date_default_timezone_set('Asia/Shanghai');
//配置mysql数据库
const MYSQL_HOST = "127.0.0.1";
const MYSQL_USER = "root";
const MYSQL_PWD = "root";
const MYSQL_DB = "tickets";
require_once "app/Mysql.php";

define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
define('APP_NAME',explode("/",$_SERVER['PHP_SELF'])[1]);
define('BASE_SERVER','http://'.$_SERVER['HTTP_HOST'].'/'.APP_NAME);



//过滤用户输入字符串
function safeStr($str,$mode = false){
    if ($mode){
        //特殊字符的过滤方法
        $str = str_replace('`', '', $str);
        $str = str_replace('·', '', $str);
        $str = str_replace('~', '', $str);
        $str = str_replace('!', '', $str);
        $str = str_replace('！', '', $str);
        $str = str_replace('@', '', $str);
        $str = str_replace('#', '', $str);
        $str = str_replace('$', '', $str);
        $str = str_replace('￥', '', $str);
        $str = str_replace('%', '', $str);
        $str = str_replace('^', '', $str);
        $str = str_replace('……', '', $str);
        $str = str_replace('&', '', $str);
        $str = str_replace('*', '', $str);
        $str = str_replace('(', '', $str);
        $str = str_replace(')', '', $str);
        $str = str_replace('（', '', $str);
        $str = str_replace('）', '', $str);
        $str = str_replace('-', '', $str);
        $str = str_replace('_', '', $str);
        $str = str_replace('——', '', $str);
        $str = str_replace('+', '', $str);
        $str = str_replace('=', '', $str);
        $str = str_replace('|', '', $str);
        $str = str_replace('\\', '', $str);
        $str = str_replace('[', '', $str);
        $str = str_replace(']', '', $str);
        $str = str_replace('【', '', $str);
        $str = str_replace('】', '', $str);
        $str = str_replace('{', '', $str);
        $str = str_replace('}', '', $str);
        $str = str_replace(';', '', $str);
        $str = str_replace('；', '', $str);
        $str = str_replace(':', '', $str);
        $str = str_replace('：', '', $str);
        $str = str_replace('\'', '', $str);
        $str = str_replace('"', '', $str);
        $str = str_replace('“', '', $str);
        $str = str_replace('”', '', $str);
        $str = str_replace(',', '', $str);
        $str = str_replace('，', '', $str);
        $str = str_replace('<', '', $str);
        $str = str_replace('>', '', $str);
        $str = str_replace('《', '', $str);
        $str = str_replace('》', '', $str);
        $str = str_replace('.', '', $str);
        $str = str_replace('。', '', $str);
        $str = str_replace('/', '', $str);
        $str = str_replace('、', '', $str);
        $str = str_replace('?', '', $str);
        $str = str_replace('？', '', $str);
    }
    //防sql防注入代码的过滤方法
    $str = str_replace('and','',$str);
    $str = str_replace('execute','',$str);
    $str = str_replace('update','',$str);
    $str = str_replace('count','',$str);
    $str = str_replace('chr','',$str);
    $str = str_replace('mid','',$str);
    $str = str_replace('master','',$str);
    $str = str_replace('truncate','',$str);
    $str = str_replace('char','',$str);
    $str = str_replace('declare','',$str);
    $str = str_replace('select','',$str);
    $str = str_replace('create','',$str);
    $str = str_replace('delete','',$str);
    $str = str_replace('insert','',$str);
    $str = str_replace('or','',$str);
    return trim($str);
}