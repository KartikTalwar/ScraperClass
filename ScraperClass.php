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
	public $expiration = 60*60;	// cache expiration time in seconds
	
	
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
		if( extension_loaded("file_get_contents") )
		{
			return file_get_contents($url);	// return the contents
		}
		// otherwise use curl
		elseif ( extension_loaded("curl_init") )
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
			$handle = fopen($url, "r+");	// simple read the file
			
			return $handle;	// output it
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
		$cut =  $cut[0];				// get the cropped part
		
		return $cut;	// output it
	}
	
	
	/**
	 * Cut Multiple Function
	 *
	 * The following function cuts out multiple portions from the HTML content
	 *
	 * @param	(array) $start The HTML tag to start, $end HTML tag to end, $from the HTML contents
	 * @return	(array) $cut the extracted HTML contents
	 */	
	public function cutMultiple($start, $end, $from)
	{
		$count = count($start);	// count instances
		$results = array();	// init the output
		
		// start ittering
		for($i = 0; $i < $count; $i++)
		{
			$results[] = $this->cut($start[$i], $end[$i], $from);	// append the cut
		}
		
		return $results;	// output it
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
		// if not single
		if( is_array($html) )
		{
			$results = array();	// init the results
			// start ittering
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
	public function externalcss($url)
	{
		$tmp = preg_match_all('/(href=")(.*\.css)"/i', $this->load($url), $patterns);
		$result = array();
		array_push($result, $patterns[2]);
		
		return $result;
	}
	
	
	/**
	 * Get External JS Function
	 *
	 * The following function gets the URLs of JS files from a page
	 *
	 * @param	(string) $url The URL of the page to get JS from
	 * @return	(array) $result The links to the JS files
	 */
	public function externaljs($url)
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
			return FALSE;  
		}  

		$cache_path = md5($key);  

		if ( !$fp = fopen($cache_path, 'wb'))  
		{  
			return FALSE;  
		}  

		if (flock($fp, LOCK_EX))  
		{  
			fwrite($fp, serialize($data));  
			flock($fp, LOCK_UN);  
		}  
		else  
		{  
			return FALSE;  
		}  

		fclose($fp);  
		@chmod($cache_path, 0777);  

		return TRUE;  
		
	}
	
	
	/**
	 * Get Cache Function
	 *
	 * The following function gets the cached content
	 *
	 * @param	(string) $key The unique key for the saved data
	 * @return	(string) The cached content
	 */
	public function getcache($key)
	{
		if ( !is_dir($this->dir) OR !is_writable($this->dir))  
		{  
			return FALSE;  
		}  

		$cache_path = md5($key);  

		if (!@file_exists($cache_path))  
		{  
			return FALSE;  
		}  

		if (filemtime($cache_path) < (time() - $this->expiration))  
		{  
			
			if (file_exists($cache_path))  
			{  
				unlink($cache_path);  
				
				return TRUE;  
			}  
			
			return FALSE;  
		}  

		if (!$fp = @fopen($cache_path, 'rb'))  
		{  
			return FALSE;  
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
	
	
	/**
	 * Get URLs Function
	 *
	 * The following function outputs all the links found in the given text
	 *
	 * @param	(string) $text The HTML content to extract links from
	 * @return	(array) $urls The list of URLS found
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

	
}	// end class





?>