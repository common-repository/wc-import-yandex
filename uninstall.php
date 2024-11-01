<?php defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
if ( is_multisite() ) {
	delete_blog_option( get_current_blog_id(), 'ip2y_version' );
	delete_blog_option( get_current_blog_id(), 'ip2y_keeplogs' );
	delete_blog_option( get_current_blog_id(), 'ip2y_disable_notices' );
	delete_blog_option( get_current_blog_id(), 'ip2y_groups_content' );

	delete_blog_option( get_current_blog_id(), 'ip2y_settings_arr' );
} else {
	delete_option( 'ip2y_version' );
	delete_option( 'ip2y_keeplogs' );
	delete_option( 'ip2y_disable_notices' );
	delete_option( 'ip2y_groups_content' );

	delete_option( 'ip2y_settings_arr' );
}