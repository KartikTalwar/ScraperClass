<?php


include "ScraperClass.php";


$link = "http://www.bulletin.uwaterloo.ca/";

$scraper = new Scraper;



$load = $scraper->load($link);

$cut = $scraper->cut('<h3>When and where</h3>', '</div>', $load);

$strip = $scraper->strip($cut);

$escape = $scraper->escape($cut);

$unescape = $scraper->unescape($escape);

$externalcss = $scraper->externalcss($link);

$externaljs = $scraper->externaljs($link);

$replace = $scraper->replace("pm", "PM", $load);

$cache = $scraper->cache($load, "blahblah123");
$getcache = $scraper->getcache("blahblah123");



print_r($replace);



?>