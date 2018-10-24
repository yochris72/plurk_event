<?php

namespace App\Services\Twitter;

class StatusesAPI extends Twitter {

    public function __construct() {  
        
    }

    public function getHomeTimeline() {
        $url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
        $getfield = '';
        $requestMethod = 'GET';  

        return $this->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
    }
}
