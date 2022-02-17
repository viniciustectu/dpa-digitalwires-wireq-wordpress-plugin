<?php
/**  -*- coding: utf-8 -*-
*
* Copyright 2022, dpa-IT Services GmbH
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*    http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

class DigitalwiresAPI{
    public function __construct($url){
        $this->url = $url;
    }

    public function fetch_articles(){
        $response = wp_remote_get(($this->url . 'entries.json'));
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if(isset($body['entries'])){
            return $body['entries'];
        }else{
            return [];
        }
    }

    public function remove_from_queue($receipt){
        error_log('Removing ' . $receipt . ' from queue');

        $response = wp_remote_request(
            ($this->url . 'entry/' . $receipt), 
            array('method' => 'DELETE')
        );
    }
}

?>