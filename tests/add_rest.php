<?php

require '../init/conn.php';
include_once '../incs/classes/rest.class.php';
include_once '../incs/classes/simple_html_dom.class.php';

if(isset($_GET['id'])):
	$m = new ZapRest($_GET['id'],"telavivcenter");
endif;

?>
