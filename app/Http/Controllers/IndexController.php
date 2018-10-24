<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\oAuth;
use App\Services\Plurk\Plurk;
use App\Services\Plurk\UserAPI;
use App\Services\Plurk\TimelineAPI;
use App\Services\twitter\Twitter;
use App\Services\twitter\AccountAPI;
use Session;


class IndexController extends Controller {

	var $view_data = array();

	public function __construct(Plurk $Plurk, UserAPI $UserAPI, TimelineAPI $TimelineAPI,
                                Twitter $Twitter, AccountAPI $AccountAPI) {
		$this->Plurk = $Plurk;
		$this->UserAPI = $UserAPI;
        $this->TimelineAPI = $TimelineAPI;
        $this->Twitter = $Twitter;
        $this->AccountAPI = $AccountAPI;
    }

    public function index() { 
        $plurk_login = false;
        $twitter_login = false;
        //dd(Session::all());
        if ( Session::has('oauth_access_token') ) {
            $plurk_login = true;
        } 

        if ( Session::has('oauth_twitter_access_token') ) {
            $twitter_login = true;
        }

        if ( $plurk_login ) {
            $myData = $this->UserAPI->UserMe();
            $this->view_data['plurk_data'] = $myData['content'];
        }

        if ( $twitter_login ) {
            $userVerify = json_decode($this->AccountAPI->VerifyCredentials(), true);
            $this->view_data['twitter_data'] = $userVerify;
        }


        // if ( !Session::has('oauth_twitter_access_token') ) {            
        //     return redirect($this->Twitter->getAuthorizeUrl());
        // }
        // return redirect('/login_app');


        //return $this->StatusesAPI->getHomeTimeline(); 

    	// if ( !Session::has('oauth_access_token') ) {
    	// 	return redirect('/login_app');
    	// }
    
    	// $getPlurk = $this->TimelineAPI->getPlurk(1371813309);
     //    if ( $getPlurk['status'] ) {
     //        var_dump($getPlurk['content']);    
     //    } else {
     //        echo $getPlurk['content'];
     //    }
        //var_dump($this->UserAPI->UserMe());
		
        $this->view_data['plurk_login'] = $plurk_login;
        $this->view_data['twitter_login'] = $twitter_login;
        return view('index', $this->view_data);
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

    public function login_plurk($platform = 'PC') {        
        $redirect_url = $this->Plurk->get_authorization_url();
        if ( $redirect_url ) {
            return redirect($redirect_url);
        }
        echo 'plurk service down...';
    }    

    public function logout_plurk() {        
        $this->Plurk->clear_token();
        return redirect('/');
    }   

    public function login_twitter() {
        $url = $this->Twitter->getAuthorizeUrl();
        if ( $url ) {
            return redirect($url);    
        } else {
            return "twitter service down...";
        }    
    }

    public function logout_twitter() {        
        $this->Twitter->clear_token();
        return redirect('/');
    }   

    public function callback_plurk(Request $request) {          
        $getResult = $this->Plurk->getAccessToken($request);
        switch ($getResult) {
            case -1:
                // get access token failed
                return "get access token failed (Plurk)";
                break;
            case 1:
                // callback success
                return redirect('/');
                break;
            default:
                // invalid request
                return "invalid request";
                break;
        }
    }

    public function callback_twitter(Request $request) {                  
        $getResult = $this->Twitter->getAccessToken($request);
        switch ($getResult) {
            case -1:
                // get access token failed
                return "get access token failed (Twitter)";
                break;
            case 1:
                // callback success
                return redirect('/');
                break;
            default:
                // invalid request
                return "invalid request";
                break;
        }
    }
    
}