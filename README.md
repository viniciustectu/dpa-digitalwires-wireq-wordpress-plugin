# Import dpa-digitalwires via wireQ-API to Wordpress CMS

This repository shows how to import dpa-articles to Wordpress via a plugin which is based on the wireQ-API.

## Setup

- Copy the plugin folder to `wp-content/plugins/dpa-digitalwires-to-wordpress`
- Activate plugin in the WP-admin-dashboard
- Add your wireQ-Endpoint to the plugin's settings and tick the "active"-checkbox

## Wordpress cronjobs

This plugin uses WP-Cron which has some drawbacks (e.g. to work properly, regular site visits are required). 

To setup a real cronjob use your system's task scheduler to trigger wp-cron.php as specified [in the Wordpress-documentation](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/)