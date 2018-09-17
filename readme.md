# Im Hungry!
# Simple and Awesome Search Engine for Food Delivery, we won't leave you hungry!

Basically, this API is for developers who develops food apps, or just need data about specific restaurants (like opening hours, restaurant's average rating by customers). It can be helpful also for developers who wants to check and compare the prices between different cities and populations, in order to draw conclusions.

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

## Installation 

The preferred way to install our module is by using [composer](http://getcomposer.org).
add that code to your __"composer.json"__ file:
```
{

	"repositories": [
		{
			"type": "gitlab",
			"url": "https://gitlab.com/obachar46/imHungry"
		}
	],
	"require": {           
			"obachar46/imhungry": "dev-master"
	}
}
```
Then, hit 'composer update' on cmd, and the module will be installed to your project.

## How to use __Scraping__ (for example - mishlohim.co.il restaurant)

### Firstly, how to scrape the data of single restaurant web page?
```
$restaurant = new ZapRest(7440); // Scrapes the data from restaurant with ID == 7440
```
### Get products list 
```
foreach($restaurant->products as $product){

	echo $product['product_name']; // Product name
	echo $product['product_price']; // Product price
	echo $product['product_desc']; // Product description
	echo $product['product_image']; // Product image
	
	.... other code to run on each product member
}
```

### Get restaurant opening hours
```
$opening_hours = $restaurant['opening_hours'];
for($i = 0; $i <= 6; $i++){
	
	if($opening_hours[$i] !== false){
		echo sprintf("On day %s of the week, we are active on %s",$i+1,$opening_hours[$i]);
	}
	else{
		echo sprintf("On day %s of the week, we are not active");
	}
}
```

### Get other settings of restaurant
```
echo $restaurant['address']; // Restaurant address
echo $restaurant['lon']; // Restaurant address longitude
echo $restaurant['lat']; // Restaurant address latitude
echo $restaurant['category']; // Restaurant category on website
echo $restaurant['rest_rating']; // Restaurant average users rating on website
```

## TO-DOs
* To add another data centers (mishloha.co.il,easy.co.il)
