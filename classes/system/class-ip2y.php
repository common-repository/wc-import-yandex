<?php
/**
 * The main class of the plugin Import Products to Yandex
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
 * @param         
 *
 * @depends                 classes:	IP2Y_Data_Arr
 *                                      IP2Y_Settings_Page
 *                                      IP2Y_Debug_Page
 *                                      IP2Y_Extensions_Page
 *                                      IP2Y_Error_Log
 *                                      IP2Y_Generation_XML
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                          constants:  IP2Y_PLUGIN_VERSION
 *                                      IP2Y_PLUGIN_BASENAME
 *                                      IP2Y_PLUGIN_DIR_URL
 */
defined( 'ABSPATH' ) || exit;

final class IP2Y {
	/**
	 * Plugin version
	 * @var string
	 */
	private $plugin_version = IP2Y_PLUGIN_VERSION; // 0.1.0

	protected static $instance;
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Срабатывает при активации плагина (вызывается единожды)
	 * 
	 * @return void
	 */
	public static function on_activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		if ( is_multisite() ) {
			add_blog_option( get_current_blog_id(), 'ip2y_keeplogs', '' );
			add_blog_option( get_current_blog_id(), 'ip2y_disable_notices', '' );
			add_blog_option( get_current_blog_id(), 'ip2y_group_content', '' );

			add_blog_option( get_current_blog_id(), 'ip2y_settings_arr', [] );
			// add_blog_option(get_current_blog_id(), 'ip2y_registered_groups_arr', [ ]);
		} else {
			add_option( 'ip2y_keeplogs', '' );
			add_option( 'ip2y_disable_notices', '' );
			add_option( 'ip2y_group_content', '' );

			add_option( 'ip2y_settings_arr', [] );
			// add_option('ip2y_registered_groups_arr', [ ]);
		}
	}

	/**
	 * Срабатывает при отключении плагина (вызывается единожды)
	 * 
	 * @return void
	 */
	public static function on_deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		wp_clear_scheduled_hook( 'ip2y_cron_period', [ '1' ] );
		wp_clear_scheduled_hook( 'ip2y_cron_sborki', [ '1' ] );
	}

	/**
	 * The main class of the plugin Import Products to Yandex
	 */
	public function __construct() {
		$this->check_options_upd(); // проверим, нужны ли обновления опций плагина
		$this->init_classes();
		$this->init_hooks(); // подключим хуки
	}

	/**
	 * Checking whether the plugin options need to be updated
	 * 
	 * @return void
	 */
	public function check_options_upd() {
		if ( false == common_option_get( 'ip2y_version' ) ) { // это первая установка
			$ip2y_data_arr_obj = new IP2Y_Data_Arr();
			$opts_arr = $ip2y_data_arr_obj->get_opts_name_and_def_date( 'all' ); // массив дефолтных настроек
			common_option_upd( 'ip2y_settings_arr', $opts_arr, 'no', '1' ); // пишем все настройки
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'ip2y_version', $this->plugin_version );
			} else {
				update_option( 'ip2y_version', $this->plugin_version );
			}
		} else {
			$this->set_new_options();
		}
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		new IP2Y_Interface_Hoocked();
		new ICPD_Feedback( [ 
			'plugin_name' => 'Import Products to Yandex',
			'plugin_version' => $this->get_plugin_version(),
			'logs_url' => IP2Y_PLUGIN_UPLOADS_DIR_URL . '/plugin.log',
			'pref' => 'ip2y'
		] );
		new IP2Y_Api();
		new ICPD_Promo( 'ip2y' );
		return;
	}

	/**
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_init', [ $this, 'listen_submits' ], 9 ); // ещё можно слушать чуть раньше на wp_loaded
		add_action( 'admin_init', function () {
			wp_register_style( 'ip2y-admin-css', IP2Y_PLUGIN_DIR_URL . 'assets/css/ip2y-style.css' );
		}, 9999 ); // Регаем стили только для страницы настроек плагина
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ], 10, 1 );
		add_action( 'admin_enqueue_scripts', [ &$this, 'reg_script' ] ); // правильно регаем скрипты в админку

		add_action( 'ip2y_cron_sborki', [ $this, 'do_this_seventy_sec' ], 10, 1 );
		add_action( 'ip2y_cron_period', [ $this, 'do_this_event' ], 10, 1 );
		add_filter( 'cron_schedules', [ $this, 'add_cron_intervals' ], 10, 1 );

		add_filter( 'plugin_action_links', [ $this, 'add_plugin_action_links' ], 10, 2 );

		add_filter( 'ip2y_f_external_description', [ $this, 'add_product_link_to_desc' ], 9, 3 );
		add_filter( 'ip2y_f_simple_description', [ $this, 'add_product_link_to_desc' ], 9, 3 );
		add_filter( 'ip2y_f_variable_description', [ $this, 'add_product_link_to_desc' ], 9, 3 );
	}

	/**
	 * Summary of reg_script
	 * 
	 * @return void
	 */
	public function reg_script() {
		// правильно регаем скрипты в админку через промежуточную функцию
		// https://daext.com/blog/how-to-add-select2-in-wordpress/
		wp_enqueue_script( 'select2-js', IP2Y_PLUGIN_DIR_URL . 'assets/js/select2.min.js', [ 'jquery' ] );
		wp_enqueue_script( 'ip2y-select2-init', IP2Y_PLUGIN_DIR_URL . 'assets/js/select2-init.js', [ 'jquery' ] );
		// wp_enqueue_style( 'ip2y-select2-css', IP2Y_PLUGIN_DIR_URL . 'assets/css/select2.min.css', [] );
	}

	/**
	 * Listen submits. Function for `admin_init` action-hook.
	 * 
	 * @return void
	 */
	public function listen_submits() {
		do_action( 'ip2y_listen_submits' );

		if ( isset( $_REQUEST['ip2y_submit_action'] ) ) {
			$message = __( 'Updated', 'wc-import-yandex' );
			$class = 'notice-success';

			add_action( 'admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}

		$status_sborki = (int) common_option_get( 'status_sborki', false, '1', 'ip2y' );
		$step_export = (int) common_option_get( 'step_export', false, '1', 'ip2y' );

		if ( $status_sborki == 1 ) {
			$message = sprintf( 'IP2Y: %1$s. %2$s: 1. %3$s',
				__( 'Import products is running', 'wc-import-yandex' ),
				__( 'Step', 'wc-import-yandex' ),
				__( 'Importing a list of categories', 'wc-import-yandex' )
			);
		} else if ( $status_sborki > 1 ) {
			$message = sprintf( 'IP2Y: %1$s. %2$s: 2. %3$s %4$s',
				__( 'Import products is running', 'wc-import-yandex' ),
				__( 'Step', 'wc-import-yandex' ),
				__( 'Processed products', 'wc-import-yandex' ),
				$status_sborki * $step_export
			);
		} else {
			$message = '';
		}

		if ( ! empty( $message ) ) {
			$class = 'notice-success';
			add_action( 'admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}
	}

	/**
	 * Add items to admin menu. Function for `admin_menu` action-hook.
	 * 
	 * @param string $context Empty context
	 * 
	 * @return void
	 */
	public function add_admin_menu( $context ) {
		$page_suffix = add_menu_page(
			null,
			'Import Products to Yandex',
			'manage_woocommerce',
			'ip2y-import',
			[ $this, 'get_plugin_settings_page' ],
			'dashicons-redo',
			51
		);
		// создаём хук, чтобы стили выводились только на странице настроек
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_enqueue_style_css' ] );

		$page_suffix = add_submenu_page(
			'ip2y-import',
			__( 'Debug', 'wc-import-yandex' ),
			__( 'Debug page', 'wc-import-yandex' ),
			'manage_woocommerce',
			'ip2y-debug',
			[ $this, 'get_debug_page' ]
		);
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_enqueue_style_css' ] );

		$page_suffix = add_submenu_page(
			'ip2y-import',
			__( 'Add Extensions', 'wc-import-yandex' ),
			__( 'Add Extensions', 'wc-import-yandex' ),
			'manage_woocommerce',
			'ip2y-extensions',
			[ $this, 'get_extensions_page' ]
		);
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'admin_enqueue_style_css' ] );
	}

	/**
	 * Вывод страницы настроек плагина
	 * 
	 * @return void
	 */
	public function get_plugin_settings_page() {
		new IP2Y_Settings_Page();
		return;
	}

	/**
	 * Вывод страницы отладки плагина
	 * 
	 * @return void
	 */
	public function get_debug_page() {
		new IP2Y_Debug_Page();
		return;
	}

	/**
	 * Вывод страницы расширений плагина
	 * 
	 * @return void
	 */
	public function get_extensions_page() {
		new IP2Y_Extensions_Page();
		return;
	}

	/**
	 * Get plugin version
	 * 
	 * @return string
	 */
	public function get_plugin_version() {
		if ( is_multisite() ) {
			$v = get_blog_option( get_current_blog_id(), 'ip2y_version' );
		} else {
			$v = get_option( 'ip2y_version' );
		}
		return $v;
	}

	/**
	 * of admin_enqueue_style_css
	 * 
	 * @return void
	 */
	public function admin_enqueue_style_css() {
		wp_enqueue_style( 'ip2y-admin-css' ); // Ставим css-файл в очередь на вывод
	}

	/**
	 * of do_this_seventy_sec
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function do_this_seventy_sec( $feed_id ) {
		// условие исправляет возможные ошибки и повторное создание удаленного фида
		if ( $feed_id === (int) 1 || $feed_id === (float) 1 ) {
			$feed_id = (string) $feed_id;
		}
		if ( $feed_id == '' ) {
			common_option_upd( 'status_sborki', '-1', 'no', $feed_id, 'ip2y' );
			wp_clear_scheduled_hook( 'ip2y_cron_sborki', [ $feed_id ] );
			wp_clear_scheduled_hook( 'ip2y_cron_period', [ $feed_id ] );
			return;
		}

		new IP2Y_Error_Log( 'Cтартовала крон-задача do_this_seventy_sec' );
		$generation = new IP2Y_Generation_XML( $feed_id ); // делаем что-либо каждые 70 сек
		$generation->run();
	}

	/**
	 * of do_this_seventy_sec
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function do_this_event( $feed_id ) {
		// условие исправляет возможные ошибки и повторное создание удаленного фида
		if ( $feed_id === (int) 1 || $feed_id === (float) 1 ) {
			$feed_id = (string) $feed_id;
		}
		if ( $feed_id == '' ) {
			common_option_upd( 'status_sborki', '-1', 'no', $feed_id, 'ip2y' );
			wp_clear_scheduled_hook( 'ip2y_cron_sborki', [ $feed_id ] );
			wp_clear_scheduled_hook( 'ip2y_cron_period', [ $feed_id ] );
			return;
		}

		new IP2Y_Error_Log( sprintf( 'CABINET № %1$s; %2$s; Файл: %3$s; %4$s: %5$s',
			$feed_id,
			'Крон-функция do_this_event включена согласно интервала',
			'class-ip2y.php',
			__( 'line', 'wc-import-yandex' ),
			__LINE__
		) );

		common_option_upd( 'status_sborki', '1', 'no', $feed_id, 'ip2y' );
		wp_clear_scheduled_hook( 'ip2y_cron_sborki', [ $feed_id ] );

		// Возвращает nul/false. null когда планирование завершено. false в случае неудачи.
		$res = wp_schedule_event( time(), 'seventy_sec', 'ip2y_cron_sborki', [ $feed_id ] );
		if ( false === $res ) {
			new IP2Y_Error_Log( sprintf( 'CABINET № %1$s; %2$s; Файл: %3$s; %4$s: %5$s',
				$feed_id,
				'ERROR: Не удалось запланировань CRON seventy_sec',
				'class-ip2y.php',
				__( 'line', 'wc-import-yandex' ),
				__LINE__
			) );
		} else {
			new IP2Y_Error_Log( sprintf( 'CABINET № %1$s; %2$s; Файл: %3$s; %4$s: %5$s',
				$feed_id,
				'CRON seventy_sec успешно запланирован',
				'class-ip2y.php',
				__( 'line', 'wc-import-yandex' ),
				__LINE__
			) );
		}
	}

	/**
	 * Add cron intervals to WordPress. Function for `cron_schedules` filter-hook.
	 * 
	 * @param array $new_schedules An array of non-default cron schedules keyed by the schedule name.
	 * 
	 * @return array
	 */
	public function add_cron_intervals( $new_schedules ) {
		$new_schedules['seventy_sec'] = [ 
			'interval' => 70,
			'display' => __( '70 seconds', 'wc-import-yandex' )
		];
		$new_schedules['five_min'] = [ 
			'interval' => 300,
			'display' => __( '5 minutes', 'wc-import-yandex' )
		];
		$new_schedules['three_hours'] = [ 
			'interval' => 10800,
			'display' => __( '3 hours', 'wc-import-yandex' )
		];
		$new_schedules['six_hours'] = [ 
			'interval' => 21600,
			'display' => __( '6 hours', 'wc-import-yandex' )
		];
		$new_schedules['week'] = [ 
			'interval' => 604800,
			'display' => __( '1 week', 'wc-import-yandex' )
		];
		return $new_schedules;
	}

	/**
	 * of do_this_seventy_sec
	 * 
	 * @param string[] $actions An array of plugin action links. By default this can include 'activate', 'deactivate', and 'delete'
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory
	 * 
	 * @return string[]
	 */
	public function add_plugin_action_links( $actions, $plugin_file ) {
		if ( false === strpos( $plugin_file, IP2Y_PLUGIN_BASENAME ) ) { // проверка, что у нас текущий плагин
			return $actions;
		}

		$settings_link = sprintf( '<a style="%s" href="/wp-admin/admin.php?page=%s">%s</a>',
			'color: green; font-weight: 700;',
			'ip2y-extensions',
			__( 'More features', 'wc-import-yandex' )
		);
		array_unshift( $actions, $settings_link );

		$settings_link = sprintf( '<a href="/wp-admin/admin.php?page=%s">%s</a>',
			'ip2y-import',
			__( 'Settings', 'wc-import-yandex' )
		);
		array_unshift( $actions, $settings_link );
		return $actions;
	}

	/**
	 * Add to the product desc
	 * 
	 * @param string $desc_val - Required
	 * @param array $args_arr - Required
	 * @param string $feed_id - Required
	 * 
	 * @return string
	 */
	public function add_product_link_to_desc( $desc_val, $args_arr, $feed_id ) {
		$add_product_text_to_desc = common_option_get( 'add_product_text_to_desc', false, $feed_id, 'ip2y' );
		$text_product_text_to_desc = common_option_get( 'text_product_text_to_desc', false, $feed_id, 'ip2y' );

		switch ( $add_product_text_to_desc ) {
			case 'before':
				$desc_val = sprintf( '%1$s %2$s %3$s',
					$text_product_text_to_desc,
					PHP_EOL,
					$desc_val
				);
				break;
			case 'after':
				$desc_val = sprintf( '%1$s %2$s %3$s',
					$desc_val,
					PHP_EOL,
					$text_product_text_to_desc
				);
				break;
		}

		$product_link_to_desc = common_option_get( 'add_product_link_to_desc', false, $feed_id, 'ip2y' );
		$text_product_link_to_desc = common_option_get( 'text_product_link_to_desc', false, $feed_id, 'ip2y' );
		if ( empty( $text_product_link_to_desc ) ) {
			$text_product_link_to_desc = 'Ссылка на товар:';
		}

		switch ( $product_link_to_desc ) {
			case 'beginning':
				$desc_val = sprintf( '%1$s %2$s %3$s %4$s',
					$text_product_link_to_desc,
					get_permalink( $args_arr['product']->get_id() ),
					PHP_EOL,
					$desc_val
				);
				break;
			case 'end':
				$desc_val = sprintf( '%1$s %2$s %3$s %4$s',
					$desc_val,
					PHP_EOL,
					$text_product_link_to_desc,
					get_permalink( $args_arr['product']->get_id() )
				);
				break;
		}

		return $desc_val;
	}

	/**
	 * of set_new_options
	 * 
	 * @return void
	 */
	private function set_new_options() {
		// TODO: Ещё раз перепроверить функцию
		// Если предыдущая версия плагина меньше текущей
		if ( version_compare( $this->get_plugin_version(), $this->plugin_version, '<' ) ) {
			new IP2Y_Error_Log( sprintf( '%1$s (%2$s < %3$s). %4$s; Файл: %5$s; Строка: %6$s',
				'Предыдущая версия плагина меньше текущей',
				(string) $this->get_plugin_version(),
				(string) $this->plugin_version,
				'Обновляем опции плагина',
				'class-ip2y.php',
				__LINE__
			) );
			// получаем список дефолтных настроек
			$ip2y_data_arr_obj = new IP2Y_Data_Arr();
			$default_settings_obj = $ip2y_data_arr_obj->get_opts_name_and_def_date_obj( 'all' );

			$settings_arr = common_option_get( 'ip2y_settings_arr' );
			for ( $i = 0; $i < count( $default_settings_obj ); $i++ ) {
				$name = $default_settings_obj[ $i ]->name;
				$value = $default_settings_obj[ $i ]->opt_def_value;
				if ( ! isset( $settings_arr[ $name ] ) ) {
					$settings_arr[ $name ] = $value;
				}
			}
			common_option_upd( 'ip2y_settings_arr', $settings_arr, 'no' ); // пишем все настройки
		} else { // обновления не требуются
			return;
		}

		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), 'ip2y_version', $this->plugin_version );
		} else {
			update_option( 'ip2y_version', $this->plugin_version );
		}
	}

	/**
	 * Print admin notice
	 * 
	 * @param string $message
	 * @param string $class
	 * 
	 * @return void
	 */
	private function admin_notices_func( $message, $class ) {
		$ip2y_disable_notices = univ_option_get( 'ip2y_disable_notices' );
		if ( $ip2y_disable_notices === 'on' ) {
			return;
		} else {
			printf( '<div class="notice %1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			return;
		}
	}
} /* end class IP2Y */