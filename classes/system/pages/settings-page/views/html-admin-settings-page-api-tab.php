<?php
/**
 * The Instruction tab
 * 
 * @version 0.2.0 (16-04-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="postbox">
	<h2 class="hndle">
		<?php esc_html_e( 'API Settings', 'wc-import-yandex' ); ?>
	</h2>
	<div class="inside">
		<?php
		$token = common_option_get( 'access_token', false, $view_arr['feed_id'], 'ip2y' );
		if ( empty( $token ) ) {
			printf( '<p><span style="color: red;">%1$s</span></p>',
				esc_html__( 'You need to get a token', 'wc-import-yandex' )
			);
		}
		$params = [ 
			'client_id' => common_option_get( 'client_id', false, $view_arr['feed_id'], 'ip2y' ),
			'state' => 'ip2y-yandex',
			'response_type' => 'code',
			'redirect_uri' => get_site_url(),
		];

		$url = 'https://oauth.yandex.ru/authorize?' . urldecode( http_build_query( $params ) );

		printf( '<p>%1$s: <a href="%2$s">%3$s</a>. %4$s</p>',
			esc_html__(
				'Fill in the "ClientID", "Client secret", "Campaign ID", save them, and then follow this link',
				'wc-import-yandex'
			),
			esc_attr( $url ),
			esc_html__( 'Authorization via Yandex', 'wc-import-yandex' ),
			esc_html__( 'Be sure to click "allow". You will then be redirected back', 'wc-import-yandex' )
		);
		?>
		<table class="form-table" role="presentation">
			<tbody>
				<?php IP2Y_Settings_Page::print_view_html_fields( $view_arr['tab_name'], $view_arr['feed_id'] ); ?>
				<tr class="ip2y_tr">
					<th scope="row"><label for="redirect_uri">Redirect URI</label></th>
					<td class="overalldesc">
						<input type="text" name="redirect_uri" id="redirect_uri"
							value="<?php echo get_site_url( null, '/' ); ?>" disabled><br />
						<span class="description">
							<small><strong>redirect_uri</strong> -
								<?php
								esc_html_e( 'specify it in the application settings', 'wc-import-yandex' );
								?>
							</small></span>
					</td>
				</tr>
				<?php if ( ! empty( $token ) ) : ?>
					<tr class="ip2y_tr">
						<th scope="row">
							<label for="redirect_uri">
								<?php esc_html_e( 'Check API', 'wc-import-yandex' ); ?>
							</label>
						</th>
						<td class="overalldesc">
							<input id="button-check-api" class="button" value="<?php
							esc_html_e( 'Check API', 'wc-import-yandex' );
							?>" type="submit" name="ip2y_check_action" /><br />
							<span class="description">
								<small>
									<?php
									printf( '%s. %s',
										esc_html__( 'The Yandex API is configured', 'wc-import-yandex' ),
										esc_html__(
											'Now you can check its operation by clicking on this button',
											'wc-import-yandex'
										)
									);
									?></span>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>