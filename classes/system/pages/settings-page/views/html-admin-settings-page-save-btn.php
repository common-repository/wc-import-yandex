<?php
/**
 * Print the Save button
 * 
 * @version 0.2.0 (16-04-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['tab_name']
 * @param $view_arr['feed_id']
 */
defined( 'ABSPATH' ) || exit;

if ( $view_arr['tab_name'] === 'no_submit_tab' ) {
	return;
}
?>
<div class="postbox">
	<div class="inside">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="button-primary"></label></th>
					<td class="overalldesc">
						<?php wp_nonce_field( 'ip2y_nonce_action', 'ip2y_nonce_field' ); ?>
						<input id="button-primary" class="button-primary" name="ip2y_submit_action" type="submit" value="<?php
						if ( $view_arr['tab_name'] === 'main_tab' ) {
							printf( '%s & %s (ID: %s)',
								esc_html__( 'Save', 'wc-import-yandex' ),
								esc_html__( 'Run Import', 'wc-import-yandex' ),
								$view_arr['feed_id']
							);
						} else {
							printf( '%s (ID: %s)',
								esc_html__( 'Save', 'wc-import-yandex' ),
								$view_arr['feed_id']
							);
						}
						?>" /><br />
						<span class="description">
							<small>
								<?php esc_html_e( 'Click to save the settings', 'wc-import-yandex' ); ?>
							</small>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>