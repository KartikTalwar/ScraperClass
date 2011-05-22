PHP Scraper Class
=======

## DESCRIPTION

This class is a collection of helpful functions to help make data mining and scraping easier

## INSTALLATION

To Install the class, you simply need to add this code

	<?php
	
		include "ScraperClass.php";
	
	?>

## USAGE

* Load
* Cut
* Strip HTML
* more


## EXAMPLES
	
1. Loading URL


		<?php
		
			include "ScraperClass.php";
		
			$scraper = new Scraper();
			$get = $scraper->load("http://yahoo.com/");
		
			echo $get;
		
		?>