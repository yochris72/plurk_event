<?php

namespace App\Services;

class TimelineAPI extends Plurk {

    public function __construct() {  

    }

    public function getPlurk($plurk_id, $favorers_detail = true, $limited_detail = true, $replurkers_detail = true) {
        $API_url = 'APP/Timeline/getPlurk';
        $parameters = array(
            'plurk_id'=>$plurk_id,
            'favorers_detail'=>$favorers_detail,
            'limited_detail'=>$limited_detail,
            'replurkers_detail'=>$replurkers_detail,
        );
        $result = $this->API_request($API_url, $parameters);

        if ( $result['result'] ) {
            return array('status' => true, 'content' => $result['content']);        	
        } else {
            $error = json_decode($result['content'], true);
        	return array('status' => false, 'content' => $error['error_text']);
        }
    }

    public function uploadPicture($image_content, $image_name, $image_type) {
        $API_url = 'APP/Timeline/uploadPicture';
        $parameters = array(
            'image_content'=>$image_content,
            'image_name'=>$image_name,
            'image_type'=>$image_type,
        );
        $result = $this->API_request($API_url, $parameters, true);

        if ( $result['result'] ) {
            return array('status' => true, 'content' => $result['content']);            
        } else {
            $error = json_decode($result['content'], true);
            return array('status' => false, 'content' => $error['error_text']);
        }
    }
}
