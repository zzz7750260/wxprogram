<?php 
session_start();
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
				//当传入的词为流量查询时，优先查询
				//if(trim($postObj->Content)=='流量查询'){
				//	$theInfo = $this->setWebDataMb($postObj->FromUserName);
				//	return $theInfo;				
				//}
				//else{
					$theInfo = $this->get_reponse_msg('text');
					return $theInfo;						
				//}									
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
				//当传入的词为流量查询时，优先查询
				case '流量查询':{
					$info = $this->setWebDataMb($toUser);
					$content = $info;
					break;					
				}
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
					"name"=>urlencode('服务器信息'),
					"key"=>"item1",
					
				),
				array(
					'name'=>urlencode('公司资讯'),
					'sub_button'=>array(
						array(
							'name'=>urlencode('公司新闻'),
							'type'=>'click',
							'key'=>'songs',
						),
						array(
							'name'=>urlencode('公司活动'),
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
	function sendMsAll($isTest = "true"){
		//1.获取token
		$theToken = $this->getWxAccessToken();
		
		$theOpenIDArray = $this->getOpenId();
		
		//print_r($theOpenIDArray);
		
		if($isTest == "true"){
			$turl = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=".$theToken;
			$postArray = array(
				//"touser"=>$theOpenIDArray['data']['openid'],
				"touser"=>"oLWCs0cYXR3JpvPifMNcqUJBoXWI",
				"msgtype"=>"text",
				"text"=>array(
					"content"=>urlencode('这个是测试群发信息'),
					//"content"=>'这个是群发信息',
				)
			);						
		}
		else{			
			$turl = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=".$theToken;
			$postArray = array(
				"touser"=>$theOpenIDArray['data']['openid'],
				//"touser"=>"oLWCs0cYXR3JpvPifMNcqUJBoXWI",
				"msgtype"=>"text",
				"text"=>array(
					"content"=>urlencode('这个是群发信息'),
					//"content"=>'这个是群发信息',
				)
			);						
		}
		
		
		//$theUrl = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=".$theToken;
		
		//组装post发送的数据
		
		
		//将数组转换为json并提交
		$postJson = urldecode(json_encode($postArray));
		
		//$postJson = json_encode($postArray);
		print_r($postJson);
		//var_dump($postJson);
		
		//设置调用微信群发预览接口
		
		//对数据进行curl的post请求
		$res = $this->http_curl($turl,'post','json',$postJson);
				
		echo "<br/><hr/>";

		print_r($res);	
		
		
		//2.组装数据
		//3.将数组转换为json并提交
		//4.返回相关数据				
	}
	
	
	//设置调用测试模板
	function setMb(){
		//获取微信的access_token
		$theToken = $this->getWxAccessToken();
		
		//获取关注的微信openId
		
		//组装array数据
		$MbArray = array(
			'touser'=>'oLWCs0cYXR3JpvPifMNcqUJBoXWI',
			'template_id'=>'-RoL0WNPjeFS8N7pKndFisrPl-YwP3qoBXbEqWccCOE',
			'url'=>'http://www.hostspaces.net',
			'data'=>array(
				'name'=>array(
					'value'=>'hello',
					'color'=>'#ff0000'
				),
				'money'=>array(
					'value'=>10,
					'color'=>'#ff0000'
				),
				'date'=>array(
					'value'=>date('Y-m-d H:i:s'),
					'color'=>'#ff0000'
				)
			),
			
		);
		
		//将array转换为json
		$MbJson = json_encode($MbArray);

		//输出
		//print_r($MbJson);
		
		//提交模板信息的微信接口
		$turl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$theToken;
		
		//对封装的json数据发送curl的post请求
		$res = $this->http_curl($turl,'post','json',$MbJson);
		print_r($res);
		
	}
	
	
	//设置流量返回模板
	function setWebDataMb($theOpenId){
		//获取access_token
		$theToken = $this->getWxAccessToken();
		
		//$strXxArray =implode("",$theToken); 
		file_put_contents("theToken.txt",$theToken);
		
		file_put_contents("openID.txt",$theOpenId);
						
		//获取关联的数据返回
		//以下这两种数据库查询都可以
		$theDataSql = "select a.*, b.* from wp_wx_web_token as a join wp_web_data as b where a.web_username = b.the_username and a.wx_openid = '$theOpenId'";
		
		$theDataSql_db = mysql_query($theDataSql);
		
		$theDataSqlArray = array();
		
		while($theDataSql_db_array = mysql_fetch_assoc($theDataSql_db)){
			$theDataSqlArray = $theDataSql_db_array;			
		}
		print_r($theDataSqlArray);
		
		//组合成需要的微信的post数据组
		$MbArray = array(
			'touser'=>''.$theOpenId.'',
			'template_id'=>'MM9ZKA8cJzPuc9Hn4XlBfukbMvVn0NUu_l-JaHPfOb8',
			'url'=>'http://www.hostspaces.net',
			'data'=>array(
				'name'=>array(
					'value'=>$theDataSqlArray['web_username'],
					'color'=>'#ff0000'
				),
				'data'=>array(
					'value'=>$theDataSqlArray['the_data'],
					'color'=>'#ff0000'
				),
				'ddos'=>array(
					'value'=>$theDataSqlArray['the_ddos'],
					'color'=>'#ff0000'
				),
				'cc'=>array(
					'value'=>$theDataSqlArray['the_cc'],
					'color'=>'#ff0000'
				),		
				'utc'=>array(
					'value'=>$theDataSqlArray['the_utc'],
					'color'=>'#ff0000'
				),	
				'ttime'=>array(
					'value'=>date('Y-m-d H:i:s'),
					'color'=>'#ff0000'
				)	
			)
		);	

		//将array变成json数据进行提交
		$MbJson = json_encode($MbArray);
		
		print_r($MbJson);
		
		//设置微信模板提交请求接口
		$mbUrl = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$theToken;
		
		//将数据进行post提交
		$res = $this->http_curl($mbUrl,'post','json',$MbJson);
		print_r($res);
		
		$strXxArray =implode("",$res); 
		file_put_contents("resarraycontent.txt",$strXxArray);
		
		if(!$res['errcode']){
			$info = "查询成功";
		}
		else{
			$info = "查询失败";
		}
		return $info;
	}
		
	
	//获取页面授权
	function getUserWebCode(){
		//获取微信的AppID
		//$appID = "wx14f88739efb836b1";

		//获取微信的AppSecret
		//$appSecret = "518471bf295994da56ca601817769af5";
		
		//测试号的appID与appsecret
		$appID = "wx5faec86adb79db26";
		$appSecret = "17913645124aec3e59aefa3f41ba5a88";	
		
		//获取access_token
		$theToken = $this->getWxAccessToken();
		
		//这个链接主要是跳转获取到微信网页授权的access_token的链接
		$getUrl = "http://23.234.10.120/wx/wx-index.php?turl=getUserWebToken";
		
		//根据要求对$getUrl进行urlEncode转码
		$enUrl = urlencode($getUrl);
		
		//获取类型选择
		$getType = "snsapi_base";
		
		
		//echo $enUrl."<br/><hr/>";
		//获取微信code的接口
		$codeUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appID."&redirect_uri=".$enUrl."&response_type=code&scope=".$getType."&state=123#wechat_redirect";
		
		//echo $codeUrl;
		//跳转到接口getWebToken的页面
		header('location:'.$codeUrl);
	}
	
	//获取微信access_token的方法
	function getUserWebToken(){
		//获取微信的AppID
		//$appID = "wx14f88739efb836b1";

		//获取微信的AppSecret
		//$appSecret = "518471bf295994da56ca601817769af5";
		
		//测试号的appID与appsecret
		$appID = "wx5faec86adb79db26";
		$appSecret = "17913645124aec3e59aefa3f41ba5a88";	
		
		//获取微信网页授权的access_token链接
		$theCode = $_GET['code'];
		echo "code:".$theCode;
		print_r($theCode);
		$theUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appID."&secret=".$appSecret."&code=".$theCode."&grant_type=authorization_code";	
		
		//利用curl对网页授权的access_token进行获取
		$res = $this->http_curl($theUrl);
		print_r($res);
		return $res;
	}
	
	//获取微信详细用户信息的code
	function getUserDetailCode(){
		//获取微信的AppID
		//$appID = "wx14f88739efb836b1";

		//获取微信的AppSecret
		//$appSecret = "518471bf295994da56ca601817769af5";
		
		//测试号的appID与appsecret
		$appID = "wx5faec86adb79db26";
		$appSecret = "17913645124aec3e59aefa3f41ba5a88";			

		//由于获取code到获取access_token中需要进行一次跳转，因而需要将获取到的username存到session中
		//获取用户信息,用于将用户信息已微信信息结合起来
		$theUser = $_GET['username'];
		$_SESSION['username'] = $theUser;
		echo "index的session的user:".$_SESSION['username']."<br/><hr/>";
	
		
		//跳转获取网页授权的access_token的链接设置
		$getUrl = "http://23.234.10.120/wx/wx-index.php?turl=getUserDetailToken";
		
		//根据微信公众号的请求对url进行urlencode的扫码
		$enUrl = urlencode($getUrl);
		
		//设置snsapi的请求类型
		
		$snType = "snsapi_userinfo";
		
		//获取微信网页授权的code接口
		$codeUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appID."&redirect_uri=".$enUrl."&response_type=code&scope=".$snType."&state=123#wechat_redirect";
		
		//设置页面跳转到获取微信页面授权的token中
		header('location:'.$codeUrl);
		exit;
	}
	
	function getUserDetailToken2(){
		$theCode = $_GET['code'];
		//获取微信的AppID
		//$appID = "wx14f88739efb836b1";

		//获取微信的AppSecret
		//$appSecret = "518471bf295994da56ca601817769af5";
		
		//测试号的appID与appsecret
		$appID = "wx5faec86adb79db26";
		$appSecret = "17913645124aec3e59aefa3f41ba5a88";	
		
		//$_SESSION['CS']= "这个是测试的session";
		
		
				
		//由于code会存在一定的时间，因而在多次请求会导致出现40163的重复请求错误，因而需要将数据存入数据库来判断是否有必要进行请求
		
		$isCodeSql  = "select * from wp_wx_web_token where wx_code = '$theCode'";
		$isCodeSql_db = mysql_query($isCodeSql);
		$isCodeSql_db_num = mysql_num_rows($isCodeSql_db);
		$isCodeSqlArray = array();
		if($isCodeSql_db_num){
			while($isCodeSql_db_array = mysql_fetch_assoc($isCodeSql_db)){
				$isCodeSqlArray[] = $isCodeSql_db_array;				
			}
			print_r($isCodeSqlArray);
		}
		else{
			//设置微信网页授权获取token
			$tokenUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appID."&secret=".$appSecret."&code=".$theCode."&grant_type=authorization_code";
			
			//通过curl获取微信用户的access_token 
			$res = $this->http_curl($tokenUrl,"get");	
			echo "<script>alert (".$res.")</script>";
			
			//输出结果
			
			print_r($res);
			
			$theOpenid = $res['openid'];
			$theToken = $res['access_token'];
			$theRefresh = $res['refresh_token'];
			$theTimeEnd = time()+7000;
			$updateCode = $_GET['code'];			
			
			echo "<script>alert (".$theToken.")</script>";
			echo "<script>alert (".$updateCode.")</script>";
			
			//监测是否存在openid，如果存在的时候，对数据库进行更改
			$isTokenSql = "select * from wp_wx_web_token where wx_openid = '$theOpenid'";			
			$isTokenSql_db = mysql_query($isTokenSql);			
			$isTokenSql_db_num = mysql_num_rows($isTokenSql_db);
			
			$theTokenArray = array();
			
			//如果openId 存在的时候
			if($isTokenSql_db_num){
				$updataSql = "update wp_wx_web_token set wx_token = '$theToken', wx_refresh_token = '$theRefresh', wx_time_end = '$theTimeEnd', wx_code = '$updateCode'";
				
				$updataSql_db = mysql_query($updataSql);
				
				
				while($isTokenSql_db_array = mysql_fetch_array($isTokenSql_db)){
					$theTokenArray[] = $isTokenSql_db_array	;				
				}			
				print_r(theTokenArray);
			}
				
			//如果openId不存在的时候
			else{
				$insertSql = "insert into table (wx_token,wx_openid,wx_refresh_token,wx_time_end,wx_sope,wx_code) values ('$theToken','$theOpenid','$theRefresh','$theTimeEnd','','$updateCode')";	
				
				$insertSql_db = mysql_query($insertSql);
			}						
		}
						
	}
	
	//针对获取用户详细信息2,这个主要为测试
	function getUserDetailToken(){
		$theCode = $_GET['code'];
		//获取微信的AppID
		//$appID = "wx14f88739efb836b1";

		//获取微信的AppSecret
		//$appSecret = "518471bf295994da56ca601817769af5";
		
		//测试号的appID与appsecret
		$appID = "wx5faec86adb79db26";
		$appSecret = "17913645124aec3e59aefa3f41ba5a88";
		
		

		//获取token的微信接口
		$turl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appID."&secret=".$appSecret."&code=".$theCode."&grant_type=authorization_code";
		
		//据说绑定的网站没备案，因而测试号可能会出现两次，因而第一次将code存储，第二次再进行请求
		$theCodeSql = "select * from wx_get_code where code_value = '$theCode'";
		$theCodeSql_db = mysql_query($theCodeSql);
		$theCodeNum = mysql_num_rows($theCodeSql_db);
		echo "数据库中是否存在code".$theCodeNum."<br>";
		
		//当不存在的时候将code存入数据库
		if(!$theCode){
			echo "code 不存在";
			return false;
			
		}
		else{
			if(!$theCodeNum){
				$addCodeSql = "insert into wx_get_code (code_value) values ('$theCode')";
				$addCodeSql_db = mysql_query($addCodeSql);
				echo "<script>alert('成功插入数据');</script>";
			}
			//当存在的时候直接对端口进行请求，这样能防止二次请求而造成的code重复请求的问题
			else{
				$res = $this->http_curl($turl);
				print_r($res);
				$theOpenId = $res['openid'];
				$theAccessToken = $res['access_token'];
				$theTimeEnd = time()+7000;
				$theRefreshToken = $res['refresh_token'];
				$theScopt = $res['scope'];
				$theCodeValues = $_Get['code'];
				
				//查看是否有该openid存在，如果不存在就将数据存到数据库中，如果存在就将数据更新
				$getOpenIdSql = "select * from wp_wx_web_token where wx_openid = '$theOpenId'";
				$getOpenIdSql_db = mysql_query($getOpenIdSql);
				$getOpenIdSql_db_num = mysql_num_rows($getOpenIdSql_db);
				echo "是否存在openid：". $getOpenIdSql_db_num."<br/>";
				//当不存在时,进行储存
				if(!$getOpenIdSql_db_num){
					$addOpenIdSql = "insert into wp_wx_web_token (wx_token,wx_openid,wx_refresh_token,wx_time_end,wx_sope,wx_code) values ('$theAccessToken','$theOpenId','$theRefreshToken','$theTimeEnd','$theScopt','$theCodeValues')";
					$addOpenIdSql_db = mysql_query($addOpenIdSql);
					if($addOpenIdSql_db){
						echo "获取用户token，openid插入成功";					
					}
				}
				//当openid存在的时候，将数据进行更改
				else{
					$updateOpenIdSql = "update wp_wx_web_token set wx_token = '$theAccessToken',wx_refresh_token = '$theRefreshToken',wx_time_end = '$theTimeEnd',wx_sope = '$theScopt', wx_code= '$theCodeValues'";
					
					$updateOpenIdSql_db = mysql_query($updateOpenIdSql);
					
					if($updateOpenIdSql_db){
						echo "openId更改成功";					
					}
					
				}
				//通过openId和用户access_token获取用户详细信息
				
				//获取用户详细信息的微信接口
				$getDetaiUrl = "https://api.weixin.qq.com/sns/userinfo?access_token=".$theAccessToken."&openid=".$theOpenId."&lang=zh_CN";				
				$userRes = $this->http_curl($getDetaiUrl);
				echo "<hr/>";
				print_r($userRes);
				
				//用户信息
				$theOpenIdUser = $userRes['openid'];
				$theNickName = $userRes['nickname'];
				$theSex = $userRes['sex'];
				$theProvince = $userRes['province'];
				$theCity = $userRes['city'];
				$theCountry = $userRes['country'];
				$theHeadimgurl = $userRes['headimgurl'];
				$thePrivilege = $userRes['privilege'];

				//获取用户信息,用于将用户信息已微信信息结合起来
				$theUser = $_SESSION['username'];
				echo "index的class的user:".$theUser."<br/><hr/>";
				
				
				//将获取到的用户信息存入数据库
				$updateUseInfoSql = "update wp_wx_web_token set wx_nickname = '$theNickName', wx_sex = '$theSex', wx_province = '$theProvince', wx_city = '$theCity', wx_country = '$theCountry', wx_headimgurl = '$theHeadimgurl', wx_privilege = '$thePrivilege', web_username = '$theUser' where wx_openid = '$theOpenIdUser'";
				
				$updateUseInfoSql_db = mysql_query($updateUseInfoSql);
				
				if($updateUseInfoSql_db){
					echo "<br/><hr/>";
					echo "用户信息更改成功";
				}
			}										
		}
	}
	
	
	//获取微信JSSDKD的jsapi_ticket的值
	function get_jsapi_ticket(){
		$theToken = $this->getWxAccessToken();
		
		$theTime = time();
				
		if($_SESSION['ticket'] && $_SESSION['endtime']>$theTime = time()){
			$the_ticket = $_SESSION['ticket'];
		}
		else{
			//获取微信jsapi_ticket的接口
			$theJsapiUrl = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$theToken."&type=jsapi";
			
			//获取返回的字符串
			$res = $this->http_curl($theJsapiUrl);
			
			//获取jsapi_ticket
			$the_ticket = $res['ticket'];
			
			//将信息存储到session中
			$_SESSION['ticket'] = $the_ticket;
			$_SESSION['endtime'] = time()+7000;
			
		}	
		return $the_ticket;		
	}
	
	//获取微信JSSDK随机号码
	
	function getRandNum($num){
		$theArr = array(
		'A','B','C','D','E','F','G','H','I','G','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9');
		
		$theMax = count($theArr);
		//设置重复次数
		$theStr = '';
		for($i=0;$i<=$num;$i++){
			$randNum = rand(0,$theMax-1);
			$theStr.=$theArr[$randNum];		
		}
		//echo "============获取的随机数============<br/><hr/>";
		//echo $theStr;
		return $theStr;				
	}	
	
	function shareWx(){
		//$csArray = array(
		//	'name' =>'aaa',
		//	'body' => array(
		//		'header' =>'打手',
		//		'footer' =>'打脚'
		//	)
		//);
		
		//$csJson = json_encode($csArray);
		//print_r($csJson);		
		
		//由于生成的signature需要动态的url才能生成，因而需要从前端获取参数
		$theUrlP = $_POST['pURL'];
		
		//$enUrl = urldecode($theUrlP);
		//echo "<script>alert(".$theUrlP.");</script>";
		//echo "<br/><hr/>";
		
		$timestamp = time();
		
		//获取jsapi_ticket
		$theTicket = $this->get_jsapi_ticket();
		//print_r($theTicket);	
		
		//获取theNonceStr
		$theNonceStr = $this->getRandNum(16);
		//print_r($theNonceStr);
		
		//获取调用js的页面链接
		$theUrl = $theUrlP;
		
		//获取signature
		$theSignature = "jsapi_ticket=".$theTicket."&noncestr=".$theNonceStr."&timestamp=".$timestamp."&url=".$theUrlP."";
		$shaSignature = sha1($theSignature);
		
		
		//返回数据
		$resArr = array(
			'timestamp' => $timestamp,
			'nonceStr' => $theNonceStr,
			'signature' => $shaSignature,
		);
		
		//返回json数据给前端
		$resJson = json_encode($resArr);
		
		print_r($resJson);
	}
	
	
	//function refreshToken(){
		//获取返回的网页授权token
	//	$theWebTokenArray = $this->getWebToken();
		
	//}
	
	
	//利用phpqrcode生成二维码
	//有图片生成
	function createErweima($theUrl){
		require_once 'phpqrcode.php';
		$value = $theUrl; //获取需要生成二维码的地址;
		$errorCorrectionLevel = 'L';    //容错级别   
		$matrixPointSize = 5;           //生成图片大小
		define('_ROOT_',dirname(_FILE_).'/'); //定义当前文件的根目录
		//生成二维码图片
		$filename = _ROOT_.'/img/'.microtime().'.png'; 
		QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 2);    
		
		$theName = 'f'.microtime();
    
		$QR = $filename;                //已经生成的原始二维码图片文件     
		$QR = imagecreatefromstring(file_get_contents($QR));    
    
		//输出图片    
		imagepng($QR, './img/qrcode.png');    
		imagedestroy($QR);  
		return '<img src="./img/qrcode.png" alt="使用微信扫描支付">';     
	}
	
	//无图片生成
	function scerweimaWt($url=''){  
		require_once 'phpqrcode.php';  
		  
		$value = $url;                  //二维码内容  
		$errorCorrectionLevel = 'L';    //容错级别   
		$matrixPointSize = 5;           //生成图片大小    
		//生成二维码图片  
		ob_start();
		QRcode::png($value,false,$errorCorrectionLevel, $matrixPointSize, 2);  
		$data =ob_get_contents();
		ob_end_clean();
		return "data:image/jpeg;base64,".base64_encode($data);
	}  
	
	//利用微信生成临时二维码
	function createTemporaryErweima(){
		//获取生成临时二维码的ticket
		$theToken = $this->getWxAccessToken();
		print_r($theToken);
		echo "<br/><hr/>";
		//设置调用获取临时ticket的接口
		$theUrl = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$theToken;
		//设置post的数据
		
		$postArray = array(
			"expire_seconds" => 604800,
			"action_name" => "QR_SCENE",
			"action_info" => array(
				"scene" => array(
					"scene_id" => 2000
				)
			)
		);
		
		$postJson = json_encode($postArray);
		print_r($postJson);
		echo "<br/><hr/>";
		
		//curl提交post请求,获取ticket
		$res = $this->http_curl($theUrl,'post','json',$postJson);
		print_r($res);		
		
		$theTicket = $res['ticket'];
		
		//对ticket进行urlEncode转码
		$enTheTicket = urlencode($theTicket);
		//通过ticket获取二维码(返回为一个图片值)
		$erweiUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$enTheTicket;
		echo '<img src="'.$erweiUrl.'">';
	}
	
	//利用微信生成永久二维码
	function createForevenErweima(){
		//获取生成临时二维码的ticket
		$theToken = $this->getWxAccessToken();
		print_r($theToken);
		echo "<br/><hr/>";
		//设置调用获取临时ticket的接口
		$theUrl = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$theToken;
		//设置post的数据
		
		$postArray = array(
			"action_name" => "QR_LIMIT_SCENE",
			"action_info" => array(
				"scene" => array(
					"scene_id" => 3000
				)
			)
		);

		$postJson = json_encode($postArray);
		print_r($postJson);
		echo "<br/><hr/>";
		
		//curl提交post请求,获取ticket
		$res = $this->http_curl($theUrl,'post','json',$postJson);
		print_r($res);		
		
		$theTicket = $res['ticket'];
		
		//对ticket进行urlEncode转码
		$enTheTicket = urlencode($theTicket);
		//通过ticket获取二维码(返回为一个图片值)
		$erweiUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$enTheTicket;
		echo '<img src="'.$erweiUrl.'">';		
	}
	
	function returnFun($turl){
		if($turl =="definedItem"){
			$this->definedItem();			
		}
		if($turl =="getOpenId"){
			$this->getOpenId();		
		}
		if($turl =="sendMsAll"){
			$this->sendMsAll("false");			
		}
		if($turl == "setMb"){
			$this->setMb();
		}
		if($turl == "getUserWebCode"){
			$this->getUserWebCode();			
		}
		if($turl == "getUserWebToken"){
			$this->getUserWebToken();			
		}
		if($turl == "getUserDetailCode"){
			$this->getUserDetailCode();			
		}
		if($turl == "getUserDetailToken"){		
			$this->getUserDetailToken();
		}
		if($turl == "shareWx"){
			$this->shareWx();		
		}
		if($turl == "setWebDataMb"){
			$this->setWebDataMb('oLWCs0cYXR3JpvPifMNcqUJBoXWI');			
		}
		if($turl == "scerweimaWt"){
			$this->scerweimaWt("http://23.234.10.120/wx/index.php?utl=151135&udad=1651661");
		}
		if($turl == "createTemporaryErweima"){
			$this->createTemporaryErweima();
		}
		if($turl == "createForevenErweima"){
			$this->createForevenErweima();
		}
	}
	
}
