<?php

include_once '../incs/classes/utils.class.php';

//print(Utils::get_random_proxy());
//
//$url = 'https://www.mishlohim.co.il/menu/6202/#menu-page?area=telavivcenter';
//$proxy = '185.178.95.121:41258';
////$proxyauth = 'user:password';
//
//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, $url);         // URL for CURL call
//curl_setopt($ch, CURLOPT_PROXY, $proxy);     // PROXY details with port
////curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);   // Use if proxy have username and password
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  // If url has redirects then go to the final redirected URL.
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);  // Do not outputting it out directly on screen.
//curl_setopt($ch, CURLOPT_HEADER, 1);   // If you want Header information of response else make 0
//$curl_scraped_page = curl_exec($ch);
//curl_close($ch);
//
//echo $curl_scraped_page;

$location = "דרך מנחם בגין,תל אביב";
$url = sprintf("https://nominatim.openstreetmap.org/search?format=json&q=%s",urlencode($location));
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);         // URL for CURL call
//curl_setopt($ch, CURLOPT_PROXY, $proxy);     // PROXY details with port
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);   // Use if proxy have username and password
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  // If url has redirects then go to the final redirected URL.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);  // Do not outputting it out directly on screen.
curl_setopt($ch, CURLOPT_HEADER, 1);   // If you want Header information of response else make 0
$curl_scraped_page = curl_exec($ch);
curl_close($ch);

echo $curl_scraped_page;
