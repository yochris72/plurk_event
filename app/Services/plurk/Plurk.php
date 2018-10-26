<?php

namespace App\Services\plurk;
use App\Services\oAuth as oAuth;
use Session;
use URL;

class Plurk extends oAuth {
	protected $callback;
	protected $twitter_domain;

    protected $request_token_url;
    protected $authorization_url;    
    protected $access_token_url;

    /**
     * @var string
     */
    private $oauth_access_token;
    private $oauth_access_token_secret;
    private $consumer_key;
    private $consumer_secret;

    /**
     * @var array
     */
    private $postfields;

    /**
     * @var string
     */
    private $getfield;

    /**
     * @var mixed
     */
    protected $oauth;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $requestMethod;

    /**
     * The HTTP status code from the previous request
     *
     * @var int
     */
    protected $httpStatusCode;


    public function __construct() {
    	$this->domain = URL::to('/');

        $this->plurk_domain = config('app.plurk_domain');
        $this->callback = $this->plurk_domain ."/callback_plurk";

        $this->request_token_url = $this->plurk_domain.'OAuth/request_token';
        $this->authorization_url = $this->plurk_domain.'OAuth/authorize';
        $this->authorization_url_m = $this->plurk_domain.'m/authorize';
        $this->access_token_url = $this->plurk_domain.'OAuth/access_token';	    
    }

    /**
     * Set postfields array, example: array('screen_name' => 'J7mbo')
     *
     * @param array $array Array of parameters to send to API
     *
     * @throws \Exception When you are trying to set both get and post fields
     *
     * @return TwitterAPIExchange Instance of self for method chaining
     */
    public function setPostfields(array $array) {
        if (!is_null($this->getGetfield())) {
            throw new Exception('You can only choose get OR post fields (post fields include put).');
        }

        if (isset($array['status']) && substr($array['status'], 0, 1) === '@') {
            $array['status'] = sprintf("\0%s", $array['status']);
        }

        foreach ($array as $key => &$value) {
            if (is_bool($value)) {
                $value = ($value === true) ? 'true' : 'false';
            }
        }

        $this->postfields = $array;

        // rebuild oAuth
        if (isset($this->oauth['oauth_signature'])) {
            $this->buildOauth($this->url, $this->requestMethod);
        }

        return $this;
    }

    /**
     * Set getfield string, example: '?screen_name=J7mbo'
     *
     * @param string $string Get key and value pairs as string
     *
     * @throws \Exception
     *
     * @return \TwitterAPIExchange Instance of self for method chaining
     */
    public function setGetfield($string) {
        if (!is_null($this->getPostfields())) {
            throw new Exception('You can only choose get OR post / post fields.');
        }

        $getfields = preg_replace('/^\?/', '', explode('&', $string));
        $params = array();

        foreach ($getfields as $field) {
            if ($field !== '') {
                list($key, $value) = explode('=', $field);
                $params[$key] = $value;
            }
        }

        $this->getfield = '?' . http_build_query($params, '', '&');

        return $this;
    }

    /**
     * Get getfield string (simple getter)
     *
     * @return string $this->getfields
     */
    public function getGetfield() {
        return $this->getfield;
    }

    /**
     * Get postfields array (simple getter)
     *
     * @return array $this->postfields
     */
    public function getPostfields() {
        return $this->postfields;
    }

