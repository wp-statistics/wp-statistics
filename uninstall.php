<?php
// if not called from WordPress exit
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
	exit();
}

// By default, WP Statistics leaves all data in the database, however a user can select to
// remove it, in which case the wp_statistics_removal option is set and we should remove that
// here in case the user wants to re-install the plugin at some point.
delete_option('wp_statistics_removal');
