<?php

namespace Foodo;


class ZapCity extends City{

	public function __construct($id){
		// ID
		$this->id = $id;

		// Get rests ID's
		$this->rests_ids = $this->_getRestsIds();

		// Scrape the rests data
		$this->_scrapeRestsData();
	}

	/* Returns city's rests index url on website
	** @return @string index url
	*/
	public function getCityUrl(){
		return sprintf('https://www.mishlohim.co.il/food_delivery/%s/all_food_types/',$this->id);
	}

	/* Loop rests ID's and scrapes the data
	** @void
	*/
	private function _scrapeRestsData(){

		// Debugging
		if(Utils::$debug){
			var_dump($this->rests_ids);
		}

		foreach($this->rests_ids as $rest_id){
			print("Adding zapRest No. " . $rest_id);
			$rest_data = new ZapRest($rest_id,$this->id);
			usleep(200000);
		}
	}

	/* Scrapes the city's rests index in order to find the rest's IDs.
	** @return array of @string (rest IDs)
	*/
	private function _getRestsIds(){

		$array = [];

		// Extract DOM
		$data = file_get_html($this->getCityUrl());

		// Extract rests IDs from first page
		foreach($data->find("div[data-minimum-price]") as $row){
			array_push($array,$row->getAttribute("data-customer-id"));
		}

		// Calculate pages amount
		$rests_amount_match = preg_match_all('([0123456789])',$data->find(".restaurants-amount",0)->plaintext,$out);
		$rests_amount = implode('',$out[0]);

		// Calculate pages amount
		$pages = intval($rests_amount / 20) + $rests_amount % 20;

		// Extract rest ids from each page
		for($i = 2; $i <= $pages; $i++){

			// Get page source code
			$url = $this->getCityUrl() . '?page=' . $i;
			$data = file_get_html($url);

			// Extract rests IDs from first page
			foreach($data->find("div[data-minimum-price]") as $row){
				array_push($array,$row->getAttribute("data-customer-id"));
			}
		}

		return $array;


	}

}


?>
