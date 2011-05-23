PHP Scraper Class
=======

## Description

This class is a collection of helpful functions to help make data mining and scraping easier

## Installation

To Install the class, you simply need to add this code

	<?php
	
		require("ScraperClass.php");
	
	?>

## Functions

* Load
* Cut
* Strip HTML
* more

## TODO

* Finish cutCeption
* Add content type headers

## Examples
	
1. Loading URL


		<?php
		
			require("ScraperClass.php");
		
			$scraper = new Scraper();
			$get = $scraper->load("http://yahoo.com/");
		
			echo $get;
		
		?>