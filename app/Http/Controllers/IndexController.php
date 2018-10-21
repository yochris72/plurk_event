<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\Plurk;
use App\Services\UserAPI;
use App\Services\TimelineAPI;
use Session;

class IndexController extends Controller {

	var $view_data = array();

	public function __construct(Plurk $plurk, UserAPI $UserAPI, TimelineAPI $TimelineAPI) {
		$this->plurk = $plurk;
		$this->UserAPI = $UserAPI;
        $this->TimelineAPI = $TimelineAPI;
    }

    public function index() { 
    	if ( !Session::has('oauth_access_token') ) {
    		return redirect('/login_app');
    	}
    
    	$getPlurk = $this->TimelineAPI->getPlurk(1371813309);
        if ( $getPlurk['status'] ) {
            var_dump($getPlurk['content']);    
        } else {
            echo $getPlurk['content'];
        }
        //var_dump($this->UserAPI->UserMe());
		//return view('welcome', $this->view_data);
	}

    public function login_app($platform = 'PC') {        
    	$redirect_url = $this->plurk->get_authorization_url();
    	if ( $redirect_url ) {
    		return redirect($redirect_url);
    	}
    	echo 'get authorization url failed';
    }

    public function callback(Request $request) {        
    	if ( $request->has('oauth_token') ) {
    		$oauth_token = $request->get('oauth_token');
    		$oauth_verifier = $request->get('oauth_verifier');
    		$get_token = $this->plurk->get_access_token($oauth_token, $oauth_verifier);
    		if ( $get_token ) {
    			return redirect('/');
    		} else {
    			echo 'get access token failed';
    		}
    	} else {
    		echo 'invalid request';
    	}    	
    }

}