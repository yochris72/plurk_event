<?php

namespace App\Services\Plurk;

class UserAPI extends Plurk {

    public function __construct() {  
        $this->plurk_domain = config('app.plurk_domain');
    }

    public function UserMe() {
        $url = $this->plurk_domain . 'APP/Users/me';
        $field = array();

        return $this->CallAPI($field, $url);
    }
}
