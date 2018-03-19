<?php 
header("Content-type: text/html; charset=utf-8");
include("wx-database-conn.php");
//添加关键词
$getTheKeyWord = $_POST['theKeyWord'];
$getKeyWordMs = $_POST['keyWordMs'];

if($getTheKeyWord ==''|| $getKeyWordMs ==''){
	echo "关键词或者关键词描述不能为空";	
}
else{
	$addKeySql = "insert wp_wx_key_word (key_word,word_ms) values ('$$getTheKeyWord','$getKeyWordMs')";
	$addKeySql_db = mysql_query($addKeySql);
	if($addKeySql_db){
		echo $getTheKeyWord."信息成功插入";
	}
	else{
		echo "关键词插入失败";		
	}
}