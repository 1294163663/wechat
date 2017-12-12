<?php
/**
  * wechat php test
  */
//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}
class wechatCallbackapiTest
{
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
		$postStr = file_get_contents("php://input");
      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $event = $postObj->Event;
                $eventKey = $postObj->EventKey;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
                if ($event == 'subscribe')
                {
                    $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>1</ArticleCount>
                    <Articles>
                    <item>
                    <Title><![CDATA[欢迎来到梦想国度]]></Title> 
                    <Description><![CDATA[一个神奇的地方]]></Description>
                    <PicUrl><![CDATA[http://img15.3lian.com/2015/f2/132/d/23.jpg]]></PicUrl>
                    <Url><![CDATA[http://www.scut.edu.cn/oj/]]></Url>
                    </item>
                    </Articles>
                    </xml>";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time);
                    echo $resultStr;
                }
				if(!empty( $keyword ))
                {
              		$msgType = "text";
                    $robotUrl = 'http://api.qingyunke.com/api.php?key=free&appid=0&msg=' . urlencode($keyword);
                    $robotMsg = file_get_contents($robotUrl);
                    if ($robotMsg['result'] == 0) {
                        $contentStr = $robotMsg['content'];
                        $contentStr = str_replace('{br}', "\n", $contentStr);
                    } else {
                        $contentStr = "抱歉，暂无此功能";
                    }
                   
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                	echo $resultStr;
                }else{
                	echo "Input something...";
				}
				
                if($event=="CLICK" && $eventKey=="V1001_JOKE")
                {
                    $msgType = "text";
                    $url = "http://v.juhe.cn/joke/randJoke.php?type=&key=76849f11cb4d9aca65b72248b4b07b02";
                    $output = file_get_contents($url);
                    $arr = json_decode($output, true);
                    $contentStr = $arr['result'][0]['content'];
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
               
        }else {
        	echo "....";
        	exit;
        }
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
        		
		$token = TOKEN;
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
}