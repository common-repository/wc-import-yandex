<?php
/**
 * The class will help you connect your store to Yandex Market using Yandex Market API
 *
 * @package                 Import Products to Yandex
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 0.5.0 (21-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    IP2Y_Api_Helper_Simple
 *                                      IP2Y_Api_Helper_Variable
 *                                      IP2Y_Api_Helper_External
 *                                      IP2Y_Error_Log
 *                          trait       
 *                          methods:    
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

final class IP2Y_Api_Helper {
	/**
	 * Feed ID
	 * @var string
	 */
	protected $feed_id;
	/**
	 * @var WC_Product
	 */
	protected $product;
	/**
	 * Product SKU array
	 * @var array
	 */
	protected $shop_sku_arr = [];
	/**
	 * Product data array
	 * @var array
	 */
	protected $product_data_arr = [];
	/**
	 * Product SKU list array
	 * @var array
	 */
	protected $product_sku_list_arr = [];
	/**
	 * Skip reasons array
	 * @var array
	 */
	protected $skip_reasons_arr = [];

	/**
	 * The class will help you connect your store to Yandex Market using Yandex Market API
	 */
	public function __construct() {
		$this->feed_id = '1';
	}

	/**
	 * Summary of set_product_data
	 * 
	 * @param int $product_id
	 * @param string $actions
	 * 
	 * @return void
	 */
	public function set_product_data( $product_id, $actions ) {
		new IP2Y_Error_Log(
			sprintf( 'FEED № %1$s; %2$s %3$s, actions = %4$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				'Устанавливаем данные для товара',
				$product_id,
				$actions,
				'class-ip2y-api-helper.php',
				__LINE__
			)
		);
		$arhived_list_arr = [];

		$this->product = wc_get_product( $product_id );
		if ( null == $this->get_product() ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'There is no product with this ID', 'wc-import-yandex' ),
				'post_id' => $product_id,
				'file' => 'class-ip2y-api-helper.php',
				'line' => __LINE__
			] );
			return;
		}

		if ( $this->get_product()->is_type( 'simple' ) ) {
			$obj = new IP2Y_Api_Helper_Simple( $this->get_product(), $actions, $this->get_feed_id() );
			$this->set_helper_result( $obj, $product_id );
			unset( $obj );
		} else if ( $this->get_product()->is_type( 'variable' ) ) {
			$variations_arr = $this->get_product()->get_available_variations();
			$variation_count = count( $variations_arr );
			for ( $i = 0; $i < $variation_count; $i++ ) {
				$offer_id = $variations_arr[ $i ]['variation_id'];
				$offer = new WC_Product_Variation( $offer_id ); // получим вариацию

				$obj = new IP2Y_Api_Helper_Variable( $this->get_product(), $actions, $offer, $variation_count, $this->get_feed_id() );
				$this->set_helper_result( $obj, $offer_id );
				unset( $obj );
			}
			// echo get_array_as_string($this->get_result(), '<br/>');
		} else {
			$this->add_skip_reason( [ 
				'reason' => __( 'The product is not simple or variable', 'wc-import-yandex' ),
				'post_id' => $product_id,
				'file' => 'class-ip2y-api-helper.php',
				'line' => __LINE__
			] );
			return;
		}
	}

	/**
	 * Set helper result
	 * 
	 * @return void
	 */
	public function set_helper_result( $obj, $post_id_on_wp ) {
		if ( ! empty( $obj->get_skip_reasons_arr() ) ) {
			foreach ( $obj->get_skip_reasons_arr() as $value ) {
				array_push( $this->skip_reasons_arr, $value );
			}
		}
		if ( ! empty( $obj->get_result() ) ) {
			array_push( $this->product_data_arr, $obj->get_result() );
			$flag = true;
		} else {
			$flag = false;
		}

		array_push( $this->product_sku_list_arr,
			[ 
				'your_sku_on_yandex' => $obj->get_shop_sku(),
				'post_id_on_wp' => $post_id_on_wp,
				'have_get_result' => $flag
			]
		);
	}

	/**
	 * Summary of set_product_exists
	 * 
	 * @param int $product_id
	 * @param array $data_arr
	 * 
	 * @return void
	 */
	public function set_product_exists( $product_id, $data_arr ) {
		if ( isset( $data_arr['product_id_on_yandex'] ) ) {
			// marketModelId
			if ( empty( $data_arr['product_id_on_yandex'] ) ) {
				delete_post_meta( $product_id, '_ip2y_prod_id_on_yandex' );
			} else {
				update_post_meta( $product_id, '_ip2y_prod_id_on_yandex', sanitize_text_field( $data_arr['product_id_on_yandex'] ) );
			}
		}
		if ( isset( $data_arr['market_sku_on_yandex'] ) ) {
			// marketSku
			if ( empty( $data_arr['market_sku_on_yandex'] ) ) {
				delete_post_meta( $product_id, '_ip2y_market_sku_on_yandex' );
			} else {
				update_post_meta( $product_id, '_ip2y_market_sku_on_yandex', sanitize_text_field( $data_arr['market_sku_on_yandex'] ) );
			}
		}
		if ( isset( $data_arr['market_category_id_on_yandex'] ) ) {
			// marketCategoryId
			if ( empty( $data_arr['market_category_id_on_yandex'] ) ) {
				delete_post_meta( $product_id, '_ip2y_market_category_id_on_yandex' );
			} else {
				update_post_meta( $product_id, '_ip2y_market_category_id_on_yandex', sanitize_text_field( $data_arr['market_category_id_on_yandex'] ) );
			}
		}
		if ( isset( $data_arr['product_archive_status'] ) ) {
			if ( empty( $data_arr['product_archive_status'] ) ) {
				delete_post_meta( $product_id, '_ip2y_prod_archive_status' );
			} else {
				update_post_meta( $product_id, '_ip2y_prod_archive_status', sanitize_text_field( $data_arr['product_archive_status'] ) );
			}
		}
		return;
	}

	/**
	 * Summary of set_skip_reasons_arr
	 * 
	 * @param mixed $v
	 * 
	 * @return void
	 */
	public function set_skip_reasons_arr( $v ) {
		$this->skip_reasons_arr[] = $v; // ? может лучше так: array_push( $this->skip_reasons_arr, $v );
	}

	/**
	 * Summary of get_skip_reasons_arr
	 * 
	 * @return array
	 */
	public function get_skip_reasons_arr() {
		return $this->skip_reasons_arr;
	}

	/**
	 * Summary of add_skip_reason
	 * 
	 * @param array $reason
	 * 
	 * @return void
	 */
	protected function add_skip_reason( $reason ) {
		if ( isset( $reason['offer_id'] ) ) {
			$reason_string = sprintf(
				'CABINET № %1$s; Вариация (postId = %2$s, offer_id = %3$s) пропущена. Причина: %4$s; Файл: %5$s; %6$s: %7$s',
				$this->feed_id,
				$reason['post_id'],
				$reason['offer_id'],
				$reason['reason'],
				$reason['file'],
				__( 'line', 'wc-import-yandex' ),
				$reason['line']
			);
		} else {
			$reason_string = sprintf(
				'CABINET № %1$s; Товар с postId = %2$s пропущен. Причина: %3$s; Файл: %4$s; %5$s: %6$s',
				$this->feed_id,
				$reason['post_id'],
				$reason['reason'],
				$reason['file'],
				__( 'line', 'wc-import-yandex' ),
				$reason['line']
			);
		}

		$this->set_skip_reasons_arr( $reason_string );
		new IP2Y_Error_Log( $reason_string );
	}

	/* Getters */

	/**
	 * Get product
	 * 
	 * @return WC_Product
	 */
	public function get_product() {
		return $this->product;
	}

	/**
	 * Get shop SKUs array
	 * 
	 * @return array
	 */
	public function get_shop_sku_arr() {
		return $this->shop_sku_arr;
	}

	/**
	 * Get product data array
	 * 
	 * @return array
	 */
	public function get_product_data() {
		return $this->product_data_arr;
	}

	/**
	 * Get feed ID
	 * 
	 * @return int|string
	 */
	public function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get result
	 * 
	 * @return array
	 */
	public function get_result() {
		return $this->product_data_arr;
	}

	/**
	 * Get product SKU list array
	 * 
	 * @return array
	 */
	public function get_product_sku_list_arr() {
		return $this->product_sku_list_arr;
	}
}