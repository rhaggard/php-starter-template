<?php

//
// GUID generator that works in all php versions and on all platforms.
//

function guid(){
	if (function_exists('com_create_guid')){
		return strtolower(trim(com_create_guid(), '{}'));
	}
	else {
		mt_srand((double)microtime()*10000); //optional for php 4.2.0 and up.
		$charid = md5(uniqid(rand(), true));
		$hyphen = chr(45);// "-"
		
		$uuid = substr($charid, 0, 8) . $hyphen
		       .substr($charid, 8, 4) . $hyphen
		       .substr($charid,12, 4) . $hyphen
		       .substr($charid,16, 4) . $hyphen
		       .substr($charid,20,12);

		return $uuid;
	}
}
?>
