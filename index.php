<?php
/**
 * Created by 张世彪.
 * Date: 2016/8/3 19:34
 */
require './wechat.inc.php';
$wechat = new Wechat();
if ($_GET['echostr']) {
    $wechat->valid();
} else {
    $wechat->responseMsg();
}
