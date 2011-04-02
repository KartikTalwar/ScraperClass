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

$externaljs = $scraper->replace("pm", "PM", $load);






print_r($replace);



?>