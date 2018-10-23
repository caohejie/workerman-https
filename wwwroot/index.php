<?php


if (strpos($_SERVER['REQUEST_URI'], '.well-known') === false) {

} else {
    if (file_exists("/ss/duanxian/https/wwwroot/miyao" . $_SERVER['REQUEST_URI'])) {

        return file_get_contents("/ss/duanxian/https/wwwroot/miyao" . $_SERVER['REQUEST_URI']);

    } else {
        echo "校验失败";
        return;
    }

}

if (!isset($_GET['url'])) {
    echo "地址错误 url地址不需要包含http:// 例: aaa.baidu.com 目前不支持泛解析和多域名";
    return 'false';
} else {

    $res = file_get_contents("http://" . $_GET['url'] . "/check.php");

    if ($res != "ok") {

        echo "请把域名指向45.78.78.49";
        return;

    }

}

$url = $_GET['url'];

if (file_exists('/ss/duanxian/https/wwwroot/miyao/' . $url . '.key')) {
    //如果文件存在 验证是否到期

    exec("openssl x509 -in /ss/duanxian/https/wwwroot/miyao/" . $url . ".key -noout -dates", $out);

    $arr = explode("=", $out[1]);

    $aftime = date("Y-m-d H:i:s", strtotime($arr[1]));

    if (strtotime($arr[1]) < time()) {
        //需要更新
    } else {
        //不需要更新

        if (file_exists("./zip/" . $url . ".zip")) {


        } else {
            $zip = new ZipArchive();
            $zip->open("./zip/" . $url . ".zip", ZipArchive::CREATE);   //打开压缩包
            $zip->addFile("/ss/duanxian/https/wwwroot/miyao/" . $url . ".key", basename("/ss/duanxian/https/wwwroot/miyao/" . $url . ".key"));   //向压缩包中添加文件
            $zip->addFile("/ss/duanxian/https/wwwroot/miyao/domain.key", basename("/ss/duanxian/https/wwwroot/miyao/domain.key"));   //向压缩包中添加文件

            $zip->close();  //关闭压缩包
        }


        $file_name = "./zip/" . $url . ".zip";

        $downname = $url . ".zip";

        $fp = fopen($file_name, "r");
        $file_size = filesize($file_name);//获取文件的字节
        //下载文件需要用到的头
        Workerman\Protocols\Http::header("Content-type: application/octet-stream");
        Workerman\Protocols\Http::header("Accept-Ranges: bytes");
        Workerman\Protocols\Http::header("Accept-Length:" . $file_size);
        Workerman\Protocols\Http::header("Content-Disposition: attachment; filename=$downname");
        $buffer = 1024; //设置一次读取的字节数，每读取一次，就输出数据（即返回给浏览器）
        $file_count = 0; //读取的总字节数
        //向浏览器返回数据 如果下载完成就停止输出，如果未下载完成就一直在输出。根据文件的字节大小判断是否下载完成
        while (!feof($fp) && $file_count < $file_size) {
            $file_con = fread($fp, $buffer);
            $file_count += $buffer;
            echo $file_con;
        }
        fclose($fp);
        //下载完成后删除压缩包，临时文件夹
        if ($file_count >= $file_size) {
            unlink($file_name);
        }

    }

} else {

    $str = 'openssl req -new -sha256 -key ./miyao/domain.key -out ./miyao/' . $url . '.csr -subj "/CN=' . $url . '"';

    exec($str, $out);

    $str = 'php qianming.php -a /ss/duanxian/https/wwwroot/miyao/account.key -r /ss/duanxian/https/wwwroot/miyao/' . $url . '.csr -d ' . $url . ' -o /ss/duanxian/https/wwwroot/miyao/' . $url . '.key -c /ss/duanxian/https/wwwroot/.well-known/acme-challenge/';

    exec($str, $out);

    if (!file_exists("/ss/duanxian/https/wwwroot/miyao/" . $url . ".key")) {

        echo "生成失败";

    } else {

        $zip = new ZipArchive();
        $zip->open("./zip/" . $url . ".zip", ZipArchive::CREATE);   //打开压缩包
        $zip->addFile("/ss/duanxian/https/wwwroot/miyao/" . $url . ".key", basename("/ss/duanxian/https/wwwroot/miyao/" . $url . ".key"));   //向压缩包中添加文件
        $zip->addFile("/ss/duanxian/https/wwwroot/miyao/domain.key", basename("/ss/duanxian/https/wwwroot/miyao/domain.key"));   //向压缩包中添加文件

        $zip->close();  //关闭压缩包


        $file_name = "./zip/" . $url . ".zip";

        $downname = $url . ".zip";

        $fp = fopen($file_name, "r");
        $file_size = filesize($file_name);//获取文件的字节
        //下载文件需要用到的头
        Workerman\Protocols\Http::header("Content-type: application/octet-stream");
        Workerman\Protocols\Http::header("Accept-Ranges: bytes");
        Workerman\Protocols\Http::header("Accept-Length:" . $file_size);
        Workerman\Protocols\Http::header("Content-Disposition: attachment; filename=$downname");
        $buffer = 1024; //设置一次读取的字节数，每读取一次，就输出数据（即返回给浏览器）
        $file_count = 0; //读取的总字节数
        //向浏览器返回数据 如果下载完成就停止输出，如果未下载完成就一直在输出。根据文件的字节大小判断是否下载完成
        while (!feof($fp) && $file_count < $file_size) {
            $file_con = fread($fp, $buffer);
            $file_count += $buffer;
            echo $file_con;
        }
        fclose($fp);
        //下载完成后删除压缩包，临时文件夹
        if ($file_count >= $file_size) {
            unlink($file_name);
        }

        echo "生成成功";
    }


}




