<?php 
	include('wx-index.class.php');
	//获取微信get传来的相关参数，timestamp,nonce,token, echostr(第一次认证的时候获取的标识)
	//将三参数排序并加密
	//在将加密后的值与微信的signature进行对比	
	$theActiveWx = new wxIndexClass();
	$timestamp = $_GET['timestamp'];
	$nonce = $_GET['nonce'];
	$token = 'speedcloud';
	$signature = $_GET['signature'];
	$echostr = $_GET['echostr'];
	
	
	//将timestamp,nonce,token组成数组
	$wx_array = array($timestamp,$nonce,$token);
	
	//将数组转换成字符串	
	$tmpstr = implode('',$wx_array);
	
	//将字符串进行sha1加密
	$sha1_tmpstr = sha1($tmpstr);
	
	//echo $sha1_tmpstr;
	
	//加密后与signature进行对比
	if( $sha1_tmpstr == $signature && $echostr){
		//echo $_GET['echostr'];
		echo $echostr;
		exit;
	}
	else{
		$response = $theActiveWx->reponseMsg();	
		file_put_contents("response.txt",$response);
		echo $response;
	}
	
	//$theUrl = "http://tool.chinaz.com/";
	$theActiveWx->getWxAccessTiken();