	/**
     * Build the Oauth object using params set in construct and additionals
     * passed to this method. For v1.1, see: https://dev.twitter.com/docs/api/1.1
     *
     * @param string $url           The API url to use. Example: https://api.twitter.com/1.1/search/tweets.json
     * @param string $requestMethod Either POST or GET
     *
     * @throws \Exception
     *
     * @return \TwitterAPIExchange Instance of self for method chaining
     */
    public function buildOauth($url, $requestMethod) {
        if (!in_array(strtolower($requestMethod), array('post', 'get', 'put', 'delete'))) {
            throw new Exception('Request method must be either POST, GET or PUT or DELETE');
        }

        $consumer_key              = config('app.plurk_consumer_key');
        $consumer_secret           = config('app.plurk_consumer_secret');
		$oauth_access_token        = '';
        $oauth_access_token_secret = '';
        if ( Session::has('oauth_plurk_access_token') ) {
        	$oauth_access_token        = Session::get('oauth_plurk_access_token');
        	$oauth_access_token_secret = Session::get('oauth_plurk_access_token_secret');
        } 

        $oauth = array(
            'oauth_consumer_key' => $consumer_key,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            //'oauth_token' => $oauth_access_token,
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );

        if ( $oauth_access_token ) {
        	$oauth['oauth_token'] = $oauth_access_token;
        }        

        $getfield = $this->getGetfield();

        if (!is_null($getfield)) {
            $getfields = str_replace('?', '', explode('&', $getfield));

            foreach ($getfields as $g) {
                $split = explode('=', $g);

                /** In case a null is passed through **/
                if (isset($split[1])) {
                    $oauth[$split[0]] = urldecode($split[1]);
                }
            }
        }

        $postfields = $this->getPostfields();

        if (!is_null($postfields)) {
            foreach ($postfields as $key => $value) {
                if ( strpos($key, "image") === false ) {
                    $oauth[$key] = $value;
                }                
            }
        }        
       
        $base_info = $this->buildBaseString($url, $requestMethod, $oauth);
        $composite_key = rawurlencode($consumer_secret) . '&';
        if ( $oauth_access_token_secret ) {
        	$composite_key .= rawurlencode($oauth_access_token_secret);
        } else if ( Session::has('oauth_plurk_token_secret') ) {
            $composite_key .= rawurlencode(Session::get('oauth_plurk_token_secret'));
        } 

        $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
        $oauth['oauth_signature'] = $oauth_signature;
        
        $this->url           = $url;
        $this->requestMethod = $requestMethod;
        $this->oauth         = $oauth;

        return $this;
    }

    /**
     * Perform the actual data retrieval from the API
     *
     * @param boolean $return      If true, returns data. This is left in for backward compatibility reasons
     * @param array   $curlOptions Additional Curl options for this request
     *
     * @throws \Exception
     *
     * @return string json If $return param is true, returns json data.
     */
    public function performRequest($return = true, $curlOptions = array()) {
        if (!is_bool($return)) {
            throw new Exception('performRequest parameter must be true or false');
        }

        $header =  array($this->buildAuthorizationHeader($this->oauth), 'Expect:');

        $getfield = $this->getGetfield();
        $postfields = $this->getPostfields();

        if (in_array(strtolower($this->requestMethod), array('put', 'delete'))) {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $this->requestMethod;
        }

        $options = $curlOptions + array(
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_HEADER => false,
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        );

        if (!is_null($postfields)) {
            $options[CURLOPT_POSTFIELDS] = http_build_query($postfields, '', '&');
        } else {
            if ($getfield !== '') {
                $options[CURLOPT_URL] .= $getfield;
            }
        }
        
        $feed = curl_init();
        curl_setopt_array($feed, $options);
        $json = curl_exec($feed);

        $this->httpStatusCode = curl_getinfo($feed, CURLINFO_HTTP_CODE);

        if (($error = curl_error($feed)) !== '') {
            curl_close($feed);

            throw new \Exception($error);
        }

        curl_close($feed);

        return $json;
    }

    public function performImageUploadRequest($return = true, $curlOptions = array())
    {          
        $postfields = $this->getPostfields();

        $delimiter = "----" . uniqid();
        $data = "--" . $delimiter . "\r\n";     
        $data .= "Content-Disposition: form-data; name=\"image\"; filename=\"" . $postfields['image_name'] . "\"\r\n";       
        $data .= "Content-Type: " . $postfields['image_type'] . "\r\n\r\n";
        $data .= $postfields['image_content'] . "\r\n"; 
        $data .= "--" . $delimiter . "--";

        $header =  array($this->buildAuthorizationHeader($this->oauth), 
                        "cache-control: no-cache",
                        "content-type: multipart/form-data; boundary=".$delimiter);

        $options = $curlOptions + array(
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_HEADER => false,
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
        );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $json = curl_exec($curl);

        $this->httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (($error = curl_error($curl)) !== '') {
            curl_close($curl);

            throw new \Exception($error);
        }

        curl_close($curl);

        return $json;
    }

