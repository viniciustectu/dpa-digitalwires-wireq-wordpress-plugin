# Import dpa-digitalwires via wireQ-API to Wordpress CMS

This repository shows how to import dpa-articles to Wordpress via a plugin which is based on the wireQ-API.

## Setup

- Copy the plugin folder to `wp-content/plugins/dpa-digitalwires-to-wordpress`
- Activate plugin in the WP-admin-dashboard
- Add your wireQ-Endpoint to the plugin's settings and tick the "active"-checkbox

**[Details](https://github.com/dpa-newslab/dpa-digitalwires-wireq-wordpress-plugin/wiki/Plugin-Setup)**

## Wordpress cronjobs

This plugin uses WP-Cron which has some drawbacks (e.g. to work properly, regular site visits are required). 

To setup a real cronjob use your system's task scheduler to trigger wp-cron.php as specified [in the Wordpress-documentation](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/).

## Embeds

The plugin imports Web Components contained in dpa-articles as HTML. Therefore needed HTML-tags are allowed. To render the components you have to define them. This could be done by loading [dnl_embeds.js](https://github.com/dpa-newslab/dpa-digitalwires-embeds) in your template. You can directly use the [pre-built dnl_embeds.js](https://github.com/dpa-newslab/dpa-digitalwires-embeds/blob/main/dist/js/dnl_embeds.js). For details on how to add scripts to a theme please refer to the [Wordpress-documentation](https://developer.wordpress.org/themes/basics/including-css-javascript/#enqueuing-scripts-and-styles).

## Customization

To customize the imported article use the `dpa_digitalwires_post_process_post`-filter, e.g. by adding the following snippet to the `functions.php` of your theme: 

```
function modify_digitalwires_post($value, $dw_entry){
	$value["post_title"] = $value["post_title"] . " (dpa)";
	return $value;
}
add_filter("dpa_digitalwires_post_process_post", "modify_digitalwires_post", 10, 2);
```

This will add ` (dpa)` to the title of every imported post.

For customization reaching further than a modification of the inserted post you might want to modify the plugin code directly. The [post_process_post](https://github.com/dpa-newslab/dpa-digitalwires-wireq-wordpress-plugin/blob/main/includes/converter.php#L243)-function might be a good starting point.