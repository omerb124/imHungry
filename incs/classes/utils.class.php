<?php

/* UTILS Class
*** Features list:
*** query() - Execute Query
*** dte() - Get current date
*** get_location() - Get location lat&lon by address string
*** get() - Send request to url and get response
*** get_distance() - Distance calculator using lan & lon values
*/

class Utils{

	/* Returns current date
	** @GET #format (optional) @string - date format
	** @return @string - datetime string
	*/
	public static function dte($format = 'Y/m/d H:i:s'){
		date_default_timezone_set('Asia/Jerusalem');
		return date($format);
	}

	/* Execute Query
	** @GET #pdo_query @string - PDO query to be executed
	** 		#params @array - query parameters
	** @return @array - query result
	*/
	public static function query($pdo_query,...$params){
		global $conn;

		// Prepare
		$stmt = $conn->prepare($pdo_query);

		// Check if there's an error. if so, print it
		if(!$stmt && self::$debug == true){
			print_r($stmt);
			return;
		}

		// Execute
		$stmt->execute($params);

		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Debugging
		if(self::$debug == true){
			var_dump($result);
		}

		return $result;
	}

	/* Get Lon & lat of location address by string
	** @GET #location @string - location address
	** @return @array
	*** #lat @string
	*** #lon @string
	*/
	static public function get_location($location){
		$url = sprintf("https://nominatim.openstreetmap.org/search?format=json&q=%s",urlencode($location));

		$data = Utils::get($url);
		$data = json_decode($data,true);

		if(sizeof($data) == 0) return false;

		else return array('lon' => $data[0]['lon'],
						  'lat' => $data[0]['lat']);
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
}






?>
