<?php
/**
 * Print Extensions page
 * 
 * @version 0.4.0 (07-06-2024)
 * @see     
 * @package 
 */
defined( 'ABSPATH' ) || exit;
?>
<style>
	.button-primary {
		padding: 0.375rem 0.75rem !important;
		font-size: 1rem !important;
		border-radius: 0.25rem !important;
		border: #181a1c 1px solid !important;
		background-color: #181a1c !important;
		text-align: center;
		margin: 0 auto !important;
	}

	.button-primary:hover {
		background-color: #3d4247 !important;
		border-color: #4b5157 !important;
	}

	.ip2y_banner {
		max-width: 100%
	}
</style>
<div id="ip2y_extensions" class="wrap">
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1">
				<div class="meta-box-sortables">
					<div class="postbox">
						<a href="https://icopydoc.ru/product/wc-import-yandex-pro/?utm_source=wc-import-yandex&utm_medium=organic&utm_campaign=in-plugin-wc-import-yandex&utm_content=extensions&utm_term=banner-pro"
							target="_blank"><img class="ip2y_banner"
								src="<?php echo esc_attr( IP2Y_PLUGIN_DIR_URL ); ?>/assets/img/wc-import-yandex-pro-banner.jpg"
								alt="Upgrade to Import Products to Yandex PRO" /></a>
						<div class="inside">
							<table class="form-table">
								<tbody>
									<tr>
										<td class="overalldesc" style="font-size: 20px;">
											<h3 style="font-size: 24px; text-align: center; color: #5b2942;">Import
												Products to Yandex PRO</h3>
											<ul style="text-align: center;">
												<li>&#10004;
													<?php esc_html_e(
														'The ability to сhange the product price by a certain percentage',
														'wc-import-yandex' );
													?>;
												</li>
												<li>&#10004;
													<?php esc_html_e(
														'The ability to import multiple images instead of one',
														'wc-import-yandex' );
													?>;
												</li>
												<li>&#10004;
													<?php esc_html_e(
														'The ability to exclude products from certain categories',
														'wc-import-yandex' );
													?>;
												</li>
												<li>&#10004;
													<?php esc_html_e(
														'The ability to exclude products at a price',
														'wc-import-yandex'
													); ?>;
												</li>

												<li>&#10004;
													<?php esc_html_e(
														'Even more stable work', 'wc-import-yandex' );
													?>!
												</li>
											</ul>
											<p style="text-align: center;"><a class="button-primary"
													href="https://icopydoc.ru/product/wc-import-yandex-pro/?utm_source=wc-import-yandex&utm_medium=organic&utm_campaign=in-plugin-wc-import-yandex&utm_content=extensions&utm_term=poluchit-pro"
													target="_blank">
													<?php
													printf( '%s %s %s',
														esc_html__( 'Get', 'wc-import-yandex' ),
														'Import Products to Yandex PRO',
														esc_html__( 'Now', 'wc-import-yandex' )
													);
													?>
												</a>
											</p>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>