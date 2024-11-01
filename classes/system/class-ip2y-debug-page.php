<?php
/**
 * Plugin Debug Page
 *
 * @package                 Import Products to Yandex
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 0.2.0 (16-04-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param      string       $pref - Optional
 *
 * @depends                 classes:    ICPD_Feedback
 *                          traits:     IP2Y_Get_Unit
 *                          methods     
 *                          functions:  common_option_get
 *                          constants:  IP2Y_PLUGIN_DIR_PATH
 *                                      IP2Y_PLUGIN_UPLOADS_DIR_URL
 */
defined( 'ABSPATH' ) || exit;

// 'wc-import-yandex' - slug for translation (be sure to make an autocorrect)
class IP2Y_Debug_Page {
	/**
	 * Plugin prefix
	 * @var string
	 */
	private $pref = 'ip2y';

	/**
	 * Plugin Debug Page
	 * 
	 * @return void
	 */
	public function __construct( $pref = null ) {
		if ( $pref ) {
			$this->pref = $pref;
		}

		$this->init_classes();
		$this->init_hooks();
		$this->listen_submit();

		$this->print_view_html_form();
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
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		// наш класс, вероятно, вызывается во время срабатывания хука admin_menu.
		// admin_init - следующий в очереди срабатывания, хуки раньше admin_menu нет смысла вешать
		// add_action('admin_init', [ $this, 'my_func' ], 10, 1);
		return;
	}

	public function print_view_html_form() { ?>
		<div class="wrap">
			<h1>
				<?php
				printf( '%s v.%s',
					esc_html__( 'Debug page', 'wc-import-yandex' ),
					esc_html__( common_option_get( 'ip2y_version' ) )
				);
				?>
			</h1>
			<?php do_action( 'my_admin_notices', $this->get_pref() ); ?>
			<div id="dashboard-widgets-wrap">
				<div id="dashboard-widgets" class="metabox-holder">
					<div id="postbox-container-1" class="postbox-container">
						<div class="meta-box-sortables">
							<?php $this->get_html_block_logs(); ?>
						</div>
					</div>
					<div id="postbox-container-2" class="postbox-container">
						<div class="meta-box-sortables">
							<?php $this->get_html_block_simulation(); ?>
						</div>
					</div>
					<div id="postbox-container-3" class="postbox-container">
						<div class="meta-box-sortables">
							<?php $this->get_html_block_possible_problems(); ?>
							<?php $this->get_html_block_sandbox(); ?>
						</div>
					</div>
					<div id="postbox-container-4" class="postbox-container">
						<div class="meta-box-sortables">
							<?php
							do_action( 'ip2y_before_support_project' );
							do_action( 'ip2y_feedback_block' );
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php // end print_view_html_form();
	}

