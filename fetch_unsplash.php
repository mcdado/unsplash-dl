<?php

function getRedirectUrl ($url) {
    stream_context_set_default(array(
        'http' => array(
            'method' => 'HEAD'
        )
    ));
    $headers = get_headers($url, 1);
    if ($headers !== false && isset($headers['Location'])) {
        return $headers['Location'];
    }
    return false;
}

$shortlinks = array();

$r = new HttpRequest('http://unsplash.com/api/read', HttpRequest::METH_GET);
//$r->setOptions(array('lastmodified' => filemtime('local.rss')));
//$r->addQueryData(array('type' => 'photo', 'num' => 50));
$r->addQueryData(array('type' => 'photo'));

try {
    $r->send();
    if ($r->getResponseCode() == 200) {
        // file_put_contents('local.rss', $r->getResponseBody());
		$feed = simplexml_load_string($r->getResponseBody());
		//$feed = new SimpleXMLElement($r->getResponseBody());
		
		foreach ( $feed->posts->post as $entry ) {
			$extracted_link = (string)$entry->{'photo-link-url'};
			$redirection = getRedirectUrl($extracted_link);
			$clean_link = $redirection ? $redirection : $extracted_link;
			$shortlinks[] = $clean_link;
		}
    } else {
    	echo $r->getResponseCode();
    }
} catch (HttpException $ex) {
    echo $ex;
}

//print_r($links);

$redirected = array();

foreach ( $shortlinks as $link ) {
	$parsed_link = parse_url($link);
	$file_name = basename($parsed_link['path']);
	
	try {
		$connection = fopen($link, 'rb');
		
	} catch (Exception $ex){
		echo $ex;
	}
	
	if ( $connection ) {
		fopen($file_name, $connection);
		echo "File " . $file_name . " saved", "\n";
	}
}


?>