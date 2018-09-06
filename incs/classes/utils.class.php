<?php

/* UTILS Class
*** Features list:
*** - Execute Query (query())
*** - Get current date (dte())

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
		$data = file_get_contents("https://nominatim.openstreetmap.org/search?q=" . urlencode($location) . '&format=json');
		$data = json_decode($data,true);

		if(len($data) == 0) return false;

		else return array('lon' => $data[0]['lon'],
						  'lat' => $data[0]['lat']);
	}
}






?>
