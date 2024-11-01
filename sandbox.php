<?php defined( 'ABSPATH' ) || exit;
/**
 * Sandbox function
 * 
 * @since	0.1.0
 * @version 0.4.0 (07-06-2024)
 *
 * @return	void
 */
function ip2y_run_sandbox() {
	$x = true; // установите true, чтобы использовать песочницу
	if ( false === $x ) {
		printf( '%s<br/>',
			esc_html__( 'The sandbox is working. The result will appear below', 'wc-import-yandex' )
		);
		$time_start = microtime( true );
		/* вставьте ваш код ниже */
		// Example:
		// $product = wc_get_product(8303);
		// echo $product->get_price();
		// $response = wp_remote_request( 'https://api.partner.market.yandex.ru/businesses/12345678/offers/stock', [ 
		//	'method' => 'PUT',
		//	'headers' => [ 
		//		'Content-Type' => 'application/json',
		//		'Cache-Control' => 'no-cache',
		//		'Authorization' => 'Bearer y0_A*******************************bg'
		//	],
		//	'body'=> '{"skus":[{"sku":"test-214","items":[{"count":2}]}]}'
		// ] );

		/* дальше не редактируем */
		$time_end = microtime( true );
		$time = $time_end - $time_start;
		printf( '<br/>%s<br/>%s %d %s',
			esc_html__( __( 'The sandbox is working correctly', 'wc-import-yandex' ) ),
			esc_html__( __( 'The execution time of the test script was', 'wc-import-yandex' ) ),
			esc_html( $time ),
			esc_html__( __( 'seconds', 'wc-import-yandex' ) )
		);
	} else {
		printf( '%s sanbox.php',
			esc_html__( 'The sandbox is not active. To activate, edit the file', 'wc-import-yandex' )
		);
	}
}