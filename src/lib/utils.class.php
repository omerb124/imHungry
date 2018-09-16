<?php

namespace Foodo\Lib;

/* UTILS Class
** Functions list:
*** query() - Execute Query
*** dte() - Get current date
*** getLocation() - Get location lat&lon by address string
*** get() - Send request to url and get response
*** getDistance() - Distance calculator using lan & lon values
*** log() -> Send log to database
*** multiQuery() -> Execute single query with multiple values
*** getRandomProxy() -> get random proxy from proxies list (proxy_list.txt)
*** getRandomUA() -> get random user agent from user agents lists (user_agents_list.txt)
*** jsonError() -> returns json encoded array with error message
*/

class Utils{

	public static $debug = true;

	/* Returns current date
	** @GET #format (optional) @string - date format
	** @return @string - datetime string
	*/
	public static function dte($format = 'Y/m/d H:i:s'){
		date_default_timezone_set('Asia/Jerusalem');
		return date($format);
	}


	/* Multiple params queries
	** @GET #pdo_query @string - PDO query to be executed
	** 		#values_arrays @array - query parameters
	*/
	public static function multiQuery($pdo_query,$values_arrays){
		global $conn;

		// turn on exceptions && errmode
		//$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $conn->prepare($pdo_query);

		// Loop each values array and execute the query with its values
		foreach($values_arrays as $array){
			$execute = $stmt->execute($array);

			if(!$execute && self::$debug){
				echo sprintf("Error On Query:\n%s\n%s",$pdo_query,implode(" ",$stmt->errorInfo()));
			}
		}


	}

	/* Execute Query
	** @GET #pdo_query @string - PDO query to be executed
	** 		#params @array - query parameters
	** @return @array - query result
	*/
	public static function query($pdo_query,$returnLastId=false,...$params){
		global $conn;

		// If deubgging - turn on exceptions
		//$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		try{
			// Prepare
			$stmt = $conn->prepare($pdo_query);

			// Execute
			$stmt->execute($params);

			// If last inserted ID is necessary
			$lastId = $returnLastId ? $conn->lastInsertId() : null;

			// Query result
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);


			if($returnLastId){
				return array('last_id' => $lastId,
							'result' => $result);
			}

			return $result;

		} catch (PDOException $e){
			// If query error
			// Log to database
			self::log(false,"Query error-> " . $e->getMessage());

			// If debug ON - print to screen
			if(self::$debug){
				print($e->getMessage());
			}
		}

	}

	/* Get Lon & lat of location address by string
	** @GET #location @string - location address
	** @return @array
	*** #lat @string
	*** #lon @string
	*/
	static public function getLocation($location){

		// Decode special characters
		$location = htmlspecialchars_decode($location,ENT_QUOTES);

		// Prepare request URL
		$url = sprintf("https://nominatim.openstreetmap.org/search?format=json&q=%s",urlencode($location));

		// Send request and make it able to read
		$data = self::get($url,false);
		$data = json_decode($data,true);

		if(sizeof($data) == 0){

			// Service cannot found the location, lets try with another api service
			$url2 = sprintf("https://geocoder.api.here.com/6.2/geocode.json?app_id=BgBrb1phfLvzctdAipg2&app_code=tLLkS5G4pzNmPNEI2jpsxQ&searchtext=%s&country=il",urlencode($location));

			$data2 = self::get($url2,false);
			$data2 = json_decode($data2,true);

			// If location not found also with the another api
			if(sizeof($data2['Response']['View']) == 0) return false;

			// If location has found, lets return it
			$result = $data2['Response']['View'][0]['Result'][0]['Location']['NavigationPosition'][0];
			return array('lon' => $result['Longitude'],
						 'lat' => $result['Latitude']);
		} else {
			return array('lon' => $data[0]['lon'],
						 'lat' => $data[0]['lat']);
		}
	}

	/* Like file_get_contents, but much better.
	** @GET #url @string - url to be opened
			#proxy @boolean - request via proxy? default is true
			#random_ua @boolean - request via random user agent? default is true
	** @return @string - response
	*/
	static public function get($url,$proxy=true,$random_ua=true){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);         // URL for CURL call

		if($proxy):
			curl_setopt($ch, CURLOPT_PROXY, self::get_random_proxy());     // PROXY details with port
			//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);   // Use if proxy have username and password
			curl_setopt($ch, CURLOPT_USERAGENT, self::get_random_user_agent()); // Setting a user agent
		endif;

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  // If url has redirects then go to the final redirected URL.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);  // Do not outputting it out directly on screen.
		curl_setopt($ch, CURLOPT_HEADER, 1);   // If you want Header information of response else make 0
		$curl_scraped_page = curl_exec($ch);
		curl_close($ch);

		return $curl_scraped_page;
	}

	/* Distance calculator
	** @GET
	** #location1 @array - cordinates no.1
	**** #lon @string
	**** #lat @string
	** #location2 @array - cordinates no.2
	**** #lon @string
	**** #lat @string
	** #unit @string - unit to be returned
	** @return
	** @int - result in choosen unit
	*/
	static public function getDistance($location1,$location2,$unit="K"){
		$theta = $location1['lon'] - $location2['lon'];
		$dist = sin(deg2rad($location1['lat'])) * sin(deg2rad($location2['lat'])) +  cos(deg2rad($location1['lat'])) * cos(deg2rad($location2['lat'])) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "K") {
			return ($miles * 1.609344);
		} else if ($unit == "N") {
			return ($miles * 0.8684);
		} else {
			return $miles;
		}
	}

	/* Adding log to database
	** @GET #status @boolean status of log - false (negative) or true (positive)
	** 		#message @string log message
	** @void
	*/
	static public function log($status,$message){
		// Convert boolean to int
		$status = $status ? "0" : "1";

		self::query("INSERT into logs (status,message,date) values (?,?,?)",false,$status,$message,self::dte());
	}

	/* Get random proxy from list (proxy_list.txt)
	** @return @string proxy (ip:port format)
	*/
	static public function get_random_proxy(){
		$proxy_list = file( PROJECT_ROOT . "proxy_list.txt");
		return $proxy_list[rand(0,sizeof($proxy_list)-1)];
	}

	/* Get random user agent from list (user_agents_list.txt)
	** @return @string user agent
	*/
	static public function getRandomUA(){
		$list = file( PROJECT_ROOT . "ua.txt");
		return $list[rand(0,sizeof($list)-1)];
	}

	/* Returns an json encoded array of error message
	** @GET @string error message
	**		@int status code (optional)(default=403)
	** @return @array
	*/
	static public function jsonError($message,$status=403){
		echo json_encode(
			array('status' => $status,
				  'message' => $message)
		);
	}

}






?>
