<?php
include("wx-database-conn.php"); 
class theKeyWordClass{		
	function keywordAdd(){
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
		
	}	

	function keywordList(){
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
	}
	
	function getReturn($turl){
		if($turl == "addKeyWord"){
			$this->keywordAdd();			
		}
		if($turl == "keywordList"){
			$this->keywordList();
		}
		
	}
}