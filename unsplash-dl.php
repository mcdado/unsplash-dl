#!/usr/local/bin/php

<?php

class UnsplashFetch {
    
    private $version,
            $user_agent,
            $location,
            $feed,
            $logging,
            $log_file,
            $log_handle,
            $client,
            $downloads;
    
    function __construct($url, $picture_path, $log = false, $log_path = null) {
        
        $this->version = '0.2';
        $this->user_agent = 'UnsplashFetch/' . $this->version . ' (https://github.com/mcdado/unsplash-dl)';
        $this->client = new http\Client;
        $this->feed = $url;
        $this->logging = $log;
        $this->location = $picture_path;
        
        if (!file_exists($picture_path)) {
            mkdir($picture_path, 0777, true);
        }
        
        if ($log_path == null) {
            $this->log_file = null;
        } else {
            $this->log_file = $log_path;
            $this->log_handle = fopen($this->log_file, 'a') or die("Cannot open log file");
        }
        
    }
    
    function terminate() {
        
        $this->sendLog("Unsplashed.");
        
        if ($this->log_handle) {
            fclose($this->log_handle);
        }
        
    }

    private function sendLog($message) {
        if ($this->logging === true) {
            if ($this->log_file == null) {
                echo $message . PHP_EOL;
            } else {
                fwrite($this->log_handle, '[' . date('Y-m-d H:i:s') . ']' . ' ' . $message . PHP_EOL);
            }
        }
    }
    
    public function init() {
        
        $this->sendLog("Unsplash-dl started.");
        
        $request = new http\Client\Request('GET', $this->feed, array('User-Agent' => $this->user_agent));
        $request->addQuery(new http\QueryString("type=photo"));
        
        if ( file_exists($this->location . '/unsplash.rss') ) {
            $mod_date = filemtime($this->location . '/unsplash.rss');
            $request->setHeader('If-Modified-Since', gmdate('D, d M Y H:i:s \G\M\T', $mod_date)); // RFC 2616 formatted date
        }
        
        try {
            $this->client->enqueue($request)->send();
            $response = $this->client->getResponse($request);
            if ($response->getResponseCode() == 200) {
                $body = $response->getBody();
                file_put_contents($this->location . '/unsplash.rss', $body);
                $parsed_body = simplexml_load_string($body);
                unset($body);
                
                foreach ($parsed_body->posts->post as $entry) {
                    $extracted_link = "";
                    $extracted_link = (string)$entry->{'photo-link-url'};
                    if ($extracted_link != "") {
                        $redirection = $this->getRedirectUrl($extracted_link);
                        $clean_link = $redirection ? $redirection : $extracted_link;
                        $this->downloads[] = $clean_link;
                    }
                }
                unset($parsed_body);
            } else {
                $this->sendLog("Feed Response Code: " . $response->getResponseCode());
                return;
            }
        } catch (http\Exception $ex) {
            $this->sendLog("Feed Raised Exception: " . $ex->message);
            return;
        }
        
        $this->sendLog("Beginning to fetch links.");
        $this->fetchLinks();
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
            $this->sendLog("Couldn't get redirected URL: " . $url);
            return false;
        }
        $this->sendLog("`-> No redirection.");
        return false;
    }

    private function fetchLinks() {
        
        foreach ($this->downloads as $k => $link) {
            // Normalizing the URL, updating it in place.
            $link = str_replace(' ', '%20', $link);
            $this->downloads[$k] = $link;

            $parsed_link = parse_url($link);
            if ($parsed_link == false) {
                unset($this->downloads[$k]);
                break;
            }
            
            // Check if the file already exists
            $file_name = basename($parsed_link['path']);
            if (file_exists($this->location . DIRECTORY_SEPARATOR . $file_name)) {
                unset($this->downloads[$k]);
            }
        }

        foreach ($this->downloads as $d_link) {
            $d_request = new http\Client\Request('GET', $d_link, array('User-Agent' => $this->user_agent));
            try {
                $this->client->enqueue($d_request)->send();
                $d_name = basename($d_request->getRequestUrl());
                $d_response = $this->client->getResponse();
                if ($d_response->getResponseCode() == 200) {
                    file_put_contents($this->location . DIRECTORY_SEPARATOR . $d_name, $d_response->getBody());
                    $this->sendLog("Succesfully downloaded " . $d_name);

                } else {
                    $this->sendLog($file_name . " reported a status code: " . $d_response->getResponseCode());

                }
            } catch (http\Exception $ex) {
                $this->sendLog("Raised Exception: " . $ex->message);
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
