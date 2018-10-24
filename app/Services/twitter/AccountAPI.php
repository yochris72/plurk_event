<?php

namespace App\Services\Twitter;

class AccountAPI extends Twitter {

    public function __construct() {  
    	$this->domain = config('app.twitter_domain');
        $this->ver = config('app.twitter_ver');
    }

    public function VerifyCredentials() {
        $url = $this->domain . $this->ver . '/account/verify_credentials.json';
        $getfield = '';
        $requestMethod = 'GET';  

        return $this->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
    }
}
