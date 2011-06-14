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

	public $dir = "./cache/";	// cache directory with trailing slash
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
			ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/11.0.696 Safari/525.13");	// set user agent
			return file_get_contents($url);	// return the contents
		}
		// otherwise use curl
		elseif ( function_exists("curl_init") )
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);	// get the url contents
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/11.0.696 Safari/525.13");	// set user agent
			curl_setopt($ch, CURLOPT_HEADER	, TRUE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->dir."cookie.txt");	// set cookie file
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->dir."cookie.txt");
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
			
			$data = curl_exec($ch);	// execute curl request
			curl_close($ch);
			
			return $data;	// return contents
		}
		// otherwise simply read the file
		else
		{		
			$handle = fopen($url, "r");	// simple read the file
			$filename = $this->dir."/get/".md5(time());	// set cache filename
			$cachefile = fopen($filename, "w");	// open it
			fwrite($cachefile, $handle);	// cache it
			fclose($cachefile);
			
			$get = fopen($cachefile, "r");	// retrieve it
			$data = fread($get);	// load it
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
	public function strip($html, $exceptions=NULL)
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
		$get = $this->load($url);
		
		// Case 1
		$m1 = preg_match_all('|<link(.*?)css(.*?)ref="(.*?)"(.*?)>|', $get, $patterns);
		@array_push($temp, $patterns[3]);
		
		// Case 2
		$m2 = preg_match_all('|<link(.*?)ref="(.*?)"(.*?)css(.*?)>|', $get, $patterns);
		@array_push($temp, $patterns[2]);
		
		// Case 3
		$m3 = preg_match_all('/(href=")(.*\.css)"/i', $get, $patterns);
		@array_push($temp, $patterns[2]);
		
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
		$temp = array();
		$results = array();
		$get = $this->load($url);

		// Case 1
		$m1 = preg_match_all('/(src=")(.*\.js)"/i', $get, $patterns);
		@array_push($result, $patterns[2]);
		
		// Case 2
		$m1 = preg_match_all('|<script(.*?)javascript(.*?)rc="(.*?)"(.*?)>|', $get, $patterns);
		@array_push($temp, $patterns[3]);
		
		// Case 3
		$m2 = preg_match_all('|<script(.*?)rc="(.*?)"(.*?)javascript(.*?)>|', $get, $patterns);
		@array_push($temp, $patterns[2]);
		
		// collect all results
		foreach($temp as $result)
		{
			foreach($result as $js)
			{
				$results[] = $js;	// append results
			}
		}
		
		$temp = array_unique($results);	// sort for duplicates
		$results = array();	// init again
		
		// start reading all results
		foreach($temp as $unique)
		{
			$results[] = $this->getRealPath($url, $unique);	// get actual URL
		}
		
		return $results;	// return JS URLs
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
	 * JSON Parser Function
	 *
	 * The following function parses the given JSON into an object
	 *
	 * @param	(string) $data The URL of the JSON content
	 * @return	(array) The parsed JSON array
	 */
	public function parseJSON($data)
	{
		// if input is a URL
		if( $this->isURL($data) )
		{
			$data = $this->load($data);	// make it JSON
		}
		
		$get = preg_replace( "/,\s*([\]}])/m", "$1", utf8_encode($data) );	// minor error handling
		
		if(function_exists('json_decode'))
		{	
			return json_decode($get);	// output it
		}
	
	}	
	
	
	/**
	 * XML Parser Function
	 *
	 * The following function parses the given XML into an object
	 *
	 * @param	(string) $url/$xml The URL of the XML content
	 * @return	(array) The parsed XML array
	 */
	public function parseXML($url)
	{
		// if input is a URL
		if( $this->isURL($url) )
		{
			$url = $this->load($url);	// make it XML
		}
		
		if(function_exists('simplexml_load_string'))
		{
			$xml = simplexml_load_string($url);	// Parse it
			
			return $xml;	// output it
		}
	
	}	
	

	/**
	 * XML X-Path Browser Function
	 *
	 * The following interprets the X-Path for the parsed XML file
	 *
	 * @param	(string) $path The xpath, $xml The URL of the XML file or the XML content 
	 * @return	(array) The xpath array results
	 */
	public function xpath($path, $xml)
	{
		// if input is a URL
		if( $this->isURL($xml) )
		{
			$xml = $this->load($xml);	// make it XML
		}
		
		$get = $this->parseXML($xml);	// parse xml
		
		if( $path != "" )
		{
			$result = $get->xpath($path);	// parse xpath
			
			return $result;	// output xpath results
		}
	}

	/**
	 * Submit POST Request Function
	 *
	 * The following submits parameters to a URL using POST method
	 *
	 * @param	(array, string) $param The parameters to submit in key-value form, $url The URL to submit to
	 * @return	(string) $data Returns the content of the page after submiting the params
	 */
	public function submitPOST($param, $url)
	{
		$query = http_build_query($param);	// make the query

		// Start posting
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);	// get the url contents
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/11.0.696 Safari/525.13");	// set user agent
		curl_setopt($ch, CURLOPT_HEADER	, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch,CURLOPT_POST, count($param));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $query);	// post data
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->dir."cookie.txt");	// set cookie file
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->dir."cookie.txt");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
		
		$data = curl_exec($ch);	// execute curl request
		curl_close($ch);		
		
		return $data;	// returns the HTML after posting
	}
	

	/**
	 * Submit GET Request Function
	 *
	 * The following submits parameters to a URL using GET method
	 *
	 * @param	(array, string) $param The parameters to submit in key-value form, $url The URL to submit to
	 * @return	(string) $data Returns the content of the page after submiting the params
	 */
	public function submitGET($param, $url)
	{
		$query = $url."?".http_build_query($param);	// Build the URL
		$data = $this->load($query);	// get contents
		
		return $data;	// output them
	}
	
	
	/**
	 * URL Validator Function
	 *
	 * The following function checks if the given string is a url or not
	 *
	 * @param	(string) $text The URL or any other string
	 * @return	(bool) Returns true if its a url
	 */
	public function isURL($text)
	{
		$check = parse_url($text);	// parse the given text
		
		// if its a URL
		if( (@$check["scheme"] == "http"|| @$check["scheme"] == "https") && @$check["host"] != "" )
		{
			return True;	// say yes
		} 
		
		return False;
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
		$pattern  = "#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#";	// RegEx for URLs
		$match = preg_match_all($pattern, $text, $matches);	// find matched
		$results = $matches[0];	// retrieve em
		
		$urls = array();	// init results
		
		// start iterring
		foreach($results as $url)
		{
			$urls[] = $url;	// append results
		}
		
		return $urls;	// output it
	}
	
	
	/**
	 * Get All Redirects Function
	 *
	 * The following function outputs all (if any) HTTP redirects that the given url forwards to
	 *
	 * @param	(string) $url The URL of the page 
	 * @return	(array) $urls The list of URLs found
	 */
	public function getRedirects($url)
	{
		$urlParts = @parse_url($url);	// parse url
	
		if (!$urlParts) { $redirected =  ""; }	// make sure its a URL
		if (!isset($urlParts["host"])) { $redirected = ""; }	// can't process relative URLs
		if (!isset($urlParts["path"])) { $urlParts["path"] = '/'; }	// set whatever you got!

		$sock = fsockopen($urlParts["host"], ( isset($urlParts["port"]) ? (int)$urlParts["port"] : 80), $errno, $errstr, 30);	// open it up old style
		
		if (!$sock) { $redirected =  ""; }	// if cant open URL, die
		
		// Request for headers
		$request = "HEAD " . $urlParts["path"] . (isset($urlParts["query"]) ? '?'.$urlParts["query"] : '') . " HTTP/1.1\r\n"; 
		$request .= "Host: " . $urlParts["host"] . "\r\n"; 
		$request .= "Connection: Close\r\n\r\n"; 
		
		// Init response
		fwrite($sock, $request);
		$response = "";
		
		// Start iterations 
		while( !feof($sock) ) 
		{
			$response .= fread($sock, 8192);	// add to response
		}
		
		fclose($sock);	// close connection

		// Start checking for redirects
		if ( preg_match('/^Location: (.+?)$/m', $response, $matches) )
		{
			if ( substr($matches[1], 0, 1) == "/" )
			{
				$redirected = $urlParts['scheme'] . "://" . $urlParts['host'] . trim($matches[1]);	// Make URL
			}
			else
			{
				$redirected = trim($matches[1]);	// Otherwise clean up
			}
		} 
		else 
		{
			$redirected = "";	// die
		}

		// If there was a redirect
		if( !empty($redirected) )
		{
			$urls = array();	// init result
			
			// Start checking
			while ($newurl = $redirected)
			{
				if ( in_array($newurl, $urls))	// unless it already exists
				{
					break;	// do nothing
				}
				
				$urls[] = $newurl;	// add it
				$url = $newurl;	// itter to next check
			}

			return $urls;	// return results
		}
		
		return array("$url");	// otherwsie return the given url
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
		
		return $url;	// output url
	}	


	/**
	 * HTTP Header Status Function
	 *
	 * The following function sets the HTTP headers to follow the defined status code
	 *
	 * @param	(string) $code The HTTP code to follow, $url The URL to redirect to
	 * @return	(void) Sets the headers
	 */	
	public function HTTPStatus($code, $url=NULL)
	{
		// Status codes
		static $http = array (
			       100 => "HTTP/1.1 100 Continue",
			       101 => "HTTP/1.1 101 Switching Protocols",
			       200 => "HTTP/1.1 200 OK",
			       201 => "HTTP/1.1 201 Created",
			       202 => "HTTP/1.1 202 Accepted",
			       203 => "HTTP/1.1 203 Non-Authoritative Information",
			       204 => "HTTP/1.1 204 No Content",
			       205 => "HTTP/1.1 205 Reset Content",
			       206 => "HTTP/1.1 206 Partial Content",
			       300 => "HTTP/1.1 300 Multiple Choices",
			       301 => "HTTP/1.1 301 Moved Permanently",
			       302 => "HTTP/1.1 302 Found",
			       303 => "HTTP/1.1 303 See Other",
			       304 => "HTTP/1.1 304 Not Modified",
			       305 => "HTTP/1.1 305 Use Proxy",
			       307 => "HTTP/1.1 307 Temporary Redirect",
			       400 => "HTTP/1.1 400 Bad Request",
			       401 => "HTTP/1.1 401 Unauthorized",
			       402 => "HTTP/1.1 402 Payment Required",
			       403 => "HTTP/1.1 403 Forbidden",
			       404 => "HTTP/1.1 404 Not Found",
			       405 => "HTTP/1.1 405 Method Not Allowed",
			       406 => "HTTP/1.1 406 Not Acceptable",
			       407 => "HTTP/1.1 407 Proxy Authentication Required",
			       408 => "HTTP/1.1 408 Request Time-out",
			       409 => "HTTP/1.1 409 Conflict",
			       410 => "HTTP/1.1 410 Gone",
			       411 => "HTTP/1.1 411 Length Required",
			       412 => "HTTP/1.1 412 Precondition Failed",
			       413 => "HTTP/1.1 413 Request Entity Too Large",
			       414 => "HTTP/1.1 414 Request-URI Too Large",
			       415 => "HTTP/1.1 415 Unsupported Media Type",
			       416 => "HTTP/1.1 416 Requested range not satisfiable",
			       417 => "HTTP/1.1 417 Expectation Failed",
			       500 => "HTTP/1.1 500 Internal Server Error",
			       501 => "HTTP/1.1 501 Not Implemented",
			       502 => "HTTP/1.1 502 Bad Gateway",
			       503 => "HTTP/1.1 503 Service Unavailable",
			       504 => "HTTP/1.1 504 Gateway Time-out"
					);

		header( $http[$code] ) ;	// status code
	   
		// if redirect required
		if($url != "")
		{
			header ( "Location: $url" );	// redirect
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
		// check if the directory is writable
		if ( !is_dir($this->dir) OR !is_writable($this->dir))  
		{  
			return False;  
		}  

		$cache_path = $this->dir."/get/".md5($key);	// set the cache file

		// make sure the file can be open
		if ( !$fp = fopen($cache_path, 'wb'))  
		{  
			return False;  
		}  

		// lock the file's positions
		if (flock($fp, LOCK_EX))  
		{  
			fwrite($fp, serialize($data));  // cache it
			flock($fp, LOCK_UN);  
		}  
		else  
		{  
			return False;  
		}  

		fclose($fp);  
		@chmod($cache_path, 0777);	// give it permissions

		return True;  // return the boolean output
		
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
		// check if the directory is writable or not
		if ( !is_dir($this->dir) OR !is_writable($this->dir))  
		{  
			return False;  
		}  

		$cache_path = $this->dir."/get/".md5($key);	// find the file

		// if it doesnt exists, do nothing
		if (!@file_exists($cache_path))  
		{  
			return False;  
		}  

		// if the file has expired
		if (filemtime($cache_path) < (time() - $this->expiration))  
		{  
			// and exists
			if (file_exists($cache_path))  
			{  
				unlink($cache_path);	// delete it
				
				return True;  
			}  
			
			return False;	
		}  

		// make sure it can be open
		if (!$fp = @fopen($cache_path, 'rb'))  
		{  
			return False;  
		}  

		flock($fp, LOCK_SH);	// lock it

		$cache = '';	// init cache request

		if (filesize($cache_path) > 0)	// if file is valid
		{  
			$cache = unserialize(fread($fp, filesize($cache_path)));	// retrieve it
		}  
		else  
		{  
			$cache = NULL;	// do nothing
		}  

		flock($fp, LOCK_UN);
		fclose($fp);  

		return $cache;	// return the contents
	}
	
	
}	// end class





?>