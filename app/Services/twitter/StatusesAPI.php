<?php

namespace App\Services\Twitter;

class StatusesAPI extends Twitter {

    public function __construct() {  
    	$this->domain = config('app.twitter_domain');
        $this->ver = config('app.twitter_ver');
    }

    public function getHomeTimeline() {
        $url = $this->domain . $this->ver . '/statuses/home_timeline.json';
        $getfield = '';
        $requestMethod = 'GET';  

        return $this->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
    }
}
