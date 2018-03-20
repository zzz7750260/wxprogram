<?php 
include("wx-database-conn.php");
//添加关键词
$getTheKeyWord = $_POST['theKeyWord'];
$getKeyWordMs = $_POST['keyWordMs'];

if($getTheKeyWord ==''|| $getKeyWordMs ==''){
	echo "关键词或者关键词描述不能为空";	
}
else{
	//查询关键词或者描述是否存在相同
	$checkKeySql = "select * from wp_wx_key_word where key_word = '$getTheKeyWord' or word_ms = '$getKeyWordMs'";
	$checkKeySql_db = mysql_query($checkKeySql);
	$checkKeySql_db_num = mysql_num_rows($checkKeySql_db);
	echo "关键词是否存在".$checkKeySql_db_num."</br>";
	if($checkKeySql_db_num){
		echo "该关键词或者描述存在，插入失败";
		return;		
	}
	else{
		$addKeySql = "insert wp_wx_key_word (key_word,word_ms) values ('$getTheKeyWord','$getKeyWordMs')";
		$addKeySql_db = mysql_query($addKeySql);
		if($addKeySql_db){
			echo $getTheKeyWord."信息成功插入";
		}
		else{
			echo "关键词插入失败";		
		}
	}	
}
