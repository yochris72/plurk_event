<?php

namespace App\Services\Plurk;

class TimelineAPI extends Plurk {

    public function __construct() {  
        $this->plurk_domain = config('app.plurk_domain');
    }

    public function getPlurk($plurk_id, $favorers_detail = true, $limited_detail = true, $replurkers_detail = true) {
        $url = $this->plurk_domain . 'APP/Timeline/getPlurk';
        $field = array(
            'plurk_id'=>$plurk_id,
            'favorers_detail'=>$favorers_detail,
            'limited_detail'=>$limited_detail,
            'replurkers_detail'=>$replurkers_detail,
        );

        return $this->CallAPI($field, $url);
    }

    public function uploadPicture($request) {
        $image = $request->file('picture');
        $image_name = time().'_'.substr(md5(time()), 0, 5).".".$image->getClientOriginalExtension();
        $destinationPath = public_path('temp_images');
        $image->move($destinationPath, $image_name);        
        $image_type = image_type_to_mime_type(exif_imagetype($destinationPath."\\".$image_name));
        $image_content = file_get_contents($destinationPath."\\".$image_name);   

        $url = $this->plurk_domain . 'APP/Timeline/uploadPicture';
        $postfield = array(
            'image_content'=>$image_content,
            'image_name'=>$image_name,
            'image_type'=>$image_type,
        );

        $result = $this->setPostfields($postfield)->buildOauth($url, "POST")->performImageUploadRequest();
        unlink($destinationPath."\\".$image_name);

        if ( $result ) {
            return array('status' => true, 'content' => json_decode($result,true) );
        } else {
            $error = json_decode($result, true);
            return array('status' => false, 'content' => $error['error_text']);
        }  
    }
}
