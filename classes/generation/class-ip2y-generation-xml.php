<?php
/**
 * Starts import products
 *
 * @package                 Import Products to Yandex
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 0.3.1 (03-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @return         
 *
 * @depends                 class:      IP2Y_Error_Log
 *                                      IP2Y_Api
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

class IP2Y_Generation_XML {
	/**
	 * Feed ID
	 * @var string
	 */
	protected $feed_id;

	/**
	 * Starts the import
	 * 
	 * @param string|int $feed_id - Required
	 */
	public function __construct( $feed_id ) {
		$this->feed_id = (string) $feed_id;
	}

	/**
	 * Starts importing right now
	 * 
	 * @return void
	 */
	public function run() {
		$syncing_with_yandex = common_option_get( 'syncing_with_yandex', false, $this->get_feed_id(), 'ip2y' );
		if ( $syncing_with_yandex === 'disabled' ) {
			common_option_upd( 'status_sborki', '-1', 'no', $this->get_feed_id(), 'ip2y' );
			new IP2Y_Error_Log( sprintf( 'CABINET № %1$s; %2$s; Файл: %3$s; %4$s: %5$s',
				$this->get_feed_id(),
				'Останавливаем сборку тк включён глобальный запрет на импорт',
				'class-ip2y-generation-xml.php',
				__( 'line', 'wc-import-yandex' ),
				__LINE__
			) );
		}

		$step_export = (int) common_option_get( 'step_export', false, $this->get_feed_id(), 'ip2y' );
		$status_sborki = (int) common_option_get( 'status_sborki', false, $this->get_feed_id(), 'ip2y' );
		// $last_element = (int)common_option_get('last_element', 0, $this->get_feed_id());	
		new IP2Y_Error_Log( sprintf( 'CABINET № %1$s; $status_sborki = %2$s; Файл: %3$s; %4$s: %5$s',
			$this->get_feed_id(),
			$status_sborki,
			'class-ip2y-generation-xml.php',
			__( 'line', 'wc-import-yandex' ),
			__LINE__
		) );

		switch ( $status_sborki ) {
			case -1: // сборка завершена
				new IP2Y_Error_Log( sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; %4$s: %5$s',
					$this->get_feed_id(),
					'case -1',
					'class-ip2y-generation-xml.php',
					__( 'line', 'wc-import-yandex' ),
					__LINE__
				) );
				wp_clear_scheduled_hook( 'ip2y_cron_sborki', [ $this->get_feed_id() ] );
				break;
			case 1: // импорт категорий
				new IP2Y_Error_Log( sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; %4$s: %5$s',
					$this->get_feed_id(),
					'Первый шаг. Импорт категорий',
					'class-ip2y-generation-xml.php',
					__( 'line', 'wc-import-yandex' ),
					__LINE__
				) );
				common_option_upd( 'status_sborki', '2', 'no', $this->get_feed_id(), 'ip2y' );
				break;
			default:
				new IP2Y_Error_Log( sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; %4$s: %5$s',
					$this->get_feed_id(),
					'case default',
					'class-ip2y-generation-xml.php',
					__( 'line', 'wc-import-yandex' ),
					__LINE__
				) );
				if ( $status_sborki == 2 ) {
					$offset = 0;
				} else if ( $status_sborki == 3 ) {
					$offset = $step_export;
				} else {
					$offset = ( ( $status_sborki - 1 ) * $step_export ) - $step_export;
				}
				$args = [ 
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => $step_export,
					'offset' => $offset,
					'relation' => 'AND',
					'orderby' => 'ID'
				];
				$args = apply_filters( 'ip2y_f_query_arg', $args, $this->get_feed_id() );

				new IP2Y_Error_Log( sprintf( 'FEED № %1$s; %2$s $args =>; Файл: %3$s; %4$s: %5$s',
					$this->get_feed_id(),
					'Полная сборка',
					'class-ip2y-generation-xml.php',
					__( 'line', 'wc-import-yandex' ),
					__LINE__
				) );
				new IP2Y_Error_Log( $args );

				$featured_query = new \WP_Query( $args );
				$prod_id_arr = [];
				if ( $featured_query->have_posts() ) {
					for ( $i = 0; $i < count( $featured_query->posts ); $i++ ) {
						$prod_id_arr[ $i ]['ID'] = $featured_query->posts[ $i ]->ID;
						$prod_id_arr[ $i ]['post_modified_gmt'] = $featured_query->posts[ $i ]->post_modified_gmt;
					}
					wp_reset_query(); /* Remember to reset */
					unset( $featured_query ); // чутка освободим память					
					$this->run_api( $prod_id_arr );
					$status_sborki++;
					new IP2Y_Error_Log( sprintf( 'FEED № %1$s; status_sborki увеличен на %2$s и равен %3$s; Файл: %4$s; %5$s: %6$s',
						$this->get_feed_id(),
						$step_export,
						$status_sborki,
						'class-ip2y-generation-xml.php',
						__( 'line', 'wc-import-yandex' ),
						__LINE__
					) );
					common_option_upd( 'status_sborki', $status_sborki, 'no', $this->get_feed_id(), 'ip2y' );
				} else { // если постов нет, останавливаем импорт
					$this->stop();
				}
			// end default
		} // end switch($status_sborki)
		return; // final return from public function phase()
	}

	/**
	 * Summary of stop
	 * 
	 * @return void
	 */
	public function stop() {
		common_option_upd( 'status_sborki', '-1', 'no', $this->get_feed_id(), 'ip2y' );
		wp_clear_scheduled_hook( 'ip2y_cron_sborki', [ $this->get_feed_id() ] );
	}

	/**
	 * Summary of stop
	 * 
	 * @param array $ids_arr
	 * 
	 * @return void
	 */
	public function run_api( $ids_arr ) {
		$api = new IP2Y_Api();
		for ( $i = 0; $i < count( $ids_arr ); $i++ ) {
			$product_id = (int) $ids_arr[ $i ]['ID'];
			$answer_arr = $api->product_sync( $product_id );
			if ( true == $answer_arr['status'] ) {
				new IP2Y_Error_Log( sprintf( 'FEED № %1$s; Товар с $product_id = %2$s успешно импортирован; Файл: %3$s; %4$s: %5$s',
					$this->get_feed_id(),
					$product_id,
					'class-ip2y-generation-xml.php',
					__( 'line', 'wc-import-yandex' ),
					__LINE__
				) );
				new IP2Y_Error_Log( sprintf( 'FEED № %1$s; %2$s $product_id = %3$s %4$s; Файл: %5$s; %6$s: %7$s',
					$this->get_feed_id(),
					'Товар с',
					$product_id,
					'успешно импортирован',
					'class-ip2y-generation-xml.php',
					__( 'line', 'wc-import-yandex' ),
					__LINE__
				) );
			} else {
				new IP2Y_Error_Log( sprintf( 'FEED № %1$s; %2$s $product_id = %3$s $answer_arr =>; Файл: %4$s; %5$s: %6$s',
					$this->get_feed_id(),
					'Ошибка добавления товара с',
					$product_id,
					'class-ip2y-generation-xml.php',
					__( 'line', 'wc-import-yandex' ),
					__LINE__
				) );
				new IP2Y_Error_Log( $answer_arr );
			}
		}
	}

	/**
	 * Проверим, нужно ли отправлять запрос к API при обновлении поста
	 * 
	 * @param mixed $post_id
	 * 
	 * @return bool
	 */
	public function check_ufup( $post_id ) {
		$ip2y_ufup = common_option_get( 'syncing_with_yandex', false, $this->get_feed_id(), 'ip2y' );
		if ( $ip2y_ufup === 'enabled' ) {
			$status_sborki = (int) common_option_get( 'status_sborki', false, $this->get_feed_id(), 'ip2y' );
			if ( $status_sborki > -1 ) { // если идет сборка фида - пропуск
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	protected function get_feed_id() {
		return $this->feed_id;
	}
}