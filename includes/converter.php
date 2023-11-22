<?php 
/**  -*- coding: utf-8 -*-
*
* Copyright 2023, dpa-IT Services GmbH
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

class Converter{
    private $publish; 
    private $overwrite;
    private $url;

    public function __construct($publish, $overwrite){
        $this->register_meta_fields();
        $this->publish = $publish;
        $this->overwrite = $overwrite;
    }

    private function register_meta_fields(){
        register_post_meta('post', 'dw_urn', [
            'type' => 'string',
            'description' => 'digitalwires urn',
            'single' => true,
            'show_in_rest' => true
            ]);
      
          register_post_meta('post', 'dw_version', [
            'type' => 'integer',
            'description' => 'digitalwires version',
            'single' => true,
            'show_in_rest' => true
           ]);
          register_post_meta('post', 'dw_version_created', [
            'type' => 'string',
            'description' => 'digitalwires version_created',
            'single' => true,
            'show_in_rest' => true
           ]);
      
          register_post_meta('post', 'dw_updated', [
            'type' => 'string',
            'description' => 'digitalwires updated',
            'single' => true,
            'show_in_rest' => true
           ]);
      
          register_post_meta('attachment', 'dw_parent_urn', [
            'type' => 'string',
            'description' => 'digitalwires urn',
            'single' => true,
            'show_in_rest' => true
          ]);
      
          register_post_meta('attachment', 'dw_urn', [
            'type' => 'string',
            'description' => 'digitalwires urn',
            'single' => true,
            'show_in_rest' => true
            ]);
      
          register_post_meta('attachment', 'dw_version', [
            'type' => 'integer',
            'description' => 'digitalwires version',
            'single' => true,
            'show_in_rest' => true
           ]);
      
          register_post_meta('attachment', 'dw_version_created', [
            'type' => 'string',
            'description' => 'digitalwires version_created',
            'single' => true,
            'show_in_rest' => true
           ]);
    }

    private function get_post_by_meta($key, $value){
        $query = get_posts([
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => $key,
                    'compare' => '=',
                    'value' => $value
                ]
            ],
            'order' => 'DESC',
            'orderby' => 'dw_version_created',
            'fields' => 'ids',
            'posts_per_page' => 1
        ]);
    
        if(empty($query)){
            error_log('No existing post found');
            return NULL;
        }else{
            error_log('Existing post ' . $query[0] . ' found');
            return $query[0];
        }
    }

    private function get_attachment_by_meta($key, $value){
        $query = get_posts([    
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'meta_query' => [
                [
                    'key' => $key,
                    'compare' => '=',
                    'value' => $value
                ]
            ],
            'order' => 'DESC',
            'orderby' => 'dw_version_created',
            'fields' => 'ids',
            'posts_per_page' => 1
        ]);

        if(empty($query)){
            return NULL;
        }else{
            return $query[0];
        }
    }

    public function add_association($a, $parent_urn){
        $existing_attachment_id = $this->get_attachment_by_meta('dw_urn', $a['urn']);
        
        $attachment_data = [
            'post_mime_type' => $a['renditions'][0]['mimetype'],
            'post_title' => $a['headline'],
            'post_content' => $a['caption'],
            'post_excerpt' => $a['caption'] . ' Foto: ' . $a['creditline'],
            'post_status' => 'inherit',
            'post_date_gmt' => date('Y-m-d H:i:s', strtotime($a['version_created'])),
            'meta_input' => array(
                '_wp_attachment_image_alt' => $a['caption'],
                'dw_parent_urn' => $parent_urn,
                'dw_urn' => $a['urn'],
                'dw_version' => $a['version'],
                'dw_version_created' => $a['version_created']
            )
        ];

        if($existing_attachment_id != NULL){
            $attachment_meta = get_post_meta($existing_attachment_id);
            
            if(strcmp($a['version'], $attachment_meta['dw_version'][0]) <= 0 &&
                strcmp($a['version_created'], $attachment_meta['dw_version_created'][0]) < 0
            ){
                $path = parse_url($a['renditions'][0]['url'], PHP_URL_PATH);
                $filename = basename($path);
        
                $filedata = file_get_contents($a['renditions'][0]['url']);

                if($filedata === False) return False;
        
                $upload_file = wp_upload_bits($filename, null, $filedata);
                
                update_attached_file($existing_attachment_id, $upload_file['file']);

                $attachment_metadata = wp_generate_attachment_metadata($existing_attachment_id, $upload_file['file']);
                wp_update_attachment_metadata($existing_attachment_id, $attachment_metadata);
                wp_update_post($existing_attachment_id, $attachment_data);

                error_log('Attachment ' . $a['urn'] . ' (v'. $a['version'] . ' updated, id ' . $existing_attachment_id . ')');
            }else{
                error_log('Attachment ' . $a['urn'] . ' (v'.$a['version']. ') already saved (id ' . $existing_attachment_id . ').Skipping');
            }
            
            return $existing_attachment_id;
        }else{
            $path = parse_url($a['renditions'][0]['url'], PHP_URL_PATH);
            $filename = basename($path);
        
            $filedata = file_get_contents($a['renditions'][0]['url']);
        
            if($filedata === False) return False;
            
            $upload_file = wp_upload_bits($filename, null, $filedata);
    
            $attachment_id = wp_insert_attachment($attachment_data, $upload_file['file']);
    
            if(!is_wp_error($attachment_id)){
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
                wp_update_attachment_metadata($attachment_id, $attachment_data);
                
                error_log('Attachment ' . $a['urn'] . ' (v' . $a['version'] . ') added as id ' . $attachment_id);
                return $attachment_id;
            }
        }
    }  

    private function clean_html($html, $dateline, $associations){
        $input_dom = new DOMDocument();
        $output_dom = new DOMDocument();

        //Prevent parsing errors on valid HTML5-elements, e.g. <section>
        libxml_use_internal_errors(true);
        $input_dom->loadHTML('<?xml encoding=\"utf-8\" ?>' . $html);
        libxml_clear_errors();

        $xpath = new DOMXpath($input_dom);

        //Insert dateline
        if(!empty($dateline)){
            $dateline = new DOMText($dateline);
            $firstP = $xpath->evaluate('//section/p[1]')[0];
            $firstP->insertBefore($dateline, $firstP->firstChild);
        }


        foreach($xpath->evaluate('//section/*') as $article_part){
            if($article_part->tagName == "dnl-image"){
                $article_part->setAttribute("src", wp_get_attachment_url($associations[$article_part->getAttribute("ref")]));
            }
            $node = $output_dom->importNode($article_part, true);
            $output_dom->appendChild($node);
        }

        return $output_dom->saveHTML();
    }

    private function get_tags($categories){
        return array_map(function($v){
            return $v['name'];
        }, array_filter($categories, function($v){
            return in_array($v['type'], array('dnltype:dpasubject', 'dnltype:keyword', 'dnltype:geosubject'));
        }));
    }

    protected function post_process_post($dw_entry, $post){
        //Function to add custom post configurations. Keep data previously added to meta_input (dw_urn, dw_version,...) to ensure updates will still work
        return $post;
    }

    private function add_associations($associations, $parent_urn){
        $association_ids = array();
        $feature_image_id = null;
        foreach($associations as &$a){
            $association_id = $this->add_association($a, $parent_urn);
            
            if($association_id !== False){
                $association_ids[$a["urn"]] = $association_id;                        

                if($a['is_featureimage']){
                    $feature_image_id = $association_id;
                }
            }else{
                error_log('Importing association ' . $a['urn'] . ' failed.');
            }
        }
        return array($association_ids, $feature_image_id);
    }

    private function create_post($prepared_post, $dw_entry, $autopublish){
        list($association_ids, $feature_image_id) = $this->add_associations($dw_entry['associations'], $dw_entry['urn']);

        $prepared_post['post_status'] = $autopublish? 'publish' : 'draft';
        
        $prepared_post['post_content'] = $this->clean_html($dw_entry['article_html'], $dw_entry['dateline'], $association_ids);
        $prepared_post = $this->post_process_post($dw_entry, $prepared_post);

        $resp = wp_insert_post($prepared_post);
        if($resp === 0 | is_wp_error($resp)){
            throw new Exception('Inserting post for urn ' . $dw_entry['urn'] . ' failed.');
        }else{
            if(!empty($feature_image_id)){
                set_post_thumbnail($resp, $feature_image_id);
            }

            if($autopublish){
                wp_publish_post($resp);
            }
            error_log('Inserting post with new id ' . $resp . ' done.');
            return true;
        }
    }

    private function update_post($post_id, $prepared_post, $dw_entry){
        list($association_ids, $feature_image_id) = $this->add_associations($dw_entry['associations'], $dw_entry['urn']);

        set_post_thumbnail($post_id, $feature_image_id);
        $prepared_post['post_content'] = $this->clean_html($dw_entry['article_html'], $dw_entry['dateline'], $association_ids);
        $prepared_post = $this->post_process_post($dw_entry, $prepared_post);
                
        $resp = wp_update_post($prepared_post);
        if($resp === 0 | is_wp_error($resp)){
            throw new Exception('Updating post ' . $prepared_post['ID'] . ' failed.');
        }else{
            if($this->publish){
                wp_publish_post($resp);
            }
            error_log('Updating post ' . $prepared_post['ID'] . ' done.');
            return true;
        }
    }

    private function should_overwrite($post_id){
        if($this->overwrite){
            return true;
        }else{
            $last_id = get_post_meta($post_id, '_edit_last', true );
            $is_update_draft = get_post_meta($post_id, 'dw_is_update_draft');
            return empty($last_id) and empty($is_update_draft);
        }
    }

    public function add_post($dw_entry){
        $post = array(
            'post_title' => $dw_entry['headline'],
            'post_excerpt' => isset($dw_entry['teaser'])? $dw_entry['teaser'] : '',
            'post_date_gmt' => date('Y-m-d H:i:s', strtotime($dw_entry['version_created'])),
            'guid' => $dw_entry['urn'],
            'tags_input' => $this->get_tags($dw_entry['categories']),
            'meta_input' => array(
                'dw_urn' => $dw_entry['urn'],
                'dw_version' => $dw_entry['version'],
                'dw_version_created' => $dw_entry['version_created'],
                'dw_updated' => $dw_entry['updated']
            )
        );
        
        $existing_post_id = $this->get_post_by_meta('dw_urn', $dw_entry['urn']);
        if($existing_post_id != NULL){           
            $post_meta = get_post_meta($existing_post_id);
            
            if(
                $dw_entry['version'] >= $post_meta['dw_version'][0] &&
                strcmp($dw_entry['version_created'], $post_meta['dw_version_created'][0]) >= 0 &&
                strcmp($dw_entry['updated'], $post_meta['dw_updated'][0]) > 0
            ){
                if($this->should_overwrite($existing_post_id)){
                    $post['ID'] = $existing_post_id;
                    error_log('Updating post with id ' . $post['ID']);
                    $this->update_post($post['ID'], $post, $dw_entry);
                }else{
                    $post['post_title'] = "dpa-UPDATE " . $post['post_title'];
                    $post['meta_input']['dw_is_update_draft'] = true;
                    $this->create_post($post, $dw_entry, false);
                }
            }else{
                error_log('No updates to previous version. Skipping');
            }
        }else{
            error_log('Inserting post');
            $this->create_post($post, $dw_entry, $this->publish);
        }
    }

    public function remove_post($entry){
        $existing_post_id = $this->get_post_by_meta('dw_urn', $dw_entry['urn']);
        
        error_log('Deleting post ' . $existing_post_id . ' for urn ' . $dw_entry['urn']);

        wp_trash_post($existing_post_id);

        error_log('Deleting post ' . $existing_post_id . ' done.');
    }
}