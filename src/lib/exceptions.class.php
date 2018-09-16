<?php
namespace Foodo\Lib;

// Rest already exists
class RestAlreadyExists extends Exception{

	public $rest_id;

	public function __construct($rest_id){
		$this->rest_id = $rest_id;
	}

	public function getError(){
		echo sprintf('That rest is already exists on db (ID == %s)',$this->rest_id);
	}

}



?>
