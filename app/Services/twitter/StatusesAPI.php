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

        return $this->CallAPI($getfield, $url);
    }

    public function showbyID($id) {
        $url = $this->domain . $this->ver . '/statuses/show.json';
        $getfield = "id=$id"; 

        return $this->CallAPI($getfield, $url);
    }
}
