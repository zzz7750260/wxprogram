<?php
include("wx-key-class.php");

$turl = $_GET['turl'];

if(!$turl){
	$turl = $_POST['turl'];	
}

$theKey = new theKeyWordClass();

$theKey->getReturn($turl);