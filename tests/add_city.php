<?php

/*
*** Test name: Adding city's restaurants to database
*** Goal: to add the whole city's restaurants to our DB.
*/

require_once '../init/conn.php';

include_once '../incs/classes/city.class.php';

$tlv = new ZapCity("telavivcenter");
