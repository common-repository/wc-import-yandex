<?php defined( 'ABSPATH' ) || exit;
/**
 * Получает ID первого фида. Используется на случай если get-параметр feed_id не указан
 * 
 * @since 0.1.0
 *
 * @return string feed ID or (string)''
 */
function ip2y_get_first_feed_id() {
	$ip2y_settings_arr = univ_option_get( 'ip2y_settings_arr' );
	if ( ! empty( $ip2y_settings_arr ) ) {
		return (string) array_key_first( $ip2y_settings_arr );
	} else {
		return '';
	}
}

/**
 * Получает ID последнего фида
 * 
 * @since 0.1.0
 *
 * @return string feed ID or (string)''
 */
function ip2y_get_last_feed_id() {
	$ip2y_settings_arr = univ_option_get( 'ip2y_settings_arr' );
	if ( ! empty( $ip2y_settings_arr ) ) {
		return (string) array_key_last( $ip2y_settings_arr );
	} else {
		return ip2y_get_first_feed_id();
	}
}