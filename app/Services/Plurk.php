<?php

namespace App\Services;
use Session;

class Plurk {
	protected $plurk_domain;

    protected $request_token_url;
    protected $authorization_url;
    protected $authorization_url_m;
    protected $access_token_url;

    protected $retry_time;

    public function __construct() {
    	$this->plurk_domain = config('app.plurk_domain');

	    $this->request_token_url = $this->plurk_domain.'OAuth/request_token';
	    $this->authorization_url = $this->plurk_domain.'OAuth/authorize';
	    $this->authorization_url_m = $this->plurk_domain.'m/authorize';
	    $this->access_token_url = $this->plurk_domain.'OAuth/access_token';
	    $this->retry_time = 10;
    }

    public function get_authorization_url($platform = 'PC') {
    	$this->clear_token();
    	$request_token = $this->plurk_request_token();
    	if ( !$request_token ) {
			return false;
    	}

    	Session::put('oauth_token', $request_token['oauth_token']);
		Session::put('oauth_token_secret', $request_token['oauth_token_secret']);
		$oauth_token = $request_token['oauth_token'];
		if ( $platform == 'PC' ) {
			return $this->authorization_url.'?oauth_token='.$oauth_token;
		} else {
			return $this->authorization_url_m.'?oauth_token='.$oauth_token;
		}   
    }

    public function get_access_token($oauth_token, $oauth_verifier) {
    	$request_token = $this->plurk_access_token($oauth_token, $oauth_verifier);
    	if ( !$request_token ) {
			return false;
    	}

    	Session::put('oauth_access_token', $request_token['oauth_token']);
		Session::put('oauth_access_token_secret', $request_token['oauth_token_secret']);
    	return $request_token;
    }

    public function clear_token() {
    	Session::forget('oauth_access_token');
    	Session::forget('oauth_access_token_secret');
    	Session::forget('oauth_token');
    	Session::forget('oauth_token_secret');
    }

    protected function API_request($url, $parameters = array()) {
		$oauth_array = array(
			'oauth_consumer_key'=>config('app.consumer_key'),
			'oauth_nonce'=>uniqid(mt_rand(1, 1000)),
			'oauth_signature_method'=>'HMAC-SHA1',
			'oauth_timestamp'=>time(),
			'oauth_version'=>'1.0',
			'oauth_token'=>Session::get('oauth_access_token'),
		);

		foreach ($parameters as $key => $value) {
			$oauth_array[$key] = $value;
		}

		$request_url = config('app.plurk_domain').$url;
		ksort($oauth_array);
		$query_parameters = $this->buildquery($oauth_array);
		$oauth_array['oauth_signature'] = $this->getSignature($query_parameters, $request_url);

		$result = $this->doRequest($request_url, 'POST', $oauth_array);

		$status = ( $result['result'] ) ? true : false;
		$content = $status ? json_decode($result['content'], true) : $result['content'];
		return $return = array('result'=>$status, 'content'=>$content);
    }

    private function plurk_request_token() {
		$oauth_array = array(
			'oauth_consumer_key'=>config('app.consumer_key'),
			'oauth_nonce'=>uniqid(mt_rand(1, 1000)),
			'oauth_signature_method'=>'HMAC-SHA1',
			'oauth_timestamp'=>time(),
			'oauth_version'=>'1.0',			
		);

		$request_url = $this->request_token_url;
		ksort($oauth_array);
		$query_parameters = $this->buildquery($oauth_array);
		$oauth_array['oauth_signature'] = $this->getSignature($query_parameters, $request_url, false);

		$result = $this->doRequest($request_url, 'POST', $oauth_array);
		if ( $result['result'] ) {
			parse_str($result['content'], $output);
			return $output;
		} else {		
			return false;
		}
    }

    private function plurk_access_token($oauth_token, $oauth_verifier) {
		$oauth_array = array(
			'oauth_consumer_key'=>config('app.consumer_key'),
			'oauth_nonce'=>uniqid(mt_rand(1, 1000)),
			'oauth_signature_method'=>'HMAC-SHA1',
			'oauth_timestamp'=>time(),
			'oauth_version'=>'1.0',
			'oauth_token'=>$oauth_token,
			'oauth_verifier'=>$oauth_verifier,
			'oauth_token_secret'=>Session::get('oauth_token_secret'),
		);

		$request_url = $this->access_token_url;
		ksort($oauth_array);
		$query_parameters = $this->buildquery($oauth_array);		
		$oauth_array['oauth_signature'] = $this->getSignature($query_parameters, $request_url);
		
		$result = $this->doRequest($request_url, 'POST', $oauth_array);

		if ( $result['result'] ) {			
			parse_str($result['content'], $output);
			return $output;
		} else {		
			return false;
		}
    }

    private function getSignature($query_parameters, $url, $secret = true, $method = 'POST') {
    	$signature_encode_key = urlencode(config('app.consumer_secret')).'&';
    	if ( $secret ) {
    		if ( Session::has('oauth_access_token_secret') ) {
    			$signature_encode_key .= Session::get('oauth_access_token_secret');	
    		} else {
    			$signature_encode_key .= Session::get('oauth_token_secret');	
    		}			
		}
		$base_string = $method."&".urlencode($url).'&'.urlencode($query_parameters);		
		$signature = base64_encode(hash_hmac("sha1", $base_string, $signature_encode_key, true));
		return rawurlencode($signature);
    }

    public function doRequest($url, $method = 'POST', $parameter = [])
    {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_HEADER, false);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 1);
	    switch ($method) {
	        case 'GET':
	        	$query_parameters = $this->buildquery($parameter);
	        	curl_setopt($ch, CURLOPT_URL, $url."?".$query_parameters);
	            break;
	        case 'POST':
	        	curl_setopt($ch, CURLOPT_URL, $url);
	            curl_setopt($ch, CURLOPT_POST, 1);
	            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildquery($parameter));
	            break;
	        default:
	            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	            break;
	    }

	    $result = curl_exec($ch);
	    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);

	    $status = ( $code == 200 ) ? true : false;
	    return array('result'=>$status, 'content'=>$result);
	}

	private function buildquery($data) {	    
	    $query = "";
	    foreach( $data as $key => $val ) {
	        if( !$query ) {
	            $query = $key."=".$val;
	        } else {
	            $query .= "&".$key."=".$val;
	        }
	    }
	    return $query;
	}
}