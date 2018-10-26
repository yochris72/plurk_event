<?php

namespace App\Services\Plurk;

class ProfileAPI extends Plurk {

    public function __construct() {  
        $this->plurk_domain = config('app.plurk_domain');
    }

    public function getPublicProfile($user, $minimal_data = false, $include_plurks = false) {
        $url = $this->plurk_domain . 'APP/Profile/getPublicProfile';
        $field = array(
            'user_id'=>$user,
            'minimal_data'=>$minimal_data,
            'include_plurks'=>$include_plurks,
        );

        return $this->CallAPI($field, $url);
    }
}
