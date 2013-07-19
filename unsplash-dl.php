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

function fetchLink ($link) {
	$parsed_link = parse_url($link);
	$file_name = basename($parsed_link['path']);
	$final_link = str_replace(" ", "%20", $link);
    echo "Fetching " . $final_link . "\n";

    $r = new HttpRequest($final_link, HttpRequest::METH_GET);
    $r->addHeaders(array('User-Agent' => 'unsplash-dl'));
    try {
        $r->send();
        if ($r->getResponseCode() == 200) {
            file_put_contents($file_name, $r->getResponseBody());
        } else {
        	echo "Problem: " . $r->getResponseCode() . "\n";
        }
    } catch (HttpException $ex) {
        echo $ex;
    }
}

$shortlinks = array();

$r = new HttpRequest('http://unsplash.com/api/read', HttpRequest::METH_GET);
$r->setOptions(array('lastmodified' => filemtime('local.rss')));
$r->addQueryData(array('type' => 'photo', 'num' => 3));

try {
    $r->send();
    if ($r->getResponseCode() == 200) {
        file_put_contents('local.rss', $r->getResponseBody());
		$feed = simplexml_load_string($r->getResponseBody());
		
		foreach ( $feed->posts->post as $entry ) {
			$extracted_link = (string)$entry->{'photo-link-url'};
			$redirection = getRedirectUrl($extracted_link);
			$clean_link = $redirection ? $redirection : $extracted_link;
			//$shortlinks[] = $clean_link;
            fetchLink($clean_link);
		}
    } else {
    	echo $r->getResponseCode();
    }
} catch (HttpException $ex) {
    echo $ex;
}

s?>