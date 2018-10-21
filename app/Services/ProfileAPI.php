<?php

namespace App\Services;

class ProfileAPI extends Plurk {

    public function __construct() {  

    }

    public function getPublicProfile($user, $minimal_data = false, $include_plurks = false) {
        $API_url = 'APP/Profile/getPublicProfile';
        $parameters = array(
        	'user_id'=>$user,
        	'minimal_data'=>$minimal_data,
        	'include_plurks'=>$include_plurks,
        );
        $result = $this->API_request($API_url, $parameters);

        if ( $result['result'] ) {
            return array('status' => true, 'content' => $result['content']);        	
        } else {
            $error = json_decode($result['content'], true);
        	return array('status' => false, 'content' => $error['error_text']);
        }
        //return $this->API_request($API_url,$parameters);
    }
}
