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
    
    	// $getPlurk = $this->TimelineAPI->getPlurk(1371813309);
     //    if ( $getPlurk['status'] ) {
     //        var_dump($getPlurk['content']);    
     //    } else {
     //        echo $getPlurk['content'];
     //    }
        //var_dump($this->UserAPI->UserMe());
		
        return view('test', $this->view_data);
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

    public function uploadImage(Request $request) {
        $image = $request->file('picture');
        $image_name = time().'_'.substr(md5(time()), 0, 5).".".$image->getClientOriginalExtension();
        $destinationPath = public_path('temp_images');
        $image->move($destinationPath, $image_name);        
        $image_type = image_type_to_mime_type(exif_imagetype($destinationPath."\\".$image_name));
        $image_content = file_get_contents($destinationPath."\\".$image_name);        
        $uploadPicture = $this->TimelineAPI->uploadPicture($image_content, $image_name, $image_type);
        dd($uploadPicture);
    }
}