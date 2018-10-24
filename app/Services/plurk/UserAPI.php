<?php

namespace App\Services\Plurk;

class UserAPI extends Plurk {

    public function __construct() {  

    }

    public function UserMe() {
        $API_url = 'APP/Users/me';
        $result = $this->API_request($API_url);
        if ( $result['result'] ) {
            return array('status' => true, 'content' => $result['content']);        	
        } else {
            $error = json_decode($result['content'], true);
        	return array('status' => false, 'content' => $error['error_text']);
        }
    }
}
