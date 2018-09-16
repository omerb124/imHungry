<?php

namespace Foodo\Lib;

class Rest{

		// Restaurant ID
		public $id;

		// Webpage source code
		public $DOM;

		// Restaurant name
		public $name;

		// Restaurant's logo url
		public $logo_image;

		// Products array
		public $products;

		// Opening hours
		public $opening_hours;

		// Restaurant rating
		public $rest_rating;

		// Location - lon
		public $lon;

		// Location - lat
		public $lat;

		// City Name
		public $city_slug;

		// Rest Category
		public $category;

		// Is it a custom menu
		public $custom_menu;

	}


	class ZapRest extends Rest{

		public function __construct($rest_id,$city_slug = 'none'){

			// Restaurant ID
			$this->id = $rest_id;

			// City name
			$this->city_slug = $city_slug;

			// Scraping data
			$this->_scrapeData();

			if($this->DOM != null){

				// Initial products list array
				$this->products = [];

				// Handling DOM
				$this->_handleDOM();

				// If menu is not a custom menu
//				if($this->custom_menu !== true){
//
//					// Adding to database
//				    $this->_addToDb();
//
//				}
			}
			else{
				echo 'cant extract DOM.';
			}
		}

		/* Check if rest with this ZAP ID has been already added.
		** @GET #rest_id @int Rest ID on zap
		** @return @boolean
		*/
		public static function restIdExists($rest_id){
			$data = Utils::query("SELECT COUNT(id) as count FROM rests WHERE data_id=? LIMIT 1",false,$rest_id);

			return ($data['count'] > 0);
		}

		/* Scraping rest data from website
		** 	@return @array data
		*/
		private function _scrapeData(){
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
		public function getRestUrl(){
			return 'https://www.mishlohim.co.il/menu/' . $this->id;
		}

		/* Handling HTML source code, extracting wanted data:
		*** $this->name @string - restaurant name
		*** $this->logo_image @string - logo url
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
		*** $this->opening_hours @array - array of opening hours (index: 0 - sunday, 6 - saturday)
		*/
		private function _handleDOM(){

			// Validate that this is not a customed menu
			// (like in https://www.mishlohim.co.il/ExternalMenu.aspx?BusinessID=5034)
			if($this->DOM->find(".middle-menu",0)){

				// Restaurant name
				$this->name = ($this->DOM->find(".customerName",0)->plaintext);

				if(!$this->DOM->find(".customerName",0)){
					print("NO NAME ON " . $this->id);
				}

				// Restaurant's logo image
				if($this->DOM->find(".icon-company img",0)){
					$this->logo_image = $this->DOM->find(".icon-company img",0)->src;
				}


				// Products list
				foreach($this->DOM->find("#menu-items-container section") as $section){

					// Category name
					if($section->find(".title-block h3",0)){
						// Validate that exists
						$category_name = $section->find(".title-block h3",0)->plaintext;
					}
					else{
						$category_name = -1;
					}

					$category_array = [];

					foreach($section->find(".row-item ") as $product){
						$array = [];

						// Product name
						$array['product_name'] = $product->find(".title",0)->plaintext;

						// Product Price
						// Try to find the price element
						$price_class = $product->find(".price",0);

						if($price_class){
							$array['product_price'] = trim(str_replace("₪","",$price_class->plaintext));
						}
						else{
							// If cannot found price class, set it to 0 (won't be added to DB)
							$array['product_price'] = 0;
						}

						// Product description
						$array['product_desc'] = $product->find(".review",0)->plaintext;

						// Product image
						$array['product_image'] =
						$product->find(".add-box a",0) !== null ?
						$product->find(".add-box a",0)->href :
						'';

						if($array['product_price'] != 0){
							// Push product to category array
							array_push($category_array,$array);
						}


					}

					// Push new product category into products list array
					array_push($this->products,array('name' => $category_name, 'products' => $category_array));
				}

				// Restaurant rating
				$rest_rating = $this->DOM->find(".grade",0);
				// Check if rating does exists, if not - return 1
				$this->rest_rating = $rest_rating ? $rest_rating->plaintext : -1;

				// Restaurant Address
				$this->address = $this->DOM->find("h4[itemprop=streetAddress]",0)->plaintext;

				if(!$this->DOM->find("h4[itemprop=streetAddress]",0)){
					print("NO ADDRESS ON " . $this->id);
				}



				// Get location lon & lat
				if(strlen($this->address) > 3){

					// Request to location API
					$location_data = Utils::getLocation($this->address);
					if($location_data !== false){
						$this->lon = $location_data['lon'];
						$this->lat = $location_data['lat'];
					}
					else{
						$this->lon = $this->lat = '';
					}
				}

				// Opening Hours
				$this->opening_hours = [];
				if($this->DOM->find(".hours-area",0)){
					$hours_div = $this->DOM->find(".hours-area",0);
					foreach($hours_div->find(".day-block") as $day){

						// Push each's day opening hours
						array_push($this->opening_hours,$day->find(".time",0)->plaintext);
					}
				}

				// Phone number
				if($this->DOM->find(".phone-number",0)){
					$this->phone_number = $this->DOM->find(".phone-number",0)->plaintext;
				}
				else{
					$this->phone_number = -1;
				}


				// Minimum Price
				if($this->DOM->find(".js-minimumPrice",0)){
					// Validate that exists
					$this->min_price = trim(str_replace("₪","",$this->DOM->find(".js-minimumPrice",0)->plaintext));
				}
				else{
					$this->min_price = -1;
				}


				// Shipping cost
				if($this->DOM->find(".js-deliveryPrice",0)){
					// Validate that exists
					$this->shipping_cost = trim(str_replace("₪","",$this->DOM->find(".js-deliveryPrice",0)->plaintext));
				}
				else{
					$this->shipping_cost = -1;
				}


				// Category
				$this->category = $this->DOM->find("div[data-key=r_category]",0)->getAttribute("data-value");

			}
			else{
				$this->custom_menu = true;
			}
		}

		/* Adding rest data to DB
		** @void
		*/
		private function _addToDb(){

			// Adding cateogry to database, if exists - return existing ID record
			$db_cat_id = $this->_addCategoryToDb();

			try{
				// Adding rest to database according to given category id, if exists - returns existing ID
				$db_rest_id = $this->_addRestToDb($db_cat_id);

				var_dump($db_rest_id,"DB REST ID",$this->id,"ZAP ID");

				// Adding products to database, according to provided rest ID
				$this->_addProductsToDb($db_rest_id);

				// Update logs about success
				Utils::log(true,sprintf("New rest has been added. ID == %s, CITY_SLUG == %s",$db_rest_id,$this->city_slug));

			} catch (RestAlreadyExists $e){
				// Rest already exists on db
				Utils::log(false,$e->getError());
				die($e->getError);

			} catch (Exception $e){
				// Unknown error
				Utils::log(false,sprintf('An error occured during _add_to_db of Restaurant ID "%s": %s',$this->id,$e->getMessage()));
				echo $e->getMessage();
				die($e->getMessage());
			}





		}

		/* Add category to DB
		** Starting with checking if category is already exists.
		** @return @int record's id of category on database, also if category exists
		*/
		private function _addCategoryToDb(){

			if($this->category){

				// Check if category exists
				$check_query = Utils::query("SELECT id as exists_id FROM categories WHERE name=? LIMIT 1",false,$this->category);

				if(sizeof($check_query) == 0){
					// Category does not exists
					// Add category to database
					// Return last inserted id
					$cat_id = Utils::query("INSERT into categories (name) values (?)",true,$this->category)['last_id'];


				}
				else{
					// Category does exists, get the exists category id on db
					$cat_id = $check_query[0]['exists_id'];
				}

				return $cat_id;
			}
			return 0;
		}

		/* Add rest to DB
		** Starting with checking if restaurant has already been added before, if so - returns false
		** @GET @int category's id on database
		** @return @int rest's row ID on database
		*/
		private function _addRestToDb($db_cat_id){

			try{
				// Check if restaurant with name & location
				if(3 > 1){
					$check_query = Utils::query("SELECT id FROM rests WHERE location_lon=? AND location_lat = ? AND min_price = ? AND shipping_cost = ?",
												false,
												$this->lon,
												$this->lat,
												$this->min_price,
												$this->shipping_cost
					);
					if(sizeof($check_query) > 0){
						// Rest already exists, throws an exception
						throw new RestAlreadyExists($check_query[0]['id']);

					}
					else{
						// Rest does not exists, lets add it and get the new row's ID
						$rest_id = Utils::query("INSERT into rests (name,city_id,location_lon,location_lat,address,logo_image_url,rating,category_id,opening_hours,min_price,phone_number,shipping_cost,date_added,data_id,type) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
												true,
												$this->name,
												$this->city_slug,
												$this->lon,
												$this->lat,
												$this->address,
												$this->logo_image,
												$this->rest_rating,
												$db_cat_id,
												serialize($this->opening_hours), // Serialized array of opening hours
												$this->min_price,
												$this->phone_number,
												$this->shipping_cost,
												Utils::dte(), // Current date
												$this->id,
												'zap'
						)['last_id'];

						// Return new rest row's ID
						return $rest_id;

					}
				}

			} catch (Exception $e){
				throw new Exception($e->getMessage());
			}

		}

		/* Add products to DB
		** Loop each product, check if row already exists and if not so - adds it
		** @GET #db_rest_id @int - Rest ID on database
		** @void
		*/
		private function _addProductsToDb($db_rest_id){

			foreach($this->products as $category){

				// Products array, will be executed with prepared query later
				$products_array_to_query = array();

				// Category name
				$category_name = $category['name'];

				foreach($category['products'] as $product){
					// Prepare product data array
					$product_array_to_push = array(
						$product['product_name'],
						$product['product_price'],
						$product['product_desc'],
						$product['product_image'],
						$category_name,
						$db_rest_id,
						Utils::dte()
					);

					// Push product array to products array, in order to execute it on query later
					array_push($products_array_to_query,$product_array_to_push);
				}

				// Main query to be executed
				$query = "INSERT into products (name,price,description,image_path,category,rest_id,date_added) values (?,?,?,?,?,?,?)";

				try{

					// Execute query
					Utils::multiQuery($query,$products_array_to_query);

				} catch (Exception $e){

					// If error, log & die
					Utils::log(false,sprintf('An error occured during _add_products_to_db of Restaurant ID "zap%s": %s',$this->id,$e->getMessage()));
					die($e->getMessage());
				}
			}
		}

	}






?>
