<?php
	require 'functions.php';

	$file = "access.log";
	$aclist = file_get_csv($file);
	$count = count(file($file));
	for($i = $count; $i >= 0; $i--){
		if($aclist[$i][1] == $_SERVER["REMOTE_ADDR"]){
			$referer = $aclist[$i][2];
			break;
		}
	}
	if(isset($referer)){
		echo "\n\n".$referer;
	}else{
		echo "\n\n"."error";
	}
?>