	public function get_html_block_logs() {
		$keeplogs = common_option_get( $this->get_input_name_keeplogs() );
		$ip2y_disable_notices = common_option_get( $this->get_input_name_disable_notices() ); ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php esc_html_e( 'Logs', 'wc-import-yandex' ); ?>
			</h2>
			<div class="inside">
				<p>
					<?php if ( $keeplogs === 'on' ) {
						printf(
							'<strong>%1$s:</strong><br /><a href="%2$s/plugin.log" target="_blank">%3$s/plugin.log</a>',
							esc_html__( 'Log-file here', 'wc-import-yandex' ),
							esc_html( IP2Y_PLUGIN_UPLOADS_DIR_URL ),
							esc_html( IP2Y_PLUGIN_UPLOADS_DIR_PATH )
						);
					} ?>
				</p>
				<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">
									<label for="<?php echo $this->get_input_name_keeplogs(); ?>">
										<?php esc_html_e( 'Keep logs', 'wc-import-yandex' ); ?>
									</label><br />
									<input class="button" id="<?php echo $this->get_submit_name_clear_logs(); ?>" type="submit"
										name="<?php echo $this->get_submit_name_clear_logs(); ?>"
										value="<?php esc_html_e( 'Clear logs', 'wc-import-yandex' ); ?>" />
								</th>
								<td class="overalldesc">
									<input type="checkbox" name="<?php echo $this->get_input_name_keeplogs(); ?>"
										id="<?php echo $this->get_input_name_keeplogs(); ?>" <?php checked( $keeplogs, 'on' ); ?> /><br />
									<span class="description">
										<?php esc_html_e( 'Do not check this box if you are not a developer', 'wc-import-yandex' ); ?>!
									</span>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="<?php echo $this->get_input_name_disable_notices(); ?>">
										<?php esc_html_e( 'Disable notices', 'wc-import-yandex' ); ?>
									</label></th>
								<td class="overalldesc">
									<input type="checkbox" name="<?php echo $this->get_input_name_disable_notices(); ?>"
										id="<?php echo $this->get_input_name_disable_notices(); ?>" <?php checked( $ip2y_disable_notices, 'on' ); ?> /><br />
									<span class="description">
										<?php esc_html_e( 'Disable notices about the import of products', 'wc-import-yandex' ); ?>!
									</span>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="button-primary"></label></th>
								<td class="overalldesc"></td>
							</tr>
							<tr>
								<th scope="row"><label for="button-primary"></label></th>
								<td class="overalldesc">
									<?php wp_nonce_field( $this->get_nonce_action_debug_page(), $this->get_nonce_field_debug_page() ); ?><input
										id="button-primary" class="button-primary" type="submit"
										name="<?php echo $this->get_submit_name(); ?>"
										value="<?php esc_html_e( 'Save', 'wc-import-yandex' ); ?>" /><br />
									<span class="description">
										<?php esc_html_e( 'Click to save the settings', 'wc-import-yandex' ); ?>
									</span>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
		<?php
	} // end get_html_block_logs();

	public function get_html_block_simulation() { ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php esc_html_e( 'Request simulation', 'wc-import-yandex' ); ?>
			</h2>
			<div class="inside">
				<form action="<?php esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
					<?php $resust_simulated = '';
					$resust_report = '';
					if ( isset( $_POST['ip2y_num_feed'] ) ) {
						$ip2y_num_feed = sanitize_text_field( $_POST['ip2y_num_feed'] );
					} else {
						$ip2y_num_feed = '1';
					}
					if ( isset( $_POST['ip2y_simulated_post_id'] ) ) {
						$ip2y_simulated_post_id = sanitize_text_field( $_POST['ip2y_simulated_post_id'] );
					} else {
						$ip2y_simulated_post_id = '';
					}
					if ( isset( $_POST['ip2y_textarea_info'] ) ) {
						$ip2y_textarea_info = sanitize_text_field( $_POST['ip2y_textarea_info'] );
					} else {
						$ip2y_textarea_info = '';
					}
					if ( isset( $_POST['ip2y_textarea_res'] ) ) {
						$ip2y_textarea_res = sanitize_text_field( $_POST['ip2y_textarea_res'] );
					} else {
						$ip2y_textarea_res = '';
					}
					if ( $ip2y_textarea_res == 'calibration' ) {
						$resust_report .= ip2y_calibration( $ip2y_textarea_info );
					}
					if ( isset( $_REQUEST['ip2y_submit_simulated'] ) ) {
						if ( ! empty( $_POST )
							&& check_admin_referer( 'ip2y_nonce_action_simulated', 'ip2y_nonce_field_simulated' ) ) {
							$product_id = (int) $ip2y_simulated_post_id;
							$api = new IP2Y_Api();
							$answer_arr = $api->product_sync( $product_id );
							if ( $answer_arr['status'] == true ) {
								$resust_report = 'Всё штатно';
								$resust_simulated = get_array_as_string( $answer_arr );
							} else {
								$resust_report = 'Есть ошибки';
								$resust_simulated = get_array_as_string( $answer_arr );
							}
						}
					} ?>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label for="ip2y_simulated_post_id">postId</label></th>
								<td class="overalldesc">
									<input type="number" min="1" name="ip2y_simulated_post_id"
										value="<?php echo $ip2y_simulated_post_id; ?>">
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="ip2y_num_feed">feed_id</label></th>
								<td class="overalldesc">
									<select style="width: 100%" name="ip2y_num_feed" id="ip2y_num_feed">
										<option value="1">feed-1</option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row" colspan="2"><textarea rows="4" name="ip2y_textarea_info"
										style="width: 100%;"><?php echo htmlspecialchars( $resust_report ); ?></textarea></th>
							</tr>
							<tr>
								<th scope="row" colspan="2"><textarea rows="16" name="ip2y_textarea_res"
										style="width: 100%;"><?php echo htmlspecialchars( $resust_simulated ); ?></textarea>
								</th>
							</tr>
						</tbody>
					</table>
					<?php wp_nonce_field( 'ip2y_nonce_action_simulated', 'ip2y_nonce_field_simulated' ); ?><input
						class="button-primary" type="submit" name="ip2y_submit_simulated"
						value="<?php esc_html_e( 'Simulated', 'wc-import-yandex' ); ?>" />
				</form>
			</div>
		</div>
	<?php // end get_html_feeds_list();
	} // end get_html_block_simulation();

