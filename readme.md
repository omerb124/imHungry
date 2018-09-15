# Im Hungry!
# Simple and Awesome Search Engine for Food Delivery, we won't leave you hungry!

## Available Data Scraping centers
* [mishlohim.co.il](http://mishlohim.co.il)
* ~~easy.co.il~~ - SOON
* ~~mishloha.co.il~~ - SOON

## Features
* Full single rest data scraping
* Full whole city's rests data scraping

## Which data is being scraped from each restaurant?
* Restaurant name
* Customers average rating (if exists)
* Opening hours
* Full menu
	- Product name
	- Product price
	- Product description
	- Product image url
* Delivery cost
* Minimum order amount for delivery
* Restaurant category
* Address
* Address cordinates (longittude and lattitude)
* Restaurant's phone number

## How to use

### Firstly, update database connection file ('init/conn.php')
```
$servername = "localhost";
$username = "{YOUR_DB_USERNAME}";
$password = "{YOUR_DB_PASSWORD}";
// Create connection
$conn = new PDO('mysql:host=localhost;dbname={YOUR_DB_NAME}', $username, $password);
```

### Scraping single rest data
```
$rest_data = new ZapRest("{ZAP_REST_ID}");
```

### Scraping the whole city's rests data
```
$city_rests_data = new zapCity("{ZAP_CITY_ID"});
```


## TO-DOs
* To add another data centers (mishloha.co.il,easy.co.il)
* To build a great UI for searching the closest and cheapest rests around.
