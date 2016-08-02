<?php
/**
 * Created by 张世彪.
 * Date: 2016/8/2 15:22
 */
require './wechat.inc.php';
$ticket = new Wechat();
$ticket->getTicket('666');