#!/usr/local/bin/php

<?php

class UnsplashFetch {
    
    private $location,
            $feed,
            $logging,
            $log_file,
            $log_handle,
            $client,
            $downloads;
                
    function __construct($url, $picture_path, $log = false, $log_path = "") {
        $this->feed = $url;
        $this->logging = $log;
        $this->location = $picture_path;
        if ( $log_path == "") {
            $this->log_file = "";
        } else {
            $this->log_file = $log_path;
            $this->log_handle = fopen($this->log_file, 'a') or die('Cannot open log file');
        }
        
        $this->client = new http\Client;
        
        if (!file_exists($picture_path)) {
            mkdir($picture_path, 0777, true);
        }
        
        
    }
    
    function terminate() {
        
        $this->sendLog("Unsplashed.");
        
        if ( $this->log_handle )
            fclose($this->log_handle);
        
    }
    
    public function init() {
        
        $this->sendLog("Unsplash-dl started.");
        
        $request = new http\Client\Request("GET", $this->feed, array("User-Agent" => "unsplash-dl (https://github.com/mcdado/unsplash-dl)"));
        $request->addQuery(new http\QueryString("type=photo"));

        if ( file_exists($this->location . '/unsplash.rss') ) {
            $request->setHeader('If-Modified-Since', filemtime($this->location . '/unsplash.rss'));
        }

        try {
            $this->client->enqueue($request)->send();
            $response = $this->client->getResponse($request);
            
            if ($response->getResponseCode() == 200) {
                $body = $response->getBody();
                file_put_contents($this->location . '/unsplash.rss', $body);
                $parsed_body = simplexml_load_string($body);
                unset($body);
                
                foreach ( $parsed_body->posts->post as $entry ) {
                    $extracted_link = "";
                    $extracted_link = (string)$entry->{'photo-link-url'};
                    if ( $extracted_link != "" ) {
                        $redirection = $this->getRedirectUrl($extracted_link);
                        $clean_link = $redirection ? $redirection : $extracted_link;
                        $this->downloads[] = $clean_link;
                    }
                }
                unset($parsed_body);
            } else {
                $this->sendLog("Feed Response Code: " . $response->getResponseCode() );
            }
        } catch (http\Exception $ex) {
            $this->sendLog("Feed Raised Exception: " . $ex);
        }
        
        $this->sendLog("Beginning to fetch links.");
        $this->fetchLinks();
    }

    private function sendLog($message) {
        if ( $this->logging === true ) {
            if ( $this->log_file == "" ) {
                echo $message . "\n";
            } else {
                fwrite($this->log_handle, "[" . date('Y-m-d H:i:s') . "]" . " " . $message . "\n");
            }
        }
    }
    
    private function getRedirectUrl($url) {
        $this->sendLog("Getting redirected for " . $url);
        stream_context_set_default(array(
            'http' => array(
                'method' => 'HEAD'
            )
        ));
        try {
            $headers = get_headers($url, 1);
            if ($headers !== false && isset($headers['Location'])) {
                $this->sendLog("`-> Redirected to " . $headers['Location']);
                return $headers['Location'];
            }
        } catch (Exception $ex) {
            $this->sendLog("Couldn't get redirected URL.");
        }
        $this->sendLog("`-> No redirection.");
        return false;
    }

    private function fetchLinks() {
        
        foreach ($this->downloads as $k => $link ) {
            // Normalizing the URL early
            $this->downloads[$k] = str_replace(" ", "%20", $link);

            $parsed_link = parse_url($link);
            if ( $parsed_link == false ) {
                unset($this->downloads[$k]);
                break;
            }

            $file_name = basename($parsed_link['path']);
            if ( file_exists($this->location . "/" . $file_name) ) {
                unset($this->downloads[$k]);
            }
        }

        foreach ($this->downloads as $d_link) {
            $d_request = new http\Client\Request("GET", $d_link);
            try {
                $this->client->enqueue($d_request)->send();
                $d_name = basename($d_request->getRequestUrl());
                $d_response = $this->client->getResponse();
                if ($d_response->getResponseCode() == 200) {
                    file_put_contents($this->location . "/" . $d_name, $d_response->getBody());
                    $this->sendLog("Succesfully downloaded " . $d_name );

                } else {
                    $this->sendLog( $file_name . " reported a status code: " . $d_response->getResponseCode() );

                }
            } catch (http\Exception $ex) {
                $this->sendLog("Raised Exception: " . $ex);
            }
        }
    }
}

$unsplash = new UnsplashFetch(  'http://unsplash.com/api/read',
                                getenv("HOME") . '/Pictures/Unsplash',
                                true,
                                getenv("HOME") . '/Library/Logs/com.mcdado.unsplash.log');
$unsplash->init();
$unsplash->terminate();

?>
