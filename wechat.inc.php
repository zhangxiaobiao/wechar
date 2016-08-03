<?php
/**
 * Created by 张世彪.
 * Date: 2016/8/2 10:54
 */
//加载配置文件
require './wechat.cfg.php';
//定义一个wechat类，用来存放微信开发里的接口请求方法
class Wechat
{
    //封装，多态，继承
    //私有化属性  公共，私有，被保护
    private $appid;
    private $appsecret;
    private $token;

    //构造方法
    public function __construct()
    {
        $this->appid = APPID;
        $this->appsecret = APPSECRET;
        $this->token = TOKEN;
        $this->textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
        $this->imgText = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[news]]></MsgType>
                        <ArticleCount>%s</ArticleCount>
                        <Articles>%s</Articles>
                        </xml>";
        $this->item = "<item>
                        <Title><![CDATA[%s]]></Title> 
                        <Description><![CDATA[%s]]></Description>
                        <PicUrl><![CDATA[%s]]></PicUrl>
                        <Url><![CDATA[%s]]></Url>
                        </item>";
        $this->music = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[music]]></MsgType>
                        <Music>
                        <Title><![CDATA[%s]]></Title>
                        <Description><![CDATA[%s]]></Description>
                        <MusicUrl><![CDATA[%s]]></MusicUrl>
                        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                        </Music>
                        </xml>";
    }
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            switch ($postObj->MsgType) {
                case 'text':
                    $this->_doText($postObj);
                    break;
                case 'image':
                    $this->_doImage($postObj);
                    break;
                case 'location':
                    $this->_doLocation($postObj);
                    break;
                case 'event':
                    $this->_doEvent($postObj);
                    break;
                default:
                    break;
            }
        }else {
                echo "";
                exit;
                }
    }
    //文本消息回复
    public function _doText($postObj)
    {
        $keyword = trim($postObj->Content);
        if(!empty( $keyword ))
        {
            $msgType = "text";
            $contentStr = "Welcome to wechat world!";
            if ($keyword == '音乐') {
                //file_put_contents("ceshi.txt", "222");
                $this->_sendMusic($postObj);
                exit();
            } elseif ($keyword == '靠') {
                $contentStr = "说话文明!";
            }
//            $url = 'http://api.qingyunke.com/api.php?key=free&appid=0&msg=' . $keyword;
//            $content = $this->request($url, false);
//            $content = json_decode($content);
//            $contentStr = $content->content;
            $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $msgType, $contentStr);
            echo $resultStr;
        }else{
            echo "Input something...";
        }
    }
    //图片消息处理
    public function _doImage($postObj)
    {
        $PicUrl = $postObj->PicUrl;
        $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $PicUrl);
        echo $resultStr;
    }
    //坐标信息处理
    public function _doLocation($postObj)
    {
        $location = "您的X坐标是:" . $postObj->Location_X . "Y坐标是：" . $postObj->Location_Y;
        $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $location);
        echo $resultStr;
    }
    //Event事件处理
    public function _doEvent($postObj)
    {
        $event = $postObj->Event;
        switch ($event) {
            case 'subscribe':
                $this->_doSubscribe($postObj);
                break;
            case 'unsubscribe':
                $this->_doUnsubcribe($postObj);
                break;
            case 'CLICK':
                $this->_click($postObj);
                break;
            default:
                break;
        }
    }
    //关注事件
    public function _doSubscribe($postObj)
    {
        $contentStr = "欢迎您关注我们";
        $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
        echo $resultStr;
    }
    //点击自定义菜单事件
    public function _click($postObj)
    {
        $eventKey = $postObj->EventKey;
        switch ($eventKey) {
            case 'news':
                $this->_sendTuwen($postObj);
                break;
            default:
                break;
        }
    }
    //发送图文消息事件
    public function _sendTuwen($postObj)
    {
        $array = array(
            array(
                'Title' => '林心如真的怀孕了！陪霍建华K歌，一阵风让孕肚现形',
                'Description' => '林心如霍建华婚讯最新消息：心如貌似是真的怀孕了！！！',
                'PicUrl' => 'http://p2.ifengimg.com/haina/2016_29/f45d2288cf36e36_w440_h440.jpg',
                'Url' => 'http://ent.ifeng.com/a/20160714/42651663_0.shtml',
            ),
            array(
                'Title' => '汪峰传闻惹章子怡发飙 盘点大牌女星产后复出：章子怡惊艳 寡姐性感',
                'Description' => '女明星产后复工不仅是她们人生的新起点,更是众人津津乐道的重头戏。前不久,韩国女神全智贤产后复出,时尚大片又仙又霸气。最近,章子怡又被证实洛杉矶低调复工,大合影上黑色小礼服的那一抹倩影,依然美如少女模样……',
                'PicUrl' => 'http://www.people.com.cn/mediafile/pic/20160803/61/2381498461783015885.jpg',
                'Url' => 'http://cq.people.com.cn/GB/365409/c28776823.html',
            ),
        );
        $item = '';
        foreach ($array as $key => $value) {
            $item .= sprintf($this->item, $value['Title'], $value['Description'], $value['PicUrl'], $value['Url']);
        }
        $resultStr = sprintf($this->imgText, $postObj->FromUserName, $postObj->ToUserName, time(), count($array), $item);
        echo $resultStr;
    }
    //发送音乐消息事件
    public function _sendMusic($postObj)
    {
        //file_put_contents('1111.txt', "999");
        $musicUrl = 'http://wx.jsy135.com/wechat48/huluwa.mp3';
        $content = sprintf($this->music, $postObj->FromUserName, $postObj->ToUserName, time(), '音乐', '好听的', $musicUrl, $musicUrl);
        echo $content;
    }
    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    public function request($url,$https=true,$method='get',$data=null){
        //1.初始化url
        $ch = curl_init($url);
        //2.设置相关的参数
        //字符串不直接输出,进行一个变量的存储
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //判断是否为https请求
        if($https === true){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //判断是否为post请求
        if($method == 'post'){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //3.发送请求
        $str = curl_exec($ch);
        //4.关闭连接
        curl_close($ch);
        //返回请求到的结果
        return $str;
    }
    public function getAccessToken()
    {
        //获取文件创建时间filemtime()
        //判断生成的access_token值是否过期
        $filename = './accesstoken';
        if (!file_exists($filename) || (file_exists($filename) && (time() - filemtime($filename)) > 7200)) {
            //1.url地址
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->appsecret;
            //2.判断是否为post请求
            //3.发送请求
            $content = $this->request($url);
            //4.处理返回值
            //返回数据格式为json,php不可以直接操作json格式
            $content = json_decode($content);
            $access_token = $content->access_token;
            //把access_token保存到文件
            file_put_contents($filename, $access_token);
        } else {//如果没有过期，那么就去读取缓存文件里的access_token
            $access_token = file_get_contents($filename);
        }
        //把access_token返回
        return $access_token;
    }
    public function getTicket($scend_id, $tmp = true, $expire_second = 604800)
    {
        //1.url地址
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->getAccessToken();
        //2.判断是否为post请求
        //判断是临时的还是永久的
        if ($tmp === true) {
            $data = '{"expire_seconds": '.$expire_second.', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scend_id.'}}}';
        } else {
            $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scend_id.'}}}';
        }
        //3.发送请求
        $content = $this->request($url, true, 'post', $data);
        //4.处理返回值
        //把json转化为对象，输出ticket
        $content = json_decode($content);
        return $content->ticket;
    }
    //通过ticket获取二维码
    public function getQRCode()
    {
        $ticket = $this->getTicket('666');
        //1.url地址
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
        //2.判断是否post
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        //返回值是一张jpg图片（文件）
        file_put_contents('./QRCode.jpg', $content);
    }
    //删除菜单操作
    public function delMenu()
    {
        //1.url地址
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$this->getAccessToken();
        //2.判断是否post请求
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回数据
        $content = json_decode($content);
        if ($content->errmsg == 'ok') {
            echo '删除菜单成功';
        } else {
            echo '删除菜单不成功' . '<br />';
            echo '错误代码为：' . $content->errcode;
        }
    }
    //创建菜单操作
    public function createMenu()
    {
        //1.url地址
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->getAccessToken();
        //2.判断是否是post
        $data = ' {
                     "button":[
                     {	
                          "type":"click",
                          "name":"今日歌曲",
                          "key":"news"
                      },
                      {
                           "name":"菜单",
                           "sub_button":[
                           {	
                               "type":"view",
                               "name":"搜索",
                               "url":"http://www.soso.com/"
                            },
                            {
                               "type":"view",
                               "name":"视频",
                               "url":"http://v.qq.com/"
                            },
                            {
                               "type":"click",
                               "name":"赞一下我们",
                               "key":"V1001_GOOD"
                            }]
                       }]
                 }';
        //3.发送请求
        $content = $this->request($url, true, 'post', $data);
        //4.处理返回数据
        $content = json_decode($content);
        if ($content->errmsg == 'ok') {
            echo '创建菜单成功';
        } else {
            echo '创建菜单不成功' . '<br />';
            echo '错误代码为：' . $content->errcode;
        }
    }
    //查询菜单操作
    public function showMenu()
    {
        //1.url地址
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=' . $this->getAccessToken();
        //2.判断是否post
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回数据
        //$content = json_decode($content);
        var_dump($content);
    }
    //获取用户openID
    public function getUserList()
    {
        //1.url地址
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $this->getAccessToken();
        //2.是否post
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        $content = json_decode($content);
        echo '现在的关注者数：'.$content->total.'<br />';
        //var_dump($content->data);
        foreach ($content->data->openid as $key => $value) {
            echo ($key+1) . '#' . '<a href="getuserinfo.php?openid='.$value.'">' . $value .'</a><br/>';
        }
    }
    //通过openID获取用户基本信息
    public function getUserInfo($openid)
    {
        //1.url地址
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$openid.'&lang=zh_CN';
        //2.是否post
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        $content = json_decode($content);
        echo '昵称为：' . $content->nickname . '<br />';
        echo '性别为：' . $content->sex . '<br />';
        echo '省份为：' . $content->province . '<br />';
        echo '<img src="'.$content->headimgurl.'"/>';
    }
}