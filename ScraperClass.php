<?php



/**
 * The Scraper Class with helpful functions that are essential to data 
 * mining and screen scraping.
 *
 * @author     	Kartik Talwar
 * @version    	1.0
 * @example		./examples.php
 * @link				http://github.com/kartiktalwar/PHP-Scraper-Class
 */
class Scraper
{

	public $dir = "../cache";
	public $expiration = 3600;
	
	
	/**
	 * Constructor
	 *
	 * The following function does nothing at the moment
	 *
	 * @param	(none) NONE
	 * @return		(none) NONE
	 */
	function __construct()
	{
		return True;
	}

	
	/**
	 * Load Function
	 *
	 * The following function gets the contents of the webpage
	 *
	 * @param	(string) $url The URL of the page to load
	 * @return		(string) $data The contents of the URL
	 */
	public function load($url)
	{
		if(function_exists('file_get_contents'))
		{
			return file_get_contents("$url");
		}
		else
		{
			if( !function_exists('file_get_contents') && function_exists('curl_init'))
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_URL, $g);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/11.0.696 Safari/525.13');
				curl_setopt($ch, CURLOPT_HEADER	, TRUE);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_COOKIEFILE, './cache/cookie.txt');
				curl_setopt($ch, CURLOPT_COOKIEJAR, './cache/cookie.txt');
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
				
				$data = curl_exec($ch);
				curl_close($ch);
				
				return $data;
			}
		}
	}


	/**
	 * Cut
	 *
	 * The following function extracts the data between 2 tags
	 *
	 * @param	(string) $start The HTML tag to start, $end HTML tag to end, $from the HTML contents
	 * @return		(string) $cut the extracted HTML contents
	 */	
	public function cut($start, $end, $from)
	{
		$cut =  explode($start, $from);
		$cut =  explode($end, @$cut[1]);
		$cut = $cut[0];
		
		return $cut;
	}


	/**
	 * Strip Tags Function
	 *
	 * The following function removes all HTML code from the contents
	 *
	 * @param	(string) $html The HTML contents to be stripped
	 * @return		(string) $results The stripped text contents 
	 */
	public function strip($html)
	{
		if( count($html) > 1  )
		{
			$results = array();
			foreach($html as $single)
			{
				$results[] = strip_tags($single);
			}
			
			return $results;
		}
		else
		{
			return strip_tags($html);
		}
	}


	/**
	 * Escape Function
	 *
	 * The following function escapes the given HTML
	 *
	 * @param	(string) $html The content to be escaped
	 * @return		(string) Escaped HTML
	 */
	public function escape($html)
	{
		if( count($html) >1 )
		{
			
			foreach($html as $entry)
			{
				return addslashes(htmlspecialchars($entry));
			}

		}
		else
		{
			return addslashes(htmlspecialchars($html));			
		}
	}


	/**
	 * Un-Escape Function
	 *
	 * The following function unescapes the HTML content
	 *
	 * @param	(string) $html The escaped HTML contents
	 * @return		(string) Unescaped HTML
	 */	
	public function unescape($html)
	{
		if( count($html) >1 )
		{
			
			foreach($html as $entry)
			{
				return stripslashes(htmlspecialchars_decode($entry));
			}

		}
		else
		{
			return stripslashes(htmlspecialchars_decode($html));
		}
		
	}
	

	/**
	 * Get External CSS Function
	 *
	 * The following function gets the URLs of CSS files from a page
	 *
	 * @param	(string) $url The URL of the page to get CSS from
	 * @return		(array) $result The links to the CSS files
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
	 * @return		(array) $result The links to the JS files
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
	 * @param	(string, array) $what The string/array to be replaced, $with The string/array to replace with, $from The HTML contents
	 * @return		(string) The replaced HTML contents
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
	 * @return		(array) The parsed XML array
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
	 * @return		(bool) Returns True if data is cached
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
	 * @return		(string) The cached content
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
	 * @return		(array) $urls The list of URLS found
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