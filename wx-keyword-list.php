<?php
include("wx-database-conn.php");

$wxKeyWordListSql = "select * from wp_wx_key_word";

$wxKeyWordListSql_db = mysql_query($wxKeyWordListSql);

$wxKeyWordListArray = array();

$theTable .='<table><th>关键词ID</th><th>关键词</th><th>对应内容</th><th>操作</th></tr>';

while($wxKeyWordListSql_db_array = mysql_fetch_assoc($wxKeyWordListSql_db)){
	$wxKeyWordListArray[] = $wxKeyWordListSql_db_array;	
	$theTable .='<tr><td>'.$wxKeyWordListSql_db_array['kid'].'</td><td>'.$wxKeyWordListSql_db_array['key_word'].'</td><td>'.$wxKeyWordListSql_db_array['word_ms'].'</td><td></td></tr>';
}
$theTable .='</table>';

echo $theTable;
