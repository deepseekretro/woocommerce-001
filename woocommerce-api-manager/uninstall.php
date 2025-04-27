<?php

defined( 'WP_UNINSTALL_PLUGIN' ) or exit;

wp_unschedule_hook( 'wc_am_weekly_event' );

global $wpdb;

if ( ! $wpdb ) {
	return;
}

// drop tables
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wc_am_secure_hash' );
