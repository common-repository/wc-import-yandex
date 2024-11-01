<?php
/**
 * Settings page
 * 
 * @version 0.1.0 (06-02-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1>Import products to Yandex Market</h1>
	<div id="poststuff">
		<div id="post-body" class="columns-2">

			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
					<?php
					do_action( 'ip2y_activation_forms' );

					do_action( 'ip2y_feedback_block' );

					do_action( 'ip2y_before_container_1', $view_arr['feed_id'] );

					do_action( 'ip2y_between_container_1', $view_arr['feed_id'] );

					do_action( 'ip2y_append_container_1', $view_arr['feed_id'] );
					?>
				</div>
			</div><!-- /postbox-container-1 -->

			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables">
					
					<?php include_once __DIR__ . '/html-admin-settings-page-tabs.php'; ?>

					<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post"
						enctype="multipart/form-data">
						<input type="hidden" name="ip2y_feed_id_for_save"
							value="<?php echo esc_attr( $view_arr['feed_id'] ); ?>">
						<?php
						switch ( $view_arr['tab_name'] ) {
							case 'api_tab':
								include_once __DIR__ . '/html-admin-settings-page-api-tab.php';
								break;
							case 'instruction_tab':
								include_once __DIR__ . '/html-admin-settings-page-instruction-tab.php';
								break;
							default:
								$html_template = __DIR__ . '/html-admin-settings-page-tab-another.php';
								$html_template = apply_filters( 'ip2y_f_html_template_tab',
									$html_template,
									[ 
										'tab_name' => $view_arr['tab_name'],
										'view_arr' => $view_arr
									]
								);
								include_once $html_template;
						}

						do_action( 'ip2y_between_container_2', $view_arr['feed_id'] );

						include_once __DIR__ . '/html-admin-settings-page-save-btn.php';
						?>
					</form>

				</div>
			</div><!-- /postbox-container-2 -->

		</div>
	</div><!-- /poststuff -->
	<?php
	do_action( 'print_view_html_icp_banners', 'ip2y' );
	do_action( 'print_view_html_icpd_my_plugins_list', 'ip2y' );
	?>
</div>