<?php
/**
 * The Instruction tab
 * 
 * @version 0.3.1 (03-06-2024)
 * @see     
 * @package 
 * 
 * @param 
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="postbox">
	<h2 class="hndle">
		<?php esc_html_e( 'Instruction', 'wc-import-yandex' ); ?>
	</h2>
	<div class="inside">
		<p><i>(
				<?php esc_html_e( 'The full version of the instruction can be found', 'wc-import-yandex' );
				?><a href="<?php
				printf( '%1$s?utm_source=%2$s&utm_medium=organic&utm_campaign=in-plugin-%2$s%3$s',
					'https://icopydoc.ru/import-tovarov-iz-woocommerce-v-yandeks-cherez-api/',
					'wc-import-yandex',
					'&utm_content=api-set-page&utm_term=main-instruction'
				); ?>" target="_blank"><?php esc_html_e( 'here', 'wc-import-yandex' ); ?></a>)
			</i></p>
		<p>
			<?php esc_html_e( 'To access Yandex API you need', 'wc-import-yandex' ); ?> <a target="_blank"
				href="//oauth.yandex.ru/client/new"><?php esc_html_e( 'Create application', 'wc-import-yandex' ); ?></a>
		</p>
		<p>
			<?php esc_html_e( 'For this', 'wc-import-yandex' ); ?>:
		</p>
		<ol>
			<li>
				<?php
				esc_html_e( 'Follow the link and click', 'wc-import-yandex' ); ?> "<a target="_blank"
					href="//oauth.yandex.ru/client/new">
					<?php esc_html_e( 'Add application', 'wc-import-yandex' ); ?></a>"
			</li>
		</ol>
		<p><img style="max-width: 100%;" src="<?php echo IP2Y_PLUGIN_DIR_URL; ?>assets/img/instruction-1.png"
				alt="instruction-1.png" /></p>
		<ol>
			<li value="2">
				<?php esc_html_e(
					'Come up with a name for the new application, check the box next to the "web services" item',
					'wc-import-yandex' ); ?>. "Redirect URI": <code><?php echo get_site_url( null, '/' ); ?></code>
			</li>
			<li>
				<?php esc_html_e(
					'In the "Data Access" section, use the search bar to find',
					'wc-import-yandex' ); ?>. "API Яндекс.Маркета/Поиск по товарам для партнёров" <?php
				  esc_html_e(
				  	'and add them to the application',
				  	'wc-import-yandex' );
				  ?>
			</li>
			<li><?php esc_html_e(
				'Fill in the email address and click "Create app"',
				'wc-import-yandex' );
			?></li>
		</ol>
		<p><img style="max-width: 100%;" src="<?php echo IP2Y_PLUGIN_DIR_URL; ?>assets/img/instruction-2.png"
				alt="instruction-2.png" /></p>
		<ol>
			<li value="5">
				<?php
				printf( '%s "%s", "%s" %s (%s "%s")',
					esc_html__( 'Copy the', 'wc-import-yandex' ),
					esc_html__( 'Client ID', 'wc-import-yandex' ),
					esc_html__( 'Client secret', 'wc-import-yandex' ),
					esc_html__( 'and paste them on the plugin settings page', 'wc-import-yandex' ),
					esc_html__( 'tab', 'wc-import-yandex' ),
					esc_html__( 'API Settings', 'wc-import-yandex' )
				); ?>
			</li>
		</ol>
		<p><img style="max-width: 100%;" src="<?php echo IP2Y_PLUGIN_DIR_URL; ?>assets/img/instruction-3.png"
				alt="instruction-3.png" /></p>
		<ol>
			<li value="6">
				<?php
				printf( '%s "%s" %s %s (%s "%s")',
					esc_html__( 'Copy the', 'wc-import-yandex' ),
					esc_html__( 'Campaign ID', 'wc-import-yandex' ),
					esc_html__( 'from your personal account in Yandex Market', 'wc-import-yandex' ),
					esc_html__( 'and paste them on the plugin settings page', 'wc-import-yandex' ),
					esc_html__( 'tab', 'wc-import-yandex' ),
					esc_html__( 'API Settings', 'wc-import-yandex' )
				); ?>
			</li>
		</ol>
		<p><img style="max-width: 100%;" src="<?php echo IP2Y_PLUGIN_DIR_URL; ?>assets/img/instruction-4.png"
				alt="instruction-4.png" /></p>
		<ol>
			<li value="7">
				<?php
				printf( '%s "%s" %s %s (%s "%s"). %s "%s"',
					esc_html__( 'Copy the', 'wc-import-yandex' ),
					esc_html__( 'Businesses ID', 'wc-import-yandex' ),
					esc_html__( 'from your personal account in Yandex Market', 'wc-import-yandex' ),
					esc_html__( 'and paste them on the plugin settings page', 'wc-import-yandex' ),
					esc_html__( 'tab', 'wc-import-yandex' ),
					esc_html__( 'API Settings', 'wc-import-yandex' ),
					esc_html__( 'After all four fields are filled in, click', 'wc-import-yandex' ),
					esc_html__( 'Save', 'wc-import-yandex' )
				); ?>
			</li>
		</ol>
		<p><img style="max-width: 100%;" src="<?php echo IP2Y_PLUGIN_DIR_URL; ?>assets/img/instruction-5.png"
				alt="instruction-5.png" /></p>
		<ol>
			<li value="8">
				<?php
				printf( '%s "%s" %s "%s"',
					esc_html__( 'Go to tab', 'wc-import-yandex' ),
					esc_html__( 'API Settings', 'wc-import-yandex' ),
					esc_html__( 'and click on the link', 'wc-import-yandex' ),
					esc_html__( 'Authorization via Yandex', 'wc-import-yandex' )
				);
				?>
			</li>
		</ol>
		<p><img style="max-width: 100%;" src="<?php echo IP2Y_PLUGIN_DIR_URL; ?>assets/img/instruction-6.png"
				alt="instruction-6.png" /></p>
		<ol>
			<li value="9">
				<?php
				printf( '%s "%s". %s. %s "%s. %s 10 %s"',
					esc_html__(
						'If the authorization was successful, then you will have a button',
						'wc-import-yandex'
					),
					esc_html__( 'Check API', 'wc-import-yandex' ),
					esc_html__( 'Click on it to test the API', 'wc-import-yandex' ),
					esc_html__(
						'If everything is configured correctly, you will see the message',
						'wc-import-yandex'
					),
					esc_html__( 'API connection was successful', 'wc-import-yandex' ),
					esc_html__( 'Now you can go to step', 'wc-import-yandex' ),
					esc_html__( 'of the instructions', 'wc-import-yandex' )
				);
				?>
			</li>
		</ol>
		<p><img style="max-width: 100%;" src="<?php echo IP2Y_PLUGIN_DIR_URL; ?>assets/img/instruction-7.png"
				alt="instruction-7.png" /></p>
		<ol>
			<li value="10">
				<?php
				printf( '%s "%s" %s "%s". %s',
					esc_html__( 'After that, go to the', 'wc-import-yandex' ),
					esc_html__( 'Main settings', 'wc-import-yandex' ),
					esc_html__( 'and activate the item', 'wc-import-yandex' ),
					esc_html__( 'Syncing with Yandex Market', 'wc-import-yandex' ),
					esc_html__( 'Also fill in the remaining fields, following the prompts', 'wc-import-yandex' )
				);
				?>
			</li>
		</ol>
	</div>
</div>