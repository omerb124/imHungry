<?php

/* UTILS Class
** Functions list:
*** query() - Execute Query
*** dte() - Get current date
*** get_location() - Get location lat&lon by address string
*** get() - Send request to url and get response
*** get_distance() - Distance calculator using lan & lon values
*** log() -> Send log to database
*** multi_query() -> Execute single query with multiple values
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
	public static function multi_query($pdo_query,$values_arrays){
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
	static public function get_location($location){

		// Decode special characters
		$location = htmlspecialchars_decode($location,ENT_QUOTES);

		// Prepare request URL
		$url = sprintf("https://nominatim.openstreetmap.org/search?format=json&q=%s",urlencode($location));

		// Send request and make it able to read
		$data = Utils::get($url);
		$data = json_decode($data,true);

		if(sizeof($data) == 0){

			// Service cannot found the location, lets try with another api
			$url2 = sprintf("https://geocoder.api.here.com/6.2/geocode.json?app_id=BgBrb1phfLvzctdAipg2&app_code=tLLkS5G4pzNmPNEI2jpsxQ&searchtext=%s&country=il",urlencode($location));

			$data2 = Utils::get($url2);
			$data2 = json_decode($data2,true);

			// If location not found also with the another api
			if(sizeof($data2['Response']['View']) == 0) return false;

			// If location has found, lets return it
			$result = $data2['Response']['View'][0]['Result'][0]['Location']['NavigationPosition'][0];
			return array('lon' => $result['Longitude'],
						 'lat' => $result['Latitude']);
		}

		else{
			return array('lon' => $data[0]['lon'],
						 'lat' => $data[0]['lat']);
		}
	}

	/* Like file_get_contents, but much better.
	** @GET #url @string - url to be opened
	** @return @string - response
	*/
	static public function get($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
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
	static public function get_distance($location1,$location2,$unit="K"){
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


}






?>
