<?php

include_once 'simple_html_dom.class.php';

	class Rest{

		// Restaurant ID
		public $id;

		// Webpage source code
		public $DOM;

		// Restaurant name
		public $name;

		// Products array
		public $products;

		// Opening hours
		public $opening_hours;

		// Restaurant rating
		public $rest_rating;

		// Location - lon
		public $lot;

		// Location - lat
		public $lat;


	}


	class Zap extends Rest{

		public function __construct($rest_id){
			$this->id = $rest_id;
			$this->_scrape_data();

			if($this->DOM != null){
				$this->products = [];

				$this->_handle_DOM();
				$this->DOM = null;
				echo json_encode($this);
			}
			else{
				echo 'cant extract DOM.';
			}
		}

		/* Scraping rest data from website
		** 	@return @array data
		*/
		private function _scrape_data(){
			try{
				$this->DOM = file_get_html($this->get_rest_url());
			}
			catch (Exception $e){
				print("Error: " . $e->getMessage());
				$this->DOM = null;
			}

		}

		/* Get resturant URL
		** return @string - restaurant url
		*/
		public function get_rest_url(){
			return 'https://www.mishlohim.co.il/menu/' . $this->id;
		}

		/* Handling HTML source code, extracting wanted data:
		*** $this->name @string - restaurant name
		*** $this->opening_hours @string - opening hours of restaurant
		*** $this->products @array - list of products
		***** product_name @string - product name
		***** product_price @string - product price
		***** product_desc @string - product description
		***** product_image_url @string - product url on website
		*** $this->rest_rating @string - restaurant rating
		*** $this->address @string - restaurant address
		*** $this->shipping_cost @string - shipping cost
		*** $this->min_price @string - minimum price to delivery
		*** $this->phone_number @string - phone number
		*/
		private function _handle_DOM(){
			// Restaurant name
			$this->name = ($this->DOM->find(".customerName",0)->plaintext);

			// Products list
			foreach($this->DOM->find("#menu-items-container section") as $section){
				$category_name = $section->find(".title-block h3",0)->plaintext;
				$category_array = [];

				foreach($section->find(".row-item ") as $product){
					$array = [];

					// Product name
					$array['product_name'] = $product->find(".title",0)->plaintext;

					// Proudct price without Shekels symbol
					$array['product_price'] = trim(str_replace("₪","",$product->find(".price",0)->plaintext));

					// Product description
					$array['product_desc'] = 'BB'; //$product->find(".review",0)->plaintext;

					// Product image
					$array['proudct_image'] =
					$product->find(".add-box a",0) !== null ?
					$product->find(".add-box a",0)->href :
					null;

					// Push product to category array
					array_push($category_array,$array);

				}

				// Push new product category into products list array
				array_push($this->products,array('name' => $category_name, 'products' => $category_array));
			}

			// Restaurant rating
			$this->rest_rating = $this->DOM->find(".grade",0)->plaintext;

			// Restaurant Address
			$this->address = $this->DOM->find("h4[itemprop=streetAddress]",0)->plaintext;

			// Get location lon & lat
			if(strlen($this->address) > 3){

				// Request to location API
				$location_data = Utils::get_location($this->address);
				if($location_data !== false){
					$this->lon = $location_data['lon'];
					$this->lat = $location_data['lat'];
				}
			}

			// Phone number
			$this->phone_number = $this->DOM->find(".phone-number",0)->plaintext;

			// Minimum Price
			$this->min_price = $this->DOM->find(".js-minimumPrice",0)->plaintext;

			// Shipping cost
			$this->shipping_cost = $this->DOM->find(".js-deliveryPrice",0)->plaintext;
		}
	}





?>
