<?php
/**
 * This class is responsible for the plugin interface Import Products to Yandex
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
 * @param        
 *
 * @depends                 classes:    IP2Y_Error_Log
 *                                      IP2Y_Api
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

final class IP2Y_Interface_Hoocked {
	/**
	 * This class is responsible for the plugin interface Import Products to Yandex
	 */
	public function __construct() {
		$this->init_hooks();
		$this->init_classes();
	}

	/**
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'save_post', [ $this, 'save_post_product' ], 50, 3 );
		add_action( 'woocommerce_product_duplicate', [ $this, 'product_duplicate' ], 50, 3 );

		// https://wpruse.ru/woocommerce/custom-fields-in-products/
		// https://wpruse.ru/woocommerce/custom-fields-in-variations/
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'added_wc_tabs' ], 10, 1 );
		add_action( 'woocommerce_product_data_panels', [ $this, 'added_tabs_panel_view' ], 10, 1 );

		add_filter( 'ip2y_f_save_if_empty', [ $this, 'flag_save_if_empty' ], 10, 2 );

		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_to_product_sync_info' ], 99, 1 );
		add_action( 'woocommerce_variation_options', [ $this, 'add_to_product_variation_sync_info' ], 99, 3 );
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		return;
	}

	/**
	 * Added WooCommerce tabs. Function for `woocommerce_product_data_tabs` filter-hook.
	 * 
	 * @param array $tabs
	 * 
	 * @return array
	 */
	public function added_wc_tabs( $tabs_arr ) {
		$tabs_arr['ip2y_special_panel'] = [ 
			'label' => __( 'Import Products to Yandex', 'wc-import-yandex' ), // название вкладки
			'target' => 'ip2y_added_wc_tabs', // идентификатор вкладки
			'class' => [ 'hide_if_grouped' ], // классы управления видимостью вкладки в зависимости от типа товара
			'priority' => 70 // приоритет вывода
		];
		return $tabs_arr;
	}

	/**
	 * Added WooCommerce tabs panel. Function for `woocommerce_product_data_panels` action-hook.
	 * 
	 * @return void
	 */
	public function added_tabs_panel_view() {
		global $post; ?>
		<div id="ip2y_added_wc_tabs" class="panel woocommerce_options_panel">
			<?php do_action( 'ip2y_before_options_group', $post ); ?>
			<div class="options_group">
				<h2>
					<strong>
						<?php esc_html_e( 'Individual product settings for export to Yandex Market', 'wc-import-yandex' ); ?>
					</strong>
				</h2>
				<?php
				do_action( 'ip2y_prepend_options_group', $post );

				woocommerce_wp_text_input( [ 
					'id' => '_ip2y_market_sku',
					'label' => __( 'Yandex Market SKU', 'wc-import-yandex' ),
					'description' => __( 'The article of the product on Yandex Market', 'wc-import-yandex' ),
					'type' => 'text'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_ip2y_custom_product_id',
					'label' => __( 'Custom product ID', 'wc-import-yandex' ),
					'type' => 'text'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_ip2y_length',
					'label' => sprintf( '%s (%s)',
						__( 'Package length', 'wc-import-yandex' ),
						__( 'sm', 'wc-import-yandex' )
					),
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => 'any'
					],
					'data_type' => 'decimal'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_ip2y_width',
					'label' => sprintf( '%s (%s)',
						__( 'Package width', 'wc-import-yandex' ),
						__( 'sm', 'wc-import-yandex' ) )
					,
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => 'any'
					],
					'data_type' => 'decimal'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_ip2y_height',
					'label' => sprintf( '%s (%s)',
						__( 'Package height', 'wc-import-yandex' ),
						__( 'sm', 'wc-import-yandex' )
					),
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => 'any'
					],
					'data_type' => 'decimal'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_ip2y_weight',
					'label' => sprintf( '%s (%s)',
						__( 'Package weight', 'wc-import-yandex' ),
						__( 'g', 'wc-import-yandex' )
					),
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => 'any'
					],
					'data_type' => 'decimal'
				] );

				do_action( 'ip2y_append_options_group', $post );
				?>
			</div>
			<?php do_action( 'ip2y_after_options_group', $post ); ?>
		</div>
		<?php
	}

	/**
	 * Сохраняем данные блока, когда пост сохраняется
	 * 
	 * @param int $post_id
	 * @param WP_Post $post Post object
	 * @param bool $update (true — это обновление записи; false — это добавление новой записи)
	 * 
	 * @return void
	 */
	public function save_post_product( $post_id, $post, $update ) {
		if ( $post->post_type !== 'product' ) {
			return; // если это не товар вукомерц
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return; // если это ревизия
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return; // если это автосохранение ничего не делаем
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return; // проверяем права юзера
		}

		$post_meta_arr = [ 
			'_ip2y_market_sku',
			'_ip2y_custom_product_id',
			'_ip2y_length',
			'_ip2y_width',
			'_ip2y_height',
			'_ip2y_weight'
		];
		$post_meta_arr = apply_filters( 'ip2y_f_post_meta_arr', $post_meta_arr );
		if ( ! empty( $post_meta_arr ) ) {
			$this->save_post_meta( $post_meta_arr, $post_id );
		}

		// если экспорт глобально запрещён
		// * пофиксить '1' если будет несколько фидов
		$syncing_with_yandex = common_option_get( 'syncing_with_yandex', false, '1', 'ip2y' );
		if ( $syncing_with_yandex === 'disabled' ) {
			new IP2Y_Error_Log( sprintf( 'NOTICE: Не синхроним post_id = %1$s. %2$s; Файл: %3$s; %4$s: %5$s',
				$post_id,
				'Включён глобальный запрет на импорт!',
				'class-ip2y-interface-hocked.php',
				__( 'line', 'wc-import-yandex' ),
				__LINE__
			) );
			return;
		}

		$api = new IP2Y_Api();
		$answer_arr = $api->product_sync( $post_id );
		if ( true == $answer_arr['status'] ) {
			new IP2Y_Error_Log( sprintf( 'Товара с post_id = %1$s %2$s; Файл: %3$s; %4$s: %5$s',
				$post_id,
				'успешно импортирован',
				'class-ip2y-interface-hocked.php',
				__( 'line', 'wc-import-yandex' ),
				__LINE__
			) );
		} else {
			new IP2Y_Error_Log( sprintf( '%1$s post_id = %2$s; Файл: %3$s; %4$s: %5$s',
				'Ошибка добавления товара с',
				$post_id,
				'class-ip2y-interface-hocked.php',
				__( 'line', 'wc-import-yandex' ),
				__LINE__
			) );
			new IP2Y_Error_Log( $answer_arr );
		}
	}

	/**
	 * Удаляем метаполе о синхронизации с Яндекс Маркет, если мы в админке дублируем товар
	 * Function for `woocommerce_product_duplicate` action-hook.
	 * 
	 * @param WC_Product $duplicate
	 * @param WC_Product $product
	 *
	 * @return void
	 */
	public function product_duplicate( $duplicate, $product ) {
		if ( get_post_meta( $duplicate->get_id(), '_ip2y_market_sku_on_yandex', true ) !== '' ) {
			delete_post_meta( $duplicate->get_id(), '_ip2y_market_sku_on_yandex' );
		}
		if ( get_post_meta( $duplicate->get_id(), '_ip2y_prod_id_on_yandex', true ) !== '' ) {
			delete_post_meta( $duplicate->get_id(), '_ip2y_prod_id_on_yandex' );
		}
	}

	/**
	 * Save post_meta
	 * 
	 * @param array $post_meta_arr
	 * @param int $post_id
	 * 
	 * @return void
	 */
	private function save_post_meta( $post_meta_arr, $post_id ) {
		for ( $i = 0; $i < count( $post_meta_arr ); $i++ ) {
			$meta_name = $post_meta_arr[ $i ];
			if ( isset( $_POST[ $meta_name ] ) ) {
				if ( empty( $_POST[ $meta_name ] ) ) {
					delete_post_meta( $post_id, $meta_name );
				} else {
					update_post_meta( $post_id, $meta_name, sanitize_text_field( $_POST[ $meta_name ] ) );
				}
			}
		}
	}

	/**
	 * Флаг для того, чтобы работало сохранение настроек если мультиселект пуст
	 * 
	 * @param string $save_if_empty
	 * @param array $args_arr
	 * 
	 * @return string
	 */
	public function flag_save_if_empty( $save_if_empty, $args_arr ) {
		// if ( ! empty( $_GET ) && isset( $_GET['tab'] ) && $_GET['tab'] === 'main_tab' ) {
		if ( $args_arr['opt_name'] === 'params_arr' ) {
			$save_if_empty = 'empty_arr';
		}
		// }
		return $save_if_empty;
	}

	/**
	 * Function for `woocommerce_product_options_general_product_data` action-hook.
	 * 
	 * @return void
	 */
	public function add_to_product_sync_info() {
		global $product, $post;

		if ( get_post_meta( $post->ID, '_ip2y_market_sku_on_yandex', true ) !== '' ) {
			$sku_on_yandex = get_post_meta( $post->ID, '_ip2y_market_sku_on_yandex', true );
		} else {
			$sku_on_yandex = '';
		}

		if ( get_post_meta( $post->ID, '_ip2y_prod_id_on_yandex', true ) == '' ) {
			$product_id_on_yandex = '';
		} else {
			$product_id_on_yandex = get_post_meta( $post->ID, '_ip2y_prod_id_on_yandex', true );
		}

		if ( get_post_meta( $post->ID, '_ip2y_prod_archive_status', true ) === 'archived' ) {
			$prod_archive_status = sprintf( ' %s', __( 'The product in the Yandex Market archive', 'wc-import-yandex' ) );
		} else {
			$prod_archive_status = '';
		}

		/**
		 * Выводит в админке ссылку на импортированный и опубликованный в Яндекс Маркет товар. Если же товар просто
		 * импортирован, но не опубликован, то у него нет `prod_id_on_yandex`, хотя есть `sku_on_yandex`
		 * */
		if ( ! empty( $product_id_on_yandex ) ) {
			printf( '</p><p class="form-row form-row-full">%1$s. %2$s: "%3$s", %4$s: "%5$s".%6$s<br/>
			<strong>%7$s</strong>: <a href="%8$s/%9$s?sku=%10$s" target="_blank">%8$s/%9$s?sku=%10$s</a></p>',
				esc_html__( 'The product was imported to Yandex Market', 'wc-import-yandex' ),
				esc_html__( 'His ID on Yandex Market', 'wc-import-yandex' ),
				esc_html__( $product_id_on_yandex ),
				esc_html__( 'SKU', 'wc-import-yandex' ),
				esc_html__( $sku_on_yandex ),
				esc_html__( $prod_archive_status ),
				esc_html__( 'The product on Yandex Market', 'wc-import-yandex' ),
				'https://market.yandex.ru/product',
				esc_attr( get_post_meta( $post->ID, '_ip2y_prod_id_on_yandex', true ) ),
				esc_attr( get_post_meta( $post->ID, '_ip2y_market_sku_on_yandex', true ) )
			);
		}
	}

	/**
	 * Function for `woocommerce_variation_options` action-hook.
	 * 
	 * @param int $i Position in the loop
	 * @param array $variation_data Variation data
	 * @param WP_Post $variation Post data
	 *
	 * @return void
	 */
	function add_to_product_variation_sync_info( $i, $variation_data, $variation ) {
		if ( get_post_meta( $variation->ID, '_ip2y_market_sku_on_yandex', true ) !== '' ) {
			$sku_on_yandex = get_post_meta( $variation->ID, '_ip2y_market_sku_on_yandex', true );
		} else {
			$sku_on_yandex = '';
		}

		if ( get_post_meta( $variation->ID, '_ip2y_prod_id_on_yandex', true ) == '' ) {
			$product_id_on_yandex = '';
		} else {
			$product_id_on_yandex = get_post_meta( $variation->ID, '_ip2y_prod_id_on_yandex', true );
		}

		if ( get_post_meta( $variation->ID, '_ip2y_prod_archive_status', true ) === 'archived' ) {
			$prod_archive_status = sprintf( ' %s', __( 'The product in the Yandex Market archive', 'wc-import-yandex' ) );
		} else {
			$prod_archive_status = '';
		}

		/**
		 * Выводит в админке ссылку на импортированный и опубликованный в Яндекс Маркет товар. Если же товар просто
		 * импортирован, но не опубликован, то у него нет `prod_id_on_yandex`, хотя есть `sku_on_yandex`
		 * */
		if ( ! empty( $product_id_on_yandex ) ) {
			printf( '</p><p class="form-row form-row-full">%1$s. %2$s: "%3$s", %4$s: "%5$s".%6$s<br/>
				<strong>%7$s</strong>: <a href="%8$s/%9$s?sku=%10$s" target="_blank">%8$s/%9$s?sku=%10$s</a></p>',
				esc_html__( 'The variation was imported to Yandex Market', 'wc-import-yandex' ),
				esc_html__( 'His ID on Yandex Market', 'wc-import-yandex' ),
				esc_html__( $product_id_on_yandex ),
				esc_html__( 'SKU', 'wc-import-yandex' ),
				esc_html__( $sku_on_yandex ),
				esc_html__( $prod_archive_status ),
				esc_html__( 'The variation on Yandex Market', 'wc-import-yandex' ),
				'https://market.yandex.ru/product',
				esc_attr( get_post_meta( $variation->ID, '_ip2y_prod_id_on_yandex', true ) ),
				esc_attr( get_post_meta( $variation->ID, '_ip2y_market_sku_on_yandex', true ) )
			);
		}
	}
} // end class IP2Y_Interface_Hoocked