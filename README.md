#PHP Scraper Class

## Description

This class is a collection of helpful functions to help make data mining and scraping easier

## Installation

To Install the class, you simply need to add this code

	<?php
	
		require("ScraperClass.php");	// include class
	
	?>

## Functions

* `load($url)`
* `cut($start, $end, $from)`
* `cutMultiple($start, $end, $from)`
* `strip($html, $exceptions)`
* `escape($html)`
* `unescape($html)`
* `externalCSS($url)`
* `externalJS($url)`
* `replace($what, $with, $from)`
* `parseJSON($data)`
* `parseXML($url)`
* `xpath($path, $url)`
* `submitPOST($param, $url)`
* `submitGET($param, $url)`
* `isURL($text)`
* `getURLs($html)`
* `getRedirects($url)`
* `getRealPath($url)`
* `HTTPStatus($code, $url)`
* `cache($data, $key)`
* `getCache($key)`


## TODO

* Finish cutCeption
* Add content type headers

## Examples
	
1. **Loading a URL** :
	The following method can get the contents of any page including pages with GET requests and parameters in the URLs.

		<?php
		
			require("ScraperClass.php");
		
			$scraper = new Scraper();
			$get = $scraper->load("http://www.yahoo.com/");
		
			echo $get;
		
		?>

1. **More coming soon**