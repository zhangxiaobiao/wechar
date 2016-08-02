<?php
/**
 * Created by 张世彪.
 * Date: 2016/8/2 16:06
 */
require './wechat.inc.php';
$qrcode = new Wechat();
$qrcode->getQRCode();