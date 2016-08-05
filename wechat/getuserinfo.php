<?php
/**
 * Created by 张世彪.
 * Date: 2016/8/3 10:24
 */
require './wechat.inc.php';
$wechat = new Wechat();
$openid = $_GET['openid'];
$wechat->getUserInfo($openid);