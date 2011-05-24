<?php



/**
 * The Scraper Class with helpful functions that are essential to data 
 * mining and screen scraping.
 *
 * @author     	Kartik Talwar
 * @version    	1.0
 * @example	./examples.php
 * @link	http://github.com/kartiktalwar/Scraper-Class
 */
class Scraper
{

	public $dir = "./cache";	// cache directory
	public $expiration = 3600;	// cache expiration time in seconds
	
	
	/**
	 * Constructor
	 *
	 * The following function does nothing at the moment
	 *
	 * @param	(none) NONE
	 * @return	(bool) Returns True
	 */
	public function __construct()
	{
		return True;
	}

	
	/**
	 * Load Function
	 *
	 * The following function gets the contents of the webpage
	 *
	 * @param	(string) $url The URL of the page to load
	 * @return	(string) $data The contents of the URL
	 */
	public function load($url)
	{
		$url = $this->replace( array(' '), array('+'), $url );	// remove spaces
		
		// if file_get_contents exists use that
		if( function_exists("file_get_contents"))
		{
			return file_get_contents($url);	// return the contents
		}
		// otherwise use curl
		elseif ( function_exists("curl_init") )
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);	// get the url contents
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/11.0.696 Safari/525.13');	// set user agent
			curl_setopt($ch, CURLOPT_HEADER	, TRUE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_COOKIEFILE, './cache/cookie.txt');	// set cookie file
			curl_setopt($ch, CURLOPT_COOKIEJAR, './cache/cookie.txt');
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
			
			$data = curl_exec($ch);	// execute curl request
			curl_close($ch);
			
