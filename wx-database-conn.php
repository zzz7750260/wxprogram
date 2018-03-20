<?php 
//链接数据库服务器
$conn = mysql_connect('localhost','root','');
if($conn){
	//echo "数据库服务器连接成功";
	
	//连接数据库
	$conn_db = mysql_select_db('xyidc',$conn);
	
	if($conn_db){
	//	echo "数据库连接成功";		
		header("Content-type: text/html; charset=utf-8");
		mysql_query("set names utf8");
	}
	else{
		//echo "数据库连接失败";
		
	}
}
else{
	//echo "数据库服务器连接失败";
}