	public function get_html_block_possible_problems() { ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php esc_html_e( 'Possible problems', 'wc-import-yandex' ); ?>
			</h2>
			<div class="inside">
				<?php
				$possible_problems_arr = $this->get_possible_problems_list();
				if ( $possible_problems_arr[1] > 0 ) {
					printf( '<ol>%s</ol>', esc_html__( $possible_problems_arr[0] ) );
				} else {
					printf( '<p>%s</p>',
						esc_html__( 'Self-diagnosis functions did not reveal potential problems', 'wc-import-yandex' )
					);
				}
				?>
			</div>
		</div>
		<?php
	} // end get_html_block_sandbox();

	public function get_html_block_sandbox() { ?>
		<div class="postbox">
			<h2 class="hndle">
				<?php esc_html_e( 'Sandbox', 'wc-import-yandex' ); ?>
			</h2>
			<div class="inside">
				<?php
				require_once IP2Y_PLUGIN_DIR_PATH . '/sandbox.php';
				try {
					ip2y_run_sandbox();
				} catch (Exception $e) {
					echo 'Exception: ', $e->getMessage(), "\n";
				}
				?>
			</div>
		</div>
		<?php
	} // end get_html_block_sandbox();

	public static function get_possible_problems_list() {
		$possible_problems = '';
		$possible_problems_count = 0;
		$conflict_with_plugins = 0;
		$conflict_with_plugins_list = '';
		$check_global_attr_count = wc_get_attribute_taxonomies();
		if ( count( $check_global_attr_count ) < 1 ) {
			$possible_problems_count++;
			$possible_problems .= '<li>' . __( 'Your site has no global attributes! This may affect the quality of the import to ok.ru. This can also cause difficulties when setting up the plugin', 'wc-import-yandex' ) . '. <a href="https://icopydoc.ru/globalnyj-i-lokalnyj-atributy-v-woocommerce/?utm_source=import-products-to-yandex&utm_medium=organic&utm_campaign=in-plugin-import-products-to-yandex&utm_content=debug-page&utm_term=possible-problems">' . __( 'Please read the recommendations', 'wc-import-yandex' ) . '</a>.</li>';
		}
		if ( is_plugin_active( 'snow-storm/snow-storm.php' ) ) {
			$possible_problems_count++;
			$conflict_with_plugins++;
			$conflict_with_plugins_list .= 'Snow Storm<br/>';
		}
		if ( is_plugin_active( 'ilab-media-tools/ilab-media-tools.php' ) ) {
			$possible_problems_count++;
			$conflict_with_plugins++;
			$conflict_with_plugins_list .= 'Media Cloud (Media Cloud for Amazon S3...)<br/>';
		}
		if ( is_plugin_active( 'email-subscribers/email-subscribers.php' ) ) {
			$possible_problems_count++;
			$conflict_with_plugins++;
			$conflict_with_plugins_list .= 'Email Subscribers & Newsletters<br/>';
		}
		if ( is_plugin_active( 'saphali-search-castom-filds/saphali-search-castom-filds.php' ) ) {
			$possible_problems_count++;
			$conflict_with_plugins++;
			$conflict_with_plugins_list .= 'Email Subscribers & Newsletters<br/>';
		}
		if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			$possible_problems_count++;
			$conflict_with_plugins++;
			$conflict_with_plugins_list .= 'W3 Total Cache<br/>';
		}
		if ( is_plugin_active( 'docket-cache/docket-cache.php' ) ) {
			$possible_problems_count++;
			$conflict_with_plugins++;
			$conflict_with_plugins_list .= 'Docket Cache<br/>';
		}
		if ( class_exists( 'MPSUM_Updates_Manager' ) ) {
			$possible_problems_count++;
			$conflict_with_plugins++;
			$conflict_with_plugins_list .= 'Easy Updates Manager<br/>';
		}
		if ( class_exists( 'OS_Disable_WordPress_Updates' ) ) {
			$possible_problems_count++;
			$conflict_with_plugins++;
			$conflict_with_plugins_list .= 'Disable All WordPress Updates<br/>';
		}
		if ( $conflict_with_plugins > 0 ) {
			$possible_problems_count++;
			$possible_problems .= '<li><p>' . __( 'Most likely, these plugins negatively affect the operation of', 'wc-import-yandex' ) . ' Import Products to Yandex:</p>' . $conflict_with_plugins_list . '<p>' . __( 'If you are a developer of one of the plugins from the list above, please contact me', 'wc-import-yandex' ) . ': <a href="mailto:support@icopydoc.ru">support@icopydoc.ru</a>.</p></li>';
		}
		return [ $possible_problems, $possible_problems_count, $conflict_with_plugins, $conflict_with_plugins_list ];
	}

	private function get_pref() {
		return $this->pref;
	}

	private function get_input_name_keeplogs() {
		return $this->get_pref() . '_keeplogs';
	}

	private function get_input_name_disable_notices() {
		return $this->get_pref() . '_disable_notices';
	}

	private function get_submit_name() {
		return $this->get_pref() . '_submit_debug_page';
	}

	private function get_nonce_action_debug_page() {
		return $this->get_pref() . '_nonce_action_debug_page';
	}

	private function get_nonce_field_debug_page() {
		return $this->get_pref() . '_nonce_field_debug_page';
	}

	private function get_submit_name_clear_logs() {
		return $this->get_pref() . '_submit_clear_logs';
	}

	private function listen_submit() {
		if ( isset( $_REQUEST[ $this->get_submit_name()] ) ) {
			$this->save_data();
			$message = __( 'Updated', 'wc-import-yandex' );
			$class = 'notice-success';

			add_action( 'my_admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}

		if ( isset( $_REQUEST[ $this->get_submit_name_clear_logs()] ) ) {
			$filename = IP2Y_PLUGIN_UPLOADS_DIR_PATH . '/plugin.log';
			if ( file_exists( $filename ) ) {
				wp_delete_file( $filename );
				$res = true;
			} else {
				$res = false;
			}
			if ( true == $res ) {
				$message = __( 'Logs were cleared', 'wc-import-yandex' );
				$class = 'notice-success';
			} else {
				$message = __(
					'Error accessing log file. The log file may have been deleted previously',
					'wc-import-yandex'
				);
				$class = 'notice-warning';
			}

			add_action( 'my_admin_notices', function () use ($message, $class) {
				$this->admin_notices_func( $message, $class );
			}, 10, 2 );
		}
		return;
	}

	private function save_data() {
		if ( ! empty( $_POST )
			&& check_admin_referer( $this->get_nonce_action_debug_page(), $this->get_nonce_field_debug_page() ) ) {

			if ( isset( $_POST[ $this->get_input_name_keeplogs()] ) ) {
				$keeplogs = sanitize_text_field( $_POST[ $this->get_input_name_keeplogs()] );
			} else {
				$keeplogs = '';
			}
			if ( isset( $_POST[ $this->get_input_name_disable_notices()] ) ) {
				$disable_notices = sanitize_text_field( $_POST[ $this->get_input_name_disable_notices()] );
			} else {
				$disable_notices = '';
			}

			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), $this->get_input_name_keeplogs(), $keeplogs );
				update_blog_option( get_current_blog_id(), $this->get_input_name_disable_notices(), $disable_notices );
			} else {
				update_option( $this->get_input_name_keeplogs(), $keeplogs );
				update_option( $this->get_input_name_disable_notices(), $disable_notices );
			}
		}
		return;
	}

	/**
	 * Print a notice in the admin Plugins page
	 * 
	 * @param string $class - Optional
	 * @param string $message - Optional
	 * 
	 * @return void
	 */
	private function admin_notices_func( $message, $class ) {
		printf( '<div class="notice %1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}