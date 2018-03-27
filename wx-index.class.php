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
	
	//获取access_token
	
	
	//设置curl_http的请求方法
	function http_curl($theUrl,$type='get',$res='json',$arr=''){
		//初始化curl
		$ch = curl_init();
		$url = $theUrl;
		
		//设置curl的参数
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		
		//当为post类型的时候
		if($type='post'){
			curl_setopt($ch, CURLOPT_POST, $url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);			
		}
		
		//采集
		$output = curl_exec($ch);

		//监测是否存在错别，必须放在curl_close之前监测
		if(curl_errno($ch)){
			var_dump(curl_errno($ch));		
		}		
	
		//关闭
		curl_close($ch);
		if($res =='json'){
			return json_decode($output,true);			
		}
		//var_dump($output);		
	}
	
	function getWxAccessToken(){

		//从数据库中取出token的过期时间
		$getTokenTimeSql = "select * from wp_users";
		
		$getTokenTimeSql_db = mysql_query($getTokenTimeSql);
		
		$getTokenTimeSqlArray = array();
		
		
		while($getTokenTimeSql_db_array = mysql_fetch_assoc($getTokenTimeSql_db)){
			$getTokenTimeSqlArray = $getTokenTimeSql_db_array;
		}
		$nowTime = time();
		$tokenEndTime = $getTokenTimeSqlArray['wx_token_timeEnd'];
		if($tokenEndTime>$nowTime){
			return $getTokenTimeSqlArray['wx_token'];
		}
		else{
			//重新获取token
			//获取微信的AppID
			//$appID = "wx14f88739efb836b1";

			//获取微信的AppSecret
			//$appSecret = "518471bf295994da56ca601817769af5";
			
			//测试号的appID与appsecret
			$appID = "wx5faec86adb79db26";
			$appSecret = "17913645124aec3e59aefa3f41ba5a88";
			
			$turl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appID."&secret=".$appSecret."";
			
			//初始化
			//$ch = curl_init();
			
			//设置参数
			//curl_setopt($ch, CURLOPT_URL, $turl);
			//curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查  
			//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在  
			//调用接口,得到返回值
			//$res = curl_exec($ch);	
			//关闭curl
			
			//if(curl_errno($ch)){
			//	var_dump(curl_errno($ch));		
			//}
			//curl_close($ch);
			//将返回的json转为数组
			//$arr = json_decode($res,true);
			
			//print_r($arr);	
			
			
			$theinfo = $this->http_curl($turl);
			var_dump($theinfo);
			
			$theToken = $theinfo['access_token'];
			$theTokenTime = time();//获取token的时间
			$theTokenTimeEnd = time()+7000;
				
			//将token存入数据库
			 
			
			$tokenSql = "update wp_users set wx_token = '$theToken', wx_token_time = '$theTokenTime', wx_token_timeEnd = '$theTokenTimeEnd' where user_login = 'admin' or user_login = 'chenxx'";
			
			$tokenSql_db = mysql_query($tokenSql);
			
			if($tokenSql_db){
				echo "================token插入成功====================";
				
			}
			
			return $theToken;
			
		}			
	}
	
	//创建自定义菜单
	function definedItem(){
		$getAccessToken = $this->getWxAccessToken();
		
		print_r($getAccessToken);
		
		//对自定义菜单进行数组化
		$menuArray = array(
			'button' => array(
				array(
					"type"=>"click",
					"name"=>urlencode('今日歌曲'),
					"key"=>"item1",
					
				),
				array(
					'name'=>urlencode('最新推荐'),
					'sub_button'=>array(
						array(
							'name'=>urlencode('歌曲'),
							'type'=>'click',
							'key'=>'songs',
						),
						array(
							'name'=>urlencode('电影'),
							'type'=>'view',
							'url'=>'http://www.baidu.com',
						),					
					),				
				),
				array(
					'name'=>urlencode('菜单三'),
					'type'=>'view',
					'url'=>'http://www.qq.com',			
				),
			),			
		);
		
		$menuJson = urldecode(json_encode($menuArray));
		
		var_dump($menuJson);
		
		
		$turl = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$getAccessToken.'';
		$res = $this->http_curl($turl,$type='get',$res='json',$menuJson);
		var_dump($res);
		
	}
	
	
	//function csToken(){
	//	$ch = curl_init();
		//$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx5faec86adb79db26&secret=17913645124aec3e59aefa3f41ba5a88";
		
	//	$url = "http://tool.chinaz.com/";
		
		//设置curl的参数
	//	curl_setopt($ch, CURLOPT_URL, $url);
	//	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	//	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		
		//当为post类型的时候
	//	if($type='post'){
	//		curl_setopt($ch, CURLOPT_POST, $url);
	//		curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);			
	//	}
		
		//采集
	//	$output = curl_exec($ch);
	//	var_dump($output);
		
	//}

	
	//获取微信用户列表
	function getOpenId(){
		//获取token
		$theToken = $this->getWxAccessToken();

		$theUrl = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$theToken."&next_openid=";
		
		$res = $this->http_curl($theUrl,'get','json');
		
		echo "<br/>";
		//print_r($res);		
		return $res;
	}
	
	
	//根据openID进行群发
	function sendMsAll(){
		//1.获取token
		$theToken = $this->getWxAccessToken();
		
		$theOpenIDArray = $this->getOpenId();
		
		//print_r($theOpenIDArray);
		
		$theUrl = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=".$theToken;
		
		//组装post发送的数据
		$postArray = array(
			"touser"=>$theOpenIDArray['data']['openid'],
			"msgtype"=>"text",
			"text"=>array(
				"content"=>urlencode('这个是群发信息'),
			)
		);
		
		
		//将数组转换为json并提交
		//$postJson = urldecode(json_encode($$postArray));
		
		$postJson = json_encode($postArray);
		print_r($postJson);
		//var_dump($postJson);
		
		//2.组装数据
		//3.将数组转换为json并提交
		//4.返回相关数据
		
		
	}
	
	function returnFun($turl){
		if($turl =="definedItem"){
			$this->definedItem();			
		}
		if($turl =="getOpenId"){
			$this->getOpenId();		
		}
		if($turl =="sendMsAll"){
			$this->sendMsAll();			
		}
	}
	
}
