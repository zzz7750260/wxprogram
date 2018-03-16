<?php 
class wxIndexClass{
		
	function reponseMsg(){		
		//全局变量
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
				$content = '欢迎关注我们'.$postObj->ToUserName.'的微信\n'.$postObj->FromUserName;
				//回复的模板
				$template = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";				
				$info = sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
				//echo $info;
				file_put_contents("info.txt",$info);			
				return $info;
			}
		}
		
		if(strtolower( $postObj->MsgType) == 'text'){
			if($postObj->Content == "服务器资讯"){
				$theInfo = $this->get_reponse_msg('news');
				return $theInfo;
			}
			else{				
				$theInfo = $this->get_reponse_msg('text');
				return $theInfo;				
				
			}
		}
		
	}	
	
	//封装信息回复类型,与回复内容
	function get_reponse_msg($theMsgType){
		//获取微信推送过来的信息(xml格式的信息)
		$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];	
		//$postArr = file_get_contents('php://input');

		
		//将$postArr的内容写入wxts.xml的文件中作为调试
		file_put_contents("postgetArrwxts.html",$postArr);
			
		
		//将传过来的xml转换成 SimpleXMLElement 对象。必须要用->来调用
		$postObj = simplexml_load_string($postArr);
		
		$toUser = $postObj->FromUserName;
		$fromUser = $postObj->ToUserName;
		$time = time();
		$msgType = $theMsgType;		

		//根据类型回复模板选择
		if($theMsgType == 'text'){
			//文本类型的模板选择
			$template = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
			
			//一般通过trim将输入内容的左右空格去掉
			switch( trim($postObj->Content) ){
				case '服务器':
					$content = '我们拥有美国服务器，香港服务器等优质的海外资源';
					break;
				case '美国服务器':
					$content = '我们美国服务器有配置一，配置二等多种配置,你可以点击<a href="http://www.hostspaces.net/usa/">更多</a>了解我们服务器的相关资源';
					break;				
				default:
					$content = '没有相关的查询';
			}
			file_put_contents('content.txt',$content);
			$info = sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
				//echo $info;
			file_put_contents("xxxinfo.txt",$info);			
			return $info;		
		}
		
		else if($theMsgType == 'news'){
			$arr = array(
				array(
					'title' => '美国服务器配置一',
					'description' => '美国服务器配置一详情',
					'picUrl' => 'http://www.hostspaces.net/version/images/logo_a.gif',
					'url' => 'http://www.hostspaces.net/version/images/logo_a.gif',
				),
				array(
					'title' => '美国服务器配置二',
					'description' => '美国服务器配置二详情',
					'picUrl' => 'https://www.baidu.com/img/bd_logo1.png',
					'url' => 'http://www.hostspaces.net/version/images/logo_a.gif',
				),
				array(
					'title' => '美国服务器配置三',
					'description' => '美国服务器配置三详情',
					'picUrl' => 'http://www.hostspaces.net/version/images/logo_a.gif',
					'url' => 'http://www.hostspaces.net/version/images/logo_a.gif',
				),
				array(
					'title' => '美国服务器配置四',
					'description' => '美国服务器配置一详情',
					'picUrl' => 'https://www.baidu.com/img/bd_logo1.png',
					'url' => 'http://www.hostspaces.net/version/images/logo_a.gif',
				),
			);
			
			//获取图文的模板
			$newTemplate .= "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><ArticleCount>".count($arr)."</ArticleCount><Articles>";
						
			//item的循环输出		
			foreach($arr as $thekey => $value){
				$newTemplate .="<item><Title><![CDATA[".$value['title']."]]></Title><Description><![CDATA[".$value['description']."]]></Description><PicUrl><![CDATA[".$value['picUrl']."]]></PicUrl><Url><![CDATA[".$value['url']."]]></Url></item>";
			}
			
			$newTemplate .= "</Articles></xml>";
			
			$info = sprintf($newTemplate,$toUser,$fromUser,$time,$theMsgType);
			file_put_contents('dtwinfo.txt',$info);
			
			return $info;			
		}				
	}		
}