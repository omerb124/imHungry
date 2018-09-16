<?php

/* File Name: Header
** Content: includes and requires the necessary files.
*/

define('PROJECT_ROOT',$_SERVER['DOCUMENT_ROOT'] . '/rests/');

// Autoload
spl_autoload_register(function($class_name){
	if(file_exists(PROJECT_ROOT . "incs/classes/" . $class_name . ".class.php")){
		require_once PROJECT_ROOT . "incs/classes/" . $class_name . ".class.php";
		return true;
	}
	return false;
});

