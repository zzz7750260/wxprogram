<?php 
include('wx-database-conn.php');
$theUsername = $_GET['username'];
$theUserNameSql = "select * from wp_web_users where the_username = '$theUsername'";
$theUserNameSql_db = mysql_query($theUserNameSql);
$userArray = array();

while($theUserNameSql_db_array = mysql_fetch_assoc($theUserNameSql_db)){
	$userArray = $theUserNameSql_db_array;	
}

print_r($userArray);

//将username存入session中，用于传递到获取微信用户授权的用户中

echo "user中的值".$userArray['the_username']."<br/><hr/>";


$_SESSION['username'] = $userArray['the_username'];

echo "user中的session值".$_SESSION['username']."<br/><hr/>";

$theTable .='<table>';

foreach($userArray as $key => $value){
	
	$theTable .='<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';	
};

// 生成二维码的url设置
$theEWUrl = "http://23.234.10.120/wx/wx-index.php?turl=getUserDetailCode&username=".$userArray['the_username'];

//需要对url进行转码
$enTheEWUrl = urlencode($theEWUrl);

echo $enTheEWUrl;

$theTable .='<tr><td><img src="http://pan.baidu.com/share/qrcode?w=250&h=250&url='.$enTheEWUrl.'"></td><td></td></tr></table>';

echo $theTable;
