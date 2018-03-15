<?php 
class wxIndexClass{
	function reponseMsg(){
		//获取微信推送过来的信息(xml格式的信息)
		$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];	
		//$postArr = file_get_contents('php://input');
	
		
		//将$postArr的内容写入wxts.xml的文件中作为调试
			file_put_contents("postArrwxts.html",$postArr);
			
		
		//将传过来的xml转换成 SimpleXMLElement 对象。必须要用->来调用
		$postObj = simplexml_load_string($postArr);
		
		
		// 	$postObj['ToUserName']
		//	$postObj['FromUserName']
		//	$postObj['CreateTime']
		//	$postObj['MsgType']
		//	$postObj['Event']
		//	$postObj['EventKey']
		//	$postObj['Ticket']
		
		file_put_contents("postObjwxts.txt",$postObj);
		
		$postStrArray = array($postObj->ToUserName,$postObj->FromUserName,$postObj->CreateTime,$postObj->MsgType,$postObj->Event);
		
		file_put_contents("postStrArray.txt",$postStrArray);
		
		$postStrString = implode(" ",$postStrArray);
	
		file_put_contents("postStrString.txt",$postStrString);
		
		if(strtolower( $postObj->MsgType ) == 'event'){
			//关注事件
			if(strtolower( $postObj->Event ) == "subscribe"){
				//回复用户信息(text 方式)
				$toUser = $postObj->FromUserName;
				$fromUser = $postObj->ToUserName;
				$time = time();
				$msgType = 'text';
				$content = '欢迎关注我们的微信';
				//回复的模板
				$template = "<xml> <ToUserName>< ![CDATA[%s] ]></ToUserName> <FromUserName>< ![CDATA[%s] ]></FromUserName> <CreateTime>%s</CreateTime> <MsgType>< ![CDATA[%s] ]></MsgType> <Content>< ![CDATA[%s] ]></Content> </xml>";				
				$info = sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
				//echo $info;
				file_put_contents("info.txt",$info);			
				return $info;
			}			
		}
	}	
}