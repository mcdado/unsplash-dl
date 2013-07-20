<?php

class UnsplashFetch {
    
    public $location;
    
    public $feed;
    
    function __construct($url, $path) {
        $this->feed = $url;
        $this->location = $path;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }
    
    public function init() {
        $r = new HttpRequest($this->feed, HttpRequest::METH_GET);
        
        if ( file_exists( $this->location . '/feed.rss' ) )
            $r->setOptions(array('lastmodified' => filemtime($this->location . '/feed.rss')));
        
        $r->addHeaders(array('User-Agent' => 'unsplash-dl'));
        $r->addQueryData(array('type' => 'photo'));
        
        try {
            $r->send();
            
            if ($r->getResponseCode() == 200) {
                file_put_contents($this->location . '/feed.rss', $r->getResponseBody());
        		$body = simplexml_load_string($r->getResponseBody());
                
        		foreach ( $body->posts->post as $entry ) {
        			$extracted_link = (string)$entry->{'photo-link-url'};
        			$redirection = $this->getRedirectUrl($extracted_link);
        			$clean_link = $redirection ? $redirection : $extracted_link;
                    $this->fetchLink($clean_link);
        		}
            } else {
            	echo $r->getResponseCode() . "\n";
            }
        } catch (HttpException $ex) {
            echo $ex . "\n";
        }
        
    }
    
    private function getRedirectUrl($url) {
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
    
    private function fetchLink($link) {
    	$parsed_link = parse_url($link);
    	$file_name = basename($parsed_link['path']);
    	$final_link = str_replace(" ", "%20", $link);
        if ( ! file_exists ($this->location . "/" . $file_name) ) {
            echo "Fetching " . $final_link . "\n";
            $r = new HttpRequest($final_link, HttpRequest::METH_GET);
            $r->addHeaders(array('User-Agent' => 'unsplash-dl'));
            try {
                $r->send();
                if ($r->getResponseCode() == 200) {
                    file_put_contents($this->location . "/" . $file_name, $r->getResponseBody());
                } else {
                	echo "Problem: " . $r->getResponseCode() . "\n";
                }
            } catch (HttpException $ex) {
                echo $ex . "\n";
            }
        }
    }
}

$unsplash = new UnsplashFetch('http://unsplash.com/api/read', '/Users/' . get_current_user() . '/Pictures/Unsplash');
$unsplash->init();

?>