<?php



$scraper = new Scraper;


$link              = "http://www.bulletin.uwaterloo.ca/";
$load             = $scraper->load($link);
$cut               = $scraper->cut('<h3>When and where</h3>', '</div>', $load);
$strip             = $scraper->strip($cut);
$escape         = $scraper->escape($cut);
$unescape     = $scraper->unescape($escape);
$externalcss   = $scraper->externalcss($link);
$externaljs     = $scraper->externaljs($link);






print_r($externalcss);



?>