			return $data;	// return contents
		}
		// otherwise simply read the file
		else
		{
		
			$handle = fopen($url, "r");	// simple read the file
			$filename = $this->dir."/get/".md5(time());
			$cachefile = fopen($filename, "w");
			fwrite($cachefile, $handle);
			fclose($cachefile);
			
			$get = fopen($cachefile, "r");
			$data = fread($get);
			fclose($get);
			
			return $filename;	// output it 
		}
	}


	/**
	 * Cut Function
	 *
	 * The following function extracts the data between 2 tags
	 *
	 * @param	(string) $start The HTML tag to start, $end HTML tag to end, $from the HTML contents
	 * @return	(string) $cut the extracted HTML contents
	 */	
	public function cut($start, $end, $from)
	{
		$cut =  explode($start, $from);		// cut from top
		$cut =  explode($end, @$cut[1]);	// cut from bottom
		$cut =  $cut[0];			// get the cropped part
		
		return $cut;	// output it
	}
	
	
	/**
	 * Cut Multiple Function
	 *
	 * The following function cuts out multiple portions from the HTML content
	 *
	 * @param	(array) $start The HTML tag to start, $end HTML tag to end, $from the HTML contents
	 * @return	(array) $results the extracted HTML contents
	 */	
	public function cutMultiple($start, $end, $from)
	{
		$results = array();	// init the output
		$step1 = explode($start, $from);	// get the results
		
		// start iterring
		for( $i = 1; $i < count($step1); $i++)
		{
			$outputs = explode($end, $step1[$i]);	// get final cut
			$results[] = $outputs[0];	// append the cut
		}
		
		return $results;	// output it
	}
	
	
	/**
	 * Cut Within Content Function (cutCeption)
	 *
	 * The following function cuts out HTML in layered form from previously cut content
	 *
	 * @param	(array) $steps The HTML tag to start and end in key value form, $from the HTML contents
	 * @return	(array) $results the extracted HTML contents
	 */	
	public function cutCeption($steps, $from)
	{
		$count = count($steps);	// count instances
		$start = array_keys($steps);
		$end = array_values($steps);
		
		if( count($start) > 1 )
		{
			$step1 = $this->cutMultiple($start[0], $end[0], $from);
			$step2 = array();
			$i = 1;
			
			// TODO
			while( $i < ($count-1))
			{
				$next = $i+1;
				
				foreach($step1 as $each)
				{
					$step2[] = $this->cutMultiple($start[$next], $end[$next], $each);
				}
				
				foreach($step2 as $next)
				{
					// TODO
				}
				
				$i++;
			}
			
			return $step2;
			
		}
		else
		{
			$results = array();
			
			// iter once
			foreach($steps as $key => $value)
			{
				$results[] = $this->cutMultiple($key, $value, $from);	// append single cut
			}
		
			return $results;	// output cut	
		}
		
	}	

	
	/**
	 * Strip Tags Function
	 *
	 * The following function removes all HTML code from the contents
	 * and leaves the HTML and PHP comments alone
	 *
	 * @param	(string) $html The HTML contents to be stripped, $exceptions HTML tags to be excluded
	 * 			 within quotes without separation
	 * @return	(string) $results The stripped text contents 
	 */
	public function strip($html, $exceptions)
	{
		if( $exceptions == "") { $exceptions = "";}	// set default exclusions
		
		// if not single
		if( is_array($html) )
		{
			$results = array();	// init the results
			// start iterring
			foreach($html as $single)
			{
				// replace php and comments tags so they do not get stripped  			
				$single = preg_replace("@<\?@", "#?#", $single);
				$single = preg_replace("@<!--@", "#!--#", $single);

				// strip tags normally
				# $single = preg_replace("/<[^>]*>/", "", $single);	// take care of double quotes
				$single = strip_tags($single, $exceptions);

				// return php and comments tags to their origial form
				$single = preg_replace("@#\?#@", "<?", $single);
				$single = preg_replace("@#!--#@", "<!--", $single);
				
				$results[] = $single;	// append the results	
			}
			
			return $results; // output multiple
		}
		// otherwise
		else
		{
			// replace php and comments tags so they do not get stripped  
			$html = preg_replace("@<\?@", "#?#", $html);
			$html = preg_replace("@<!--@", "#!--#", $html);

			// strip tags normally
			# $single = preg_replace("/<[^>]*>/", "", $single);	// take care of double quotes
			$html = strip_tags($html, $exceptions);

			// return php and comments tags to their origial form
			$html = preg_replace("@#\?#@", "<?", $html);
			$html = preg_replace("@#!--#@", "<!--", $html);
			
			return $html;	// return stripped single
		}
	}


	/**
	 * Escape Function
	 *
	 * The following function escapes the given HTML
	 *
	 * @param	(string) $html The content to be escaped
	 * @return	(string) Escaped HTML
	 */
	public function escape($html)
	{
		// if multiple strings
		if( is_array($html) )
		{
			$results = array();
			// start ittering
			foreach($html as $entry)
			{
				$results[] = htmlentities($entry);	// append escaped string
			}
			
			return $results;	// output it
		}
		// otherwise
		else
		{
			return htmlentities($html);	// escape it
		}
	}


	/**
	 * Un-Escape Function
	 *
	 * The following function unescapes the HTML content
	 *
	 * @param	(string) $html The escaped HTML contents
	 * @return	(string) Unescaped HTML
	 */	
	public function unescape($html)
	{
		// if multiple strings
		if( is_array($html) )
		{
			$results = array();
			// start ittering
			foreach($html as $entry)
			{
				$results[] = html_entity_decode($entry);	// append unescaped string
			}
			
			return $results;	// output it
		}
		// otherwise
		else
		{
			return html_entity_decode($html);	// unescape it
		}
	}
	

	/**
	 * Get External CSS Function
	 *
	 * The following function gets the URLs of CSS files from a page
	 *
	 * @param	(string) $url The URL of the page to get CSS from
	 * @return	(array) $result The links to the CSS files
	 */	
	public function externalCSS($url)
	{
		$temp = array();
		$results = array();

		// Case 1
		$m1 = preg_match_all('|<link(.*?)css(.*?)ref="(.*?)"(.*?)>|', $this->load($url), $patterns);
		array_push($temp, $patterns[3]);
		
		// Case 2
		$m2 = preg_match_all('|<link(.*?)ref="(.*?)"(.*?)css(.*?)>|', $this->load($url), $patterns);
		array_push($temp, $patterns[2]);
		
		// Case 3
		$m3 = preg_match_all('/(href=")(.*\.css)"/i', $this->load($url), $patterns);
		array_push($temp, $patterns[2]);
		
		// collect all results
		foreach($temp as $result)
		{
			foreach($result as $css)
			{
				$results[] = $css;	// append results
			}
		}
		
		$temp = array_unique($results);	// sort for duplicates
		$results = array();	// init again
		
		// start reading all results
		foreach($temp as $unique)
		{
			$results[] = $this->getRealPath($url, $unique);	// get actual URL
		}
		
		return $results;	// return CSS URLs
	}
	
	
	/**
	 * Get External JS Function
	 *
	 * The following function gets the URLs of JS files from a page
	 *
	 * @param	(string) $url The URL of the page to get JS from
	 * @return	(array) $result The links to the JS files
	 */
	public function externalJS($url)
	{
		$tmp = preg_match_all('/(src=")(.*\.js)"/i', $this->load($url), $patterns);
		$result = array();
		array_push($result, $patterns[2]);
		
		return $result;
	}	

	
	/**
	 * String Replacement Function
	 *
	 * The following function replaces the given text with the replacement text
	 *
	 * @param	(string, array) $what The string/array to be replaced, $with The string/array to 
	 * 			        replace with, $from The HTML contents
	 * @return	(string) The replaced HTML contents
	 */
	public function replace($what, $with, $from)
	{
		if($what  != "")
		{
			return str_replace($what, $with, $from);
		}
	
	}
	
	
	/**
	 * XML Parser Function
	 *
	 * The following function parses then e XML into an object
	 *
	 * @param	(string) $url The URL of the XML content
	 * @return	(array) The parsed XML array
	 */
	public function parseXML($url)
	{
		if(function_exists('simplexml_load_string'))
		{
			return simplexml_load_string($url);
		}
	
	}	

	
	/**
	 * Get URLs Function
	 *
	 * The following function outputs all the links found in the given text
	 *
	 * @param	(string) $text The HTML content to extract links from
	 * @return	(array) $urls The list of URLs found
	 */
	public function getURLs($text)
	{
		$pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
		$match = preg_match_all($pattern, $text, $matches);
		$results = $matches[0];
		
		$urls = array();
		
		foreach($results as $url)
		{
			$urls[] = $url;
		}
		
		return $urls;
	}
	
	
	/**
	 * Get Real Path Function
	 *
	 * The following function outputs all the HTTP URL from an absolute path
	 *
	 * @param	(string) $iurl The HTTP URL of the webpage, $irelative The relative URL of the directory/file
	 * @return	(array) $urls The list of URLs found
	 */
	public function getRealPath($iurl, $irelative)
	{
		// Validate conditions for recursion
		if( $iurl[0] == "" && $irelative[0] = "" )
		{
			$url[0] = $iurl;	// make it an array
			$relative[0] = $irelative;	// make it an array
		}
		// otherwise
		else
		{
			$url = $iurl;	// keep the array
			$relative = $irelative;	// keep the array
		}
		
		// Insert Recursion Here
			// Insert Recursion Here
				// Insert Recursion Here
		if( is_array($url) )
		{
			$results = array();	// init results
			
			for($i=0; $i < count($url); $i++ )
			{
				$results[] = $this->getRealPath($url[$i], $relative[$i]);	// Get relative URL
			}
			
			return $results;	// output result
		}
		
		
		$p = parse_url($relative);	// parse the URL
		
		if( @$p["scheme"] ) { return $relative; }	// check if url is already present
		
		extract( parse_url($url) );	// Get the variables
		$path = dirname($path); 	//  init dir
	
		// If we already have the answer
		if($relative{0} == "/")
		{
			$cparts = array_filter(explode("/", $relative));
		}
		// otherwise
		else 
		{
			// parse relative locations
			$aparts = array_filter(explode("/", $path));
			$rparts = array_filter(explode("/", $relative));
			$cparts = array_merge($aparts, $rparts);
			
			// start making the URL
			foreach($cparts as $i => $part) 
			{
				if($part == ".") 
				{
					$cparts[$i] = null;
				}
				
				if($part == "..") 
				{
					$cparts[$i - 1] = null;
					$cparts[$i] = null;
				}
			}
			
			$cparts = array_filter($cparts);
		}
		
		$path = implode("/", $cparts);
		$url = "";	// init the URL
		
		// output handling
		if($scheme) { $url = "$scheme://"; }	// Get request protocol 

		// output handling		
		if(@$user)  
		{
			$url .= "$user";
			if($pass) { $url .= ":$pass";	 } // if is post/ftp request
			$url .= "@";
		}
		
		// output handling
		if($host) { $url .= "$host/"; }	// domain
		
		$url .= $path;	// make path
		
		return $url;
	}	
	
	
	/**
	 * Generate Cache Function
	 *
	 * The following function generates a cache file and stores it
	 *
	 * @param	(string) $data The data to be cached, $key An unique identifier for the data
	 * @return	(bool) Returns True if data is cached
	 */	
	public function cache($data, $key)
	{
		if ( !is_dir($this->dir) OR !is_writable($this->dir))  
		{  
			return False;  
		}  

		$cache_path = $this->dir."/get/".md5($key);  

		if ( !$fp = fopen($cache_path, 'wb'))  
		{  
			return False;  
		}  

		if (flock($fp, LOCK_EX))  
		{  
			fwrite($fp, serialize($data));  
			flock($fp, LOCK_UN);  
		}  
		else  
		{  
			return False;  
		}  

		fclose($fp);  
		@chmod($cache_path, 0777);  

		return True;  
		
	}
	
	
	/**
	 * Get Cache Function
	 *
	 * The following function gets the cached content
	 *
	 * @param	(string) $key The unique key for the saved data
	 * @return	(string) The cached content
	 */
	public function getCache($key)
	{
		if ( !is_dir($this->dir) OR !is_writable($this->dir))  
		{  
			return False;  
		}  

		$cache_path = $this->dir."/get/".md5($key);  

		if (!@file_exists($cache_path))  
		{  
			return False;  
		}  

		if (filemtime($cache_path) < (time() - $this->expiration))  
		{  
			
			if (file_exists($cache_path))  
			{  
				unlink($cache_path);  
				
				return True;  
			}  
			
			return False;  
		}  

		if (!$fp = @fopen($cache_path, 'rb'))  
		{  
			return False;  
		}  

		flock($fp, LOCK_SH);  

		$cache = '';  

		if (filesize($cache_path) > 0)  
		{  
			$cache = unserialize(fread($fp, filesize($cache_path)));  
		}  
		else  
		{  
			$cache = NULL;  
		}  

		flock($fp, LOCK_UN);  
		fclose($fp);  

		return $cache;  	
	}
	
	
}	// end class





?>