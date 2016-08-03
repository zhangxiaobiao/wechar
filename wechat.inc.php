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
    //构造方法
    public function __construct()
    {
        $this->appid = APPID;
        $this->appsecret = APPSECRET;
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
                          "key":"V1001_TODAY_MUSIC"
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