    /**
     * Private method to generate the base string used by cURL
     *
     * @param string $baseURI
     * @param string $method
     * @param array  $params
     *
     * @return string Built base string
     */
    private function buildBaseString($baseURI, $method, $params)
    {
        $return = array();
        ksort($params);

        foreach($params as $key => $value)
        {
            $return[] = rawurlencode($key) . '=' . rawurlencode($value);
        }

        return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $return));
    }

    /**
     * Private method to generate authorization header used by cURL
     *
     * @param array $oauth Array of oauth data generated by buildOauth()
     *
     * @return string $return Header used by cURL for request
     */
    private function buildAuthorizationHeader(array $oauth) {
        $return = 'Authorization: OAuth ';
        $values = array();

        foreach($oauth as $key => $value) {
            if (in_array($key, array('oauth_consumer_key', 'oauth_token', 'oauth_signature', 'oauth_signature_method'
                , 'oauth_nonce', 'oauth_timestamp', 'oauth_version', 'oauth_verifier', 'oauth_callback', 'oauth_token_secret'))) {
                $values[] = "$key=\"" . rawurlencode($value) . "\"";
            }
        }

        $return .= implode(', ', $values);
        return $return;
    }

    /**
     * Helper method to perform our request
     *
     * @param string $url
     * @param string $method
     * @param string $data
     * @param array  $curlOptions
     *
     * @throws \Exception
     *
     * @return string The json response from the server
     */
    public function request($url, $method = 'get', $data = null, $curlOptions = array()) {
        if (strtolower($method) === 'get') {
            $this->setGetfield($data);
        } else {
            $this->setPostfields($data);
        }

        return $this->buildOauth($url, $method)->performRequest(true, $curlOptions);
    }

    /**
     * Get the HTTP status code for the previous request
     *
     * @return integer
     */
    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }

    public function getAuthorizeUrl($platform = 'PC') {
    	$this->clear_token();
    	$getoAuthToken = $this->getoAuthToken();
    	if ( $getoAuthToken ) {
            $url = $platform == 'PC' ? $this->authorization_url : $this->authorization_url_m;
    		return $url . "?oauth_token=" . Session::get('oauth_plurk_token');
    	} else {
    		return false;
    	}    	
    }

    public function getoAuthToken() {
    	$url = $this->request_token_url;        
        $postfield = array();
        $requestMethod = 'POST';
        $token_info = $this->setPostfields($postfield)->buildOauth($url, $requestMethod)->performRequest();          
        if ( $token_info ) {
        	parse_str($token_info, $output);
        	Session::put('oauth_plurk_token', $output['oauth_token']);
        	Session::put('oauth_plurk_token_secret', $output['oauth_token_secret']);
        	return true;        	
        }
        return false;
    }

    public function getAccessToken($request) {
    	if ( $request->has('oauth_token') ) {
            $oauth_token = $request->get('oauth_token');
            $oauth_verifier = $request->get('oauth_verifier');
            $url = $this->access_token_url;
			$postfield = array('oauth_token'=>$oauth_token, 
                               'oauth_verifier'=>$oauth_verifier, 
                               'oauth_token_secret'=>Session::get('oauth_plurk_token_secret'));
        	$requestMethod = 'POST';
        	$token_info = $this->setPostfields($postfield)->buildOauth($url, $requestMethod)->performRequest();  

        	if ( $token_info ) {
        		parse_str($token_info, $output);
        		Session::put('oauth_plurk_access_token', $output['oauth_token']);
        		Session::put('oauth_plurk_access_token_secret', $output['oauth_token_secret']);
	            return 1;
	        } else {
				return -1;
			}
        } else {
	        return 0;
       	}            
    }

    protected function CallAPI($field, $url) {
        if ( is_array($field) ) {
            $result = $this->setPostfields($field)->buildOauth($url, "POST")->performRequest();
        } else {
            $result = $this->setGetfield($field)->buildOauth($url, "GET")->performRequest();
        }
    
        if ( $this->httpStatusCode == 200 ) {
            return array('status' => true, 'content' => json_decode($result,true) );
        } else {
            $error = json_decode($result, true);
            return array('status' => false, 'content' => $error['error_text']);
        }
    }

    public function clear_token() {
        Session::forget('oauth_plurk_access_token');
        Session::forget('oauth_plurk_access_token_secret');
        Session::forget('oauth_plurk_token');
        Session::forget('oauth_plurk_token_secret');
    }
}