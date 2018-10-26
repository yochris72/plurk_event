<?php

namespace App\Repositories\twitter;

use App\Services\twitter\StatusesAPI;

class TweetRepository {

	private $StatusesAPI;

	public function __construct(StatusesAPI $StatusesAPI) {  
    	$this->StatusesAPI = $StatusesAPI;
    }

    public function GetImageinTweet($id) {
    	$result = false;    	
    	$messege = "";
    	$media_url = array();

    	if ( !is_numeric($id) ) {
    		$url_path = parse_url($id, PHP_URL_PATH);    		
    		$dirs = explode("/", $url_path);    		
    		$id = count($dirs) > 3 ? $dirs[3] : "error";
    	}

    	if ( !is_numeric($id) ) {
    		$messege = "不正確的網址格式";
    	} else {			
	    	$tweet = $this->StatusesAPI->showbyID($id);
	    	
	    	if ( $tweet['status'] ) {
	    		$tweet_content = $tweet['content'];
	    		if ( isset($tweet_content['extended_entities']['media']) ) {
	    			$medias = $tweet_content['extended_entities']['media'];
	    			foreach ($medias as $key => $media) {
	    				$media_url[] = $media['media_url'];
	    			}

	    			if ( $media_url ) {
	    				$result = true;
	    			} else {
	    				$messege = "此推特不含媒體資源";
	    			}
	    		} else {
	    			$messege = "此推特不含媒體資源";
	    		}
	    	} else {
	    		$messege = "指定的推特不存在，或是沒有讀取權限";	    		
	    	}
    	}

    	return array("result"=>$result, "messege"=>$messege, "media"=>$media_url);
    }
}
