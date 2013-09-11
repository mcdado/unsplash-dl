<?php

class UnsplashFetch {
    
    private $location,
            $feed,
            $log_file,
            $log_handle;
                
    function __construct($url, $path, $log) {
        $this->feed = $url;
        $this->location = $path;
        $this->log_file = $log;
        
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        
        $this->log_handle = fopen($this->log_file, 'a') or die('Cannot open log file:  ' . $this->log_file);
    }
    
    function terminate() {
        
        $this->sendLog("Unsplashed.");
        
        if ( $this->log_handle )
            fclose($this->log_handle);
        
    }
    
    public function init() {
        
        $this->sendLog("Unsplash-dl started.");
        
        $r = new HttpRequest($this->feed, HttpRequest::METH_GET);
        
        if ( file_exists( $this->location . '/unsplash.rss' ) )
            $r->setOptions(array('lastmodified' => filemtime($this->location . '/unsplash.rss')));
        
        $r->addHeaders(array('User-Agent' => 'unsplash-dl'));
        $r->addQueryData(array('type' => 'photo'));
        $r->addQueryData(array('num' => '30'));
        
        try {
            $r->send();
            
            if ($r->getResponseCode() == 200) {
                file_put_contents($this->location . '/unsplash.rss', $r->getResponseBody() );
                $body = simplexml_load_string($r->getResponseBody());
                
                foreach ( $body->posts->post as $entry ) {
                    $extracted_link = (string)$entry->{'photo-link-url'};
                    $redirection = $this->getRedirectUrl($extracted_link);
                    $clean_link = $redirection ? $redirection : $extracted_link;
                    $this->fetchLink($clean_link);
                }
            } else {
                $this->sendLog("Response Code: " . $r->getResponseCode() );
            }
        } catch (HttpException $ex) {
            $this->sendLog("Raised Exception: " . $ex);
        }
        
    }
    
    private function sendLog($message){
        fwrite($this->log_handle, "[" . date('Y-m-d H:i:s') . "]" . " " . $message . "\n");
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
        if ( $parsed_link == false )
            return false;
        
        $file_name = basename($parsed_link['path']);
        $final_link = str_replace(" ", "%20", $link);
        
        $this->sendLog("Detected link: " . $final_link);
        
        if ( ! file_exists ($this->location . "/" . $file_name) ) {
            
            $r = new HttpRequest($final_link, HttpRequest::METH_GET);
            $r->addHeaders(array('User-Agent' => 'unsplash-dl'));
            
            try {
                $r->send();
                if ($r->getResponseCode() == 200) {
                    file_put_contents($this->location . "/" . $file_name, $r->getResponseBody());
                    $this->sendLog("Succesfully downloaded " . $file_name);
                    
                } else {
                    $this->sendLog( " " . $file_name . " reported a status code: " . $r->getResponseCode() );
                     
                }
            } catch (HttpException $ex) {
                $this->sendLog("Raised Exception: " . $ex);
            }
            
        } else {
            $this->sendLog($file_name . " already downloaded. Skipping.");
        }
    }
}

$unsplash = new UnsplashFetch(  'http://unsplash.com/api/read',
                                '/Users/' . get_current_user() . '/Pictures/Unsplash',
                                '/Users/' . get_current_user() . '/Library/Logs/com.mcdado.unsplash.log');
$unsplash->init();
$unsplash->terminate();

?>