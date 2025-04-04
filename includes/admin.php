<?php
/**  -*- coding: utf-8 -*-
*
* Copyright 2024, dpa-IT Services GmbH
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

class AdminPage{
    public function __construct(){
        add_action('admin_menu', array($this, 'add_options_page'));        
        add_action("admin_init", array($this, "add_options"));
    }

    public function add_options_page(){
        add_submenu_page(
            'options-general.php',
            'dpa-digitalwires-Import',
            'dpa-digitalwires-Import',
            'manage_options',
            'dpa-digitalwires',
            array(&$this, 'admin_page_html')
        );
    }

    public function add_options(){
        $cur_settings = get_option("dpa-digitalwires");
        
        add_settings_section(
            "dpa-digitalwires-section",
            "dpa-digitalwires-Import",
            array(&$this, "admin_page_description"),
            "dpa-digitalwires"
        );

        add_settings_field(
            "dpa-digitalwires[dw_endpoint]",
            "wireQ endpoint",
            array(&$this, "form_endpoint_html"),
            "dpa-digitalwires",
            "dpa-digitalwires-section",
            $cur_settings["dw_endpoint"]
        );

        add_settings_field(
            "dpa-digitalwires[dw_cron_time]",
            "Query cycle in minutes",
            array(&$this, "form_cron_time_html"),
            "dpa-digitalwires",
            "dpa-digitalwires-section",
            $cur_settings["dw_cron_time"]
        );

        add_settings_field(
            "dpa-digitalwires[dw_active]",
            "Activated",
            array(&$this, "form_active_html"),
            "dpa-digitalwires",
            "dpa-digitalwires-section",
            $cur_settings["dw_active"]
        );

        add_settings_field(
            "dpa-digitalwires[dw_publish]",
            "Publish articles directly",
            array(&$this, "form_publish_html"),
            "dpa-digitalwires",
            "dpa-digitalwires-section",
            $cur_settings["dw_publish"]
        );

        add_settings_field(
            "dpa-digitalwires[dw_overwrite]",
            "DPA updates may overwrite articles edited in Wordpress",
            array(&$this, "form_overwrite_html"),
            "dpa-digitalwires",
            "dpa-digitalwires-section",
            $cur_settings["dw_overwrite"]
        );
    }

    public function admin_page_description(){
        echo '<p>First, register on the <a href="https://api-portal.dpa-newslab.com/" target="_blank">dpa-API-Portal</a>, to activate your own wireQ API endpoint and have your WordPress instance supplied with content.</p>';
    }

    public function form_endpoint_html($cur_val){
        echo '<input type="text" name="dpa-digitalwires[dw_endpoint]" id="dw_endpoint" value="' . $cur_val . '">';
    }

    public function form_cron_time_html($cur_val){
        if(empty($cur_val)){
            $cur_val = 5;
        }
        echo '<input type="number" min="3" max="60" name="dpa-digitalwires[dw_cron_time]" id="dw_cron_time" value="' . $cur_val . '">';
    }

    public function form_active_html($cur_val){
        echo '<input type="checkbox" name="dpa-digitalwires[dw_active]" id="dw_active" ' . checked(true, $cur_val, false) . ' />';
    }

    public function form_publish_html($cur_val){
        echo '<input type="checkbox" name="dpa-digitalwires[dw_publish]" id="dw_publish" ' . checked(true, $cur_val,  false) . ' />';
    }

    public function form_overwrite_html($cur_val){
        echo '<input type="checkbox" name="dpa-digitalwires[dw_overwrite]" id="dw_overwrite" ' . checked(true, $cur_val,  false) . ' />';
    }

    public function admin_page_html(){
        // check user capabilities
        $dw_stats = get_option('dw_stats')
        ?>
            <div class="wrap">
                <form method="post" action="options.php">
                    <?php settings_fields("dpa-digitalwires") ?>
                    <?php do_settings_sections('dpa-digitalwires') ?>
                    <?php submit_button(); ?>
                </form>
                <code style="display: block;width: fit-content;">
                    Last Query: <?php echo $dw_stats['last_run']; ?>
                    <?php if(isset($dw_stats['last_import_title'])){ ?>
                        <br><br>
                        Last imported article: <?php echo $dw_stats['last_import_title'] ?> (<?php echo $dw_stats['last_import_urn'] ?>, <?php echo $dw_stats['last_import_timestamp'] ?>)
                    <?php } ?>
                    <?php if(isset($dw_stats['last_exception_message'])){ ?>
                        <br><br>
                        Last import error: <?php echo $dw_stats['last_exception_message'] ?> (occurred at <?php echo $dw_stats['last_exception_urn'] ?>, <?php echo $dw_stats['last_exception_timestamp'] ?>)
                    <?php } ?>
                </code>
            </div>
        <?php
    }
}


?>
