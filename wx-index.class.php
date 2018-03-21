<?php 
include("wx-database-conn.php");
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
		
		//file_put_contents("postObjwxts.txt",$postObj);
		
		$postStrArray = array($postObj->ToUserName,$postObj->FromUserName,$postObj->CreateTime,$postObj->MsgType,$postObj->Event);
		
		//file_put_contents("postStrArray.txt",$postStrArray);
		
		$postStrString = implode(" ",$postStrArray);

		//file_put_contents("postStrString.txt",$postStrString);
	
	
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
				//file_put_contents("info.txt",$info);			
				return $info;
			}
		}
		
		if(strtolower( $postObj->MsgType) == 'text'){
			//查看传入的词是否符合资讯查询词，如果在在服务器或者设置中,优先进入
			$getKeyWord = trim($postObj->Content);
			$isFindSql = "select * from wp_terms where name = '$getKeyWord'";
			$isFindSql_db = mysql_query($isFindSql);
			$isFindSql_db_num = mysql_num_rows($isFindSql_db);
			
			if($isFindSql_db_num){
				$theInfo = $this->get_reponse_msg('news');
				return $theInfo;			
			}
						
			//if($postObj->Content == "服务器资讯"){
			//	$theInfo = $this->get_reponse_msg('news');
			//	return $theInfo;
			//}
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
		//file_put_contents("postgetArrwxts.html",$postArr);
			
		
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
					//$content = '没有相关的查询';
					//到数据库中查询
					$checkKey = trim($postObj->Content);
					$keyWordCheckSql = "select * from wp_wx_key_word where key_word = '$checkKey'";									
					$keyWordCheckSql_db = mysql_query($keyWordCheckSql);
					
					$keyWordCheckSql_db_num = mysql_num_rows($keyWordCheckSql_db);
					
					if($keyWordCheckSql_db_num){
						while($keyWordCheckSql_db_array = mysql_fetch_assoc($keyWordCheckSql_db)){
							$content = $keyWordCheckSql_db_array['word_ms'];
						}						
					}
					else{
						
						$textArraySql = "select * from wp_posts where post_title like '%$checkKey%' and post_status = 'publish' limit 0,5";						
						$textArraySql_db = mysql_query($textArraySql);
						
						$textArraySql_db_num = mysql_num_rows($textArraySql_db);
						
						if($textArraySql_db_num){
							//$theMsgType需要变成多图文
							$theMsgType = 'news';
							
							$getTextArray = array();
							
							$i = 0;
							while($textArraySql_db_array = mysql_fetch_assoc($textArraySql_db)){
								$getTextArray[$i]['title'] = $textArraySql_db_array['post_title'];
								$getTextArray[$i]['description'] = $textArraySql_db_array['post_content'];
								$getTextArray[$i]['picUrl'] = 'http://www.hostspaces.net/version/images/logo_a.gif';
								$getTextArray[$i]['url'] = $textArraySql_db_array['guid'];
								$i++;
							}
							$getInfo = $this->theNewsBox($getTextArray,$toUser,$fromUser,$time,$theMsgType);
							return $getInfo;
						
						}
						else{
							$content = '没有相关的查询';							
						}
								
					}
			}
			//file_put_contents('content.txt',$content);
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
			
			
			
			//根据输入的字段返回相关的网络信息
			$theNewsKeyWord = trim($postObj->Content);
			
			$getArrayNumSql = "select * from wp_terms where name = '$theNewsKeyWord'";
			
			$getArrayNumSql_db = mysql_query($getArrayNumSql);
			
			$getArrayNumSql_db_num = mysql_num_rows($getArrayNumSql_db);
			
			$getArrayNumSql_db_num_str = "获取到的数量为：".$getArrayNumSql_db_num;
			
			
			file_put_contents("theNum.txt",$getArrayNumSql_db_num_str);
			
			
			$xxArraySql = "select a.*, b.*, c.* from wp_term_relationships as a join wp_terms as b join wp_posts as c where a.term_taxonomy_id = b.term_id and c.id = a.object_id and b.name = '$theNewsKeyWord' limit 0,5";
			
			$xxArraySql_db = mysql_query($xxArraySql);			
			$getXxArray = array();
			
			$i = 0;
			
			while($xxArraySql_db_array = mysql_fetch_assoc($xxArraySql_db)){
				$getXxArray[$i]['title'] = $xxArraySql_db_array['post_title'];
				$getXxArray[$i]['description'] = $xxArraySql_db_array['post_content'];
				$getXxArray[$i]['picUrl'] = 'http://www.hostspaces.net/version/images/logo_a.gif';
				$getXxArray[$i]['url'] = $xxArraySql_db_array['guid'];
				$i++;
			};
			
			$strXxArray =implode("",$xxArraySql_db_array); 
			file_put_contents("arraycontent.txt",$strXxArray);
			
		 	$getInfo = $this->theNewsBox($getXxArray,$toUser,$fromUser,$time,$theMsgType);
			return $getInfo;
			
		}				
	}
	
	//多图文内容封装,因为图文封装可能需要多次调用
	function theNewsBox($arr,$toUser,$fromUser,$time,$theMsgType){
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