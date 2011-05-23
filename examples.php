<?php


require("ScraperClass.php");


$link = "http://www.bulletin.uwaterloo.ca/";
$cutarray = array('<div id="footer">' => '</body>', 'departmentaddress' => '</div>', '<p>' => '</p>');


$scraper = new Scraper;


$load = $scraper->load($link);

$cut = $scraper->cut('<h3>When and where</h3>', '</div>', $load);
$cutMultiple = $scraper->cutMultiple('<p>', '</p>', $load);
//$cutCeption = $scraper->cutCeption($cutarray, $load);

$strip = $scraper->strip($cut, "<pre>");

$escape = $scraper->escape($cut);

$unescape = $scraper->unescape($escape);

$externalcss = $scraper->externalcss($link);

$externaljs = $scraper->externaljs($link);

$replace = $scraper->replace("pm", "PM", $load);

$cache = $scraper->cache($load, "blahblah123");
$getcache = $scraper->getcache("blahblah123");



print_r($load);



?>