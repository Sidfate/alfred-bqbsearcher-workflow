<?php
require_once('workflows.php');
$w = new Workflows();

define('IMAGE_REMOTE_URL', 'https://v2fy.com/asset/0i/ChineseBQB/');
define('IMAGE_LOCAL_URL', 'tmp/');

if (filemtime("data.json") <= (time() - 86400 * 3)) {
    $dataUrl = 'https://raw.githubusercontent.com/zhaoolee/ChineseBQB/master/chinesebqb_github.json';
    $bqbData = $w->request($dataUrl);
    $bqbJson = json_decode($bqbData, true);
    if (isset($bqbJson['data'])) {
        file_put_contents("data.json", $bqbData);
    }
}

// 将表情数据设置成 Alfred 可读的数据结构
function setResult($bqbData) {
    if(!is_dir(IMAGE_LOCAL_URL)) {
        mkdir(IMAGE_LOCAL_URL);
    }
    global $w;
    foreach ($bqbData as $key => $value) {
        $name = $value['name'];
        $url = $value['url'];
        $icon = IMAGE_LOCAL_URL.$name;
        $w->download(IMAGE_REMOTE_URL.$value['category'].'/'.$name, $icon);
        $subTitle = "Copy ".$name." to clipboard";
        $w->result($name, $icon, $name, $subTitle, $icon);
    }
}

// 通过搜索词过滤出结果
function filter($var) {
    global $query;
    $url = strtolower($var['url']);
    $name = strtolower($var['name']);
    return strpos($url, $query) !== false || strpos($name, $query) !== false;
}
$bqbData = json_decode(file_get_contents('data.json'), true)['data'];
$data = [];

if (strlen($query) != 0) {
    $data = array_filter($bqbData, "filter");
}

// 得到的搜索，设置到缓冲区
setResult($data);

// 将结果输出成 Alfred 可读的 xml 
echo $w->toxml();
