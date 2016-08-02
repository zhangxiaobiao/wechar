<?php
/**
 * Created by 张世彪.
 * Date: 2016/8/2 11:29
 */
//加载类文件
require './wechat.inc.php';
//实例化对象，new
$wechat = new Wechat();
//调用方法
$wechat->getAccessToken();