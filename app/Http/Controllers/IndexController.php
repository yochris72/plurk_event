<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\oAuth;
use App\Services\Plurk\Plurk;
use App\Services\Plurk\UserAPI;
use App\Services\Plurk\ProfileAPI;
use App\Services\Plurk\TimelineAPI;
use App\Services\twitter\Twitter;
use App\Services\twitter\AccountAPI;
use App\Services\twitter\StatusesAPI;
use Session;


class IndexController extends Controller {

	var $view_data = array();

	public function __construct(Plurk $Plurk, UserAPI $UserAPI, ProfileAPI $ProfileAPI, TimelineAPI $TimelineAPI,
                                Twitter $Twitter, AccountAPI $AccountAPI, StatusesAPI $StatusesAPI) {
		$this->Plurk = $Plurk;
		$this->UserAPI = $UserAPI;
        $this->ProfileAPI = $ProfileAPI;
        $this->TimelineAPI = $TimelineAPI;
        $this->Twitter = $Twitter;
        $this->AccountAPI = $AccountAPI;
        $this->StatusesAPI = $StatusesAPI;
    }

    public function index() { 
        $plurk_login = false;
        $twitter_login = false;
        //dd(Session::all());
        if ( Session::has('oauth_plurk_access_token') ) {
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
            //dd($this->StatusesAPI->showbyID(1055437019025825792));
            $userVerify = $this->AccountAPI->VerifyCredentials();            
            $this->view_data['twitter_data'] = $userVerify['content'];
        }

		
        $this->view_data['plurk_login'] = $plurk_login;
        $this->view_data['twitter_login'] = $twitter_login;
        return view('index', $this->view_data);
	}

    public function testPlurkUpload() {
        return view('test', $this->view_data);
    } 

    public function uploadPlurkImage(Request $request) {
        $uploadPicture = $this->TimelineAPI->uploadPicture($request);
        if ( $uploadPicture['status'] ) {            
            echo "<img src=\"".$uploadPicture['content']['thumbnail']."\" /><br/>";
            echo "<img src=\"".$uploadPicture['content']['full']."\" />";
        } else {
            echo "upload failed";
        }        
    }

    public function login_plurk($platform = 'PC') {        
        $redirect_url = $this->Plurk->getAuthorizeUrl();
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