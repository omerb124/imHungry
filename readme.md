# Im Hungry!
# Simple and Awesome Search Engine for Food Delivery, we won't leave you hungry!

Basically, this API is for developers who develops food apps, or just need data about specific restaurants (like opening hours, restaurant's average rating by customers). It can be helpful also for developers who wants to check and compare the prices between different cities and populations, in order to draw conclusions.

## Available Data Scraping centers
* [mishlohim.co.il](http://mishlohim.co.il)
* ~~easy.co.il~~ - SOON
* ~~mishloha.co.il~~ - SOON

## Features
* Full single rest data scraping (directly to database)
* Full whole city's rests data scraping (directly to database)

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

## How to use __Scraping with database__

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
## How to use Scraping API


## TO-DOs
* To add another data centers (mishloha.co.il,easy.co.il)
* To build a beautiful API for getting the data
