<?php

include_once 'simple_html_dom.class.php';

class Scraper{

	// Url to be scraped
	public $url;

	// Web page HTML source code
	public $html;

	public function __construct($url){
		$this->url = $url;
		$this->_extract_data();

	}

	private function _extract_data(){
		try{
			$data = file_get_contents($this->url);
			$this->html = data;
		}
		catch (Execption $e){
			print("Error:",$e->getMessage());
			$this->html = null;

		}
	}
}
