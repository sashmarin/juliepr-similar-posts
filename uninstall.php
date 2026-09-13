<?php
/**
 * Removes Juliepr Similar Posts data when the user has opted in to cleanup.
 *
 * @package Juliepr_Similar_Posts
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$option_name = 'juliepr_similar_posts_delete_data_on_uninstall';
$meta_key    = '_juliepr_similar_posts_selected_post_ids';

if ( ! get_option( $option_name, false ) ) {
	return;
}

global $wpdb;

$wpdb->delete(
	$wpdb->postmeta,
	array( 'meta_key' => $meta_key ),
	array( '%s' )
);

delete_option( $option_name );
