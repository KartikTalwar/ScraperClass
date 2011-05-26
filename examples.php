<?php


require("ScraperClass.php");


$link = "http://www.kontain.com/";
//$link = "http://www.bulletin.uwaterloo.ca/";
$cutarray = array('<div id="footer">' => '</body>', 'departmentaddress' => '</div>', '<p>' => '</p>');
$params = array("name1" => "val1", "name2" => "val2", "name3" => "val3" );


$scraper = new Scraper;


$load = $scraper->load($link);

$cut = $scraper->cut('<h3>When and where</h3>', '</div>', $load);
$cutMultiple = $scraper->cutMultiple('<p>', '</p>', $load);
//$cutCeption = $scraper->cutCeption($cutarray, $load);

$strip = $scraper->strip($cut, "<pre>");

$escape = $scraper->escape($cut);

$unescape = $scraper->unescape($escape);

$externalcss = $scraper->externalCSS($link);

$externaljs = $scraper->externalJS($link);

$replace = $scraper->replace("pm", "PM", $load);

//$cache = $scraper->cache($load, "blahblah123");
//$getcache = $scraper->getCache("blahblah123");

$getrealpath = $scraper->getRealPath(array("$link"), array("../images/"));

$redirect = $scraper->HTTPCode(200);
//$redirect = $scraper->HTTPCode(302, "http://www.google.com/ncr");

$parsejson = $scraper->parseJSON("http://www.reddit.com/.json");

$parsexml = $scraper->parseXML("http://www.yoursite.com/sitemap.xml");

$submitpost = $scraper->submitPOST($params, "http://www.yoursite.com/");
$submitget = $scraper->submitGET($params, "http://www.yoursite.com/");

$xpath = $scraper->xpath("/*/*", "http://www.yoursite.com/sitemap.xml");

print_r($parsejson);



?>