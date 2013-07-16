<?php

if ()
$r = new HttpRequest('http://unsplash.com/api/read', HttpRequest::METH_GET);
$r->setOptions(array('lastmodified' => filemtime('local.rss')));
$r->addQueryData(array('type' => 'photo', 'num' => 50));
try {
    $r->send();
    if ($r->getResponseCode() == 200) {
        // file_put_contents('local.rss', $r->getResponseBody());
		$feed = simplexml_load_string($r->getResponseBody());
		//$feed = new SimpleXMLElement($r->getResponseBody());
		
		$links = array();
		foreach ( $feed->posts->post as $entry ) {
			$links[] = (string)$entry->{'photo-link-url'};
		}
    }
} catch (HttpException $ex) {
    echo $ex;
}

print_r($links);


?>