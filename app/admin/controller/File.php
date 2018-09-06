<?php
class File
{
    static function makeDir($url){
        $dir = iconv("UTF-8","GBK", BASE_PATH.$url);
        if (!is_dir($dir)){
            mkdir($dir,0777,true);
            return 1;
        }else{
            return 0;
        }
    }
    static function uploadFile($url,$files){
        $dir = iconv("UTF-8","GBK", BASE_PATH.$url);
        foreach ($files as $file){
            $file_name = is_file($dir."/index.jpg")
                ?date("YmdHis").Actions::randKey(4).".jpg"
                :"index.jpg";
            move_uploaded_file($file["tmp_name"], $dir."/$file_name");
        }
    }

    static function renameFile($url,$old_name,$new_name){
        $dir = iconv("UTF-8","GBK", BASE_PATH.$url);

        if (is_file($dir."/".$new_name)){
            $rnd_name = date("YmdHis").Actions::randKey(4);
            rename($dir."/".$new_name,$dir."/".$rnd_name.".jpg");
        }
        rename($dir."/".$old_name,$dir."/".$new_name);
        return "文件重命名成功";
    }

    static function delDir($url){
        $dir = iconv("UTF-8","GBK", BASE_PATH.$url);
        if (is_dir($dir)){
            $files = scandir($dir);
            foreach ($files as $file){
                self::delFile($url."/".$file);
            }
            //删除当前文件夹：
            rmdir($dir);
            return "文件夹已删除！";
        }else{
            return "文件夹不存在！";
        }
    }

    static function delFile($url){
        $dir = iconv("UTF-8","GBK", BASE_PATH.$url);
        if (is_file($dir)){
            unlink($dir);
            return "文件已删除！";
        }else{
            return "文件不存在！";
        }
    }
}