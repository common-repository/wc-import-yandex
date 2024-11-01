<?php 
/**
 * The Another page tab
 * 
 * @version 0.1.0 (06-02-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;

$args_arr = [ 'tab_name' => $view_arr['tab_name'], 'feed_id' => $view_arr['feed_id'] ];
$args_arr = apply_filters( 'ip2y_f_another_tab_args_arr', $args_arr );
?>
<div class="postbox">
	<?php do_action_ref_array( 'ip2y_a_before_another_tab', $args_arr ); ?>
	<div class="inside">
		<table class="form-table" role="presentation">
			<tbody>
				<?php do_action_ref_array( 'ip2y_a_prepend_another_tab', $args_arr ); ?>
				<?php IP2Y_Settings_Page::print_view_html_fields( $view_arr['tab_name'], $view_arr['feed_id'] ); ?>
				<?php do_action_ref_array( 'ip2y_a_append_another_tab', $args_arr ); ?>
			</tbody>
		</table>
	</div>
	<?php do_action_ref_array( 'ip2y_a_after_another_tab', $args_arr ); ?>
</div>
<?php
do_action( 'ip2y_switch_get_tab', [ 'tab_name' => $view_arr['tab_name'], 'feed_id' => $view_arr['feed_id'] ] );