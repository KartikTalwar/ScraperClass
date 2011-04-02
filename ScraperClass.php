<?php

error_reporting(E_ALL);


class Scraper
{

	
	public $dir = "./cache";
	public $expiration = 3600;
	
	
	
	/*
	Constructor
	*/
	function __construct()
	{
		return True;
	}
	/*
	*/	

	
	
	
	
	
	

	
	/*
	Load Function
	@param: URL to get the contents of
	@output: Returns variable with the HTML content of the webpage
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
	/*
	#########################
	######   End Function  #####
	#########################
	*/	

	
	
	
	
	
	

	/*
	#########################
	#####   Begin Function  #####
	#########################
	*/	
	public function cut($start, $end, $from)
	{
		$cut =  explode($start, $from);
		$cut =  explode($end, $cut[1]);
		$cut = $cut[0];
		
		return $cut;
	}
	/*
	#########################
	######   End Function  #####
	#########################
	*/

	
	
	
	
	
	

	/*
	#########################
	#####   Begin Function  #####
	#########################
	*/	
	public function strip($html)
	{
		return strip_tags($html);
	}
	/*
	#########################
	######   End Function  #####
	#########################
	*/

	
	
	
	
	
	

	/*
	#########################
	#####   Begin Function  #####
	#########################
	*/	
	public function escape($html)
	{
		return addslashes(htmlspecialchars($html));
	}
	/*
	#########################
	######   End Function  #####
	#########################
	*/

	
	
	
	
	
	
	/*
	#########################
	#####   Begin Function  #####
	#########################
	*/
	public function unescape($html)
	{
		return stripslashes(htmlspecialchars_decode($html));
	}
	/*
	#########################
	######   End Function  #####
	#########################
	*/

	
	
	
	
	
	
	/*
	#########################
	#####   Begin Function  #####
	#########################
	*/	
	public function externalcss($url)
	{
		$tmp = preg_match_all('/(href=")(.*\.css)"/i', $this->load($url), $patterns);
		$result = array();
		array_push($result, $patterns[2]);
		
		return $result;
	}
	/*
	#########################
	######   End Function  #####
	#########################
	*/		

	
	
	
	
	
	
	/*
	#########################
	#####   Begin Function  #####
	#########################
	*/
	public function externaljs($url)
	{
		$tmp = preg_match_all('/(src=")(.*\.js)"/i', $this->load($url), $patterns);
		$result = array();
		array_push($result, $patterns[2]);
		
		return $result;
	}	
	/*
	#########################
	######   End Function  #####
	#########################
	*/	
	
	
	/*
	Replace content
	@param: What to replace, What to replace it with, Where the content is
	@output: returns the string with the replacements
	*/
	public function replace($what, $with, $from)
	{
		if($what  != "" && $with != "")
		{
			return str_replace($what, $with, $from);
		}
	
	}
	
	
	
	
	
	/*
	Caches content
	@param: Data to be cached, name of the cache file
	@output: none
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
	


	/*
	Retrieves the cache file
	@param: name of cache file to be retrieved
	@output: cache file content
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
	
	
	
}





?>