<?php
/**
 * Set and Get the Plugin Data
 *
 * @package                 iCopyDoc Plugins (v1.1, core 22-04-2024)
 * @subpackage              Import Products to Yandex
 * @since                   0.1.0
 * 
 * @version                 0.4.0 (07-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param     
 *
 * @depends                 classes:    
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

class IP2Y_Data_Arr {
	/**
	 * Plugin options array
	 * @var array
	 */
	private $data_arr = [];

	/**
	 * Set and Get the Plugin Data
	 * 
	 * @param array $data_arr - Optional
	 */
	public function __construct( $data_arr = [] ) {
		if ( empty( $data_arr ) ) {
			$this->data_arr = [ 
				[ 
					'opt_name' => 'status_sborki',
					'def_val' => '-1',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // дата начала сборки
					'opt_name' => 'date_sborki',
					'def_val' => '0000000001',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // дата завершения сборки
					'opt_name' => 'date_sborki_end',
					'def_val' => '0000000001',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // дата сохранения настроек плагина
					'opt_name' => 'date_save_set',
					'def_val' => '0000000001',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ // число товаров, попавших в выгрузку
					'opt_name' => 'count_products_in_feed',
					'def_val' => '-1',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],
				[ 
					'opt_name' => 'status_cron',
					'def_val' => 'off',
					'mark' => 'private',
					'required' => true,
					'type' => 'auto',
					'tab' => 'none'
				],

				[ 
					'opt_name' => 'client_id',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'api_tab',
					'data' => [ 
						'label' => 'ClientID',
						'desc' => sprintf(
							'ClientID - %s',
							__( 'from the app settings', 'wc-import-yandex' )
						),
						'placeholder' => sprintf( '%s: %s',
							__( 'For example', 'wc-import-yandex' ),
							'75657728bcx27fcbabe46cb0a62780f4'
						)
					]
				],
				[ 
					'opt_name' => 'client_secret',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'api_tab',
					'data' => [ 
						'label' => __( 'Client secret', 'wc-import-yandex' ),
						'desc' => sprintf(
							'Client secret - %s',
							__( 'from the app settings', 'wc-import-yandex' )
						),
						'placeholder' => sprintf( '%s: %s',
							__( 'For example', 'wc-import-yandex' ),
							'be35av8952cf445d98bb38d791c62c8ct'
						)
					]
				],
				[ 
					'opt_name' => 'campaign_id',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'api_tab',
					'data' => [ 
						'label' => __( 'Campaign ID', 'wc-import-yandex' ),
						'desc' => sprintf(
							'campaign_id - %s (%s)',
							__(
								'from the settings of the Yandex Market account to which we export products',
								'wc-import-yandex'
							),
							__( 'only numbers', 'wc-import-yandex' )
						),
						'placeholder' => sprintf( '%s: %s',
							__( 'For example', 'wc-import-yandex' ),
							'87654321'
						)
					]
				],
				[ 
					'opt_name' => 'businesses_id',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'api_tab',
					'data' => [ 
						'label' => __( 'Businesses ID', 'wc-import-yandex' ),
						'desc' => sprintf(
							'businesses_id - %s (%s)',
							__(
								'from the settings of the Yandex Market account to which we export products',
								'wc-import-yandex'
							),
							__( 'only numbers', 'wc-import-yandex' )
						),
						'placeholder' => sprintf( '%s: %s',
							__( 'For example', 'wc-import-yandex' ),
							'7192155'
						)
					]
				],
				[ 
					'opt_name' => 'access_token',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'api_tab',
					'data' => [ 
						'label' => __( 'Access token', 'wc-import-yandex' ),
						'desc' => 'access_token',
						'placeholder' => ''
					]
				],

				[ 
					'opt_name' => 'syncing_with_yandex',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Syncing with Yandex', 'wc-import-yandex' ),
						'desc' => __(
							'Using this parameter, you can stop the plugin completely',
							'wc-import-yandex'
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'wc-import-yandex' ) ]
						],
						'tr_class' => 'ip2y_tr'
					]
				],
				[ 
					'opt_name' => 'run_cron',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'The frequency of full synchronization of products', 'wc-import-yandex' ),
						'desc' => __(
							'With the specified frequency, the plugin will transmit information about all your products to Yandex Market',
							'wc-import-yandex'
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => 'hourly', 'text' => __( 'Hourly', 'wc-import-yandex' ) ],
							[ 'value' => 'three_hours', 'text' => __( 'Every three hours', 'wc-import-yandex' ) ],
							[ 'value' => 'six_hours', 'text' => __( 'Every six hours', 'wc-import-yandex' ) ],
							[ 'value' => 'twicedaily', 'text' => __( 'Twice a day', 'wc-import-yandex' ) ],
							[ 'value' => 'daily', 'text' => __( 'Daily', 'wc-import-yandex' ) ],
							[ 'value' => 'week', 'text' => __( 'Once a week', 'wc-import-yandex' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'step_export',
					'def_val' => '300',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Step export', 'wc-import-yandex' ),
						'desc' => __(
							'Determines the maximum number of products uploaded to Yandex Market in one minute',
							'wc-import-yandex'
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => '25', 'text' => '25' ],
							[ 'value' => '30', 'text' => '30' ],
							[ 'value' => '40', 'text' => '40' ],
							[ 'value' => '50', 'text' => '50' ],
							[ 'value' => '100', 'text' => '100' ],
							[ 'value' => '200', 'text' => '200' ],
							[ 'value' => '300', 'text' => '300' ],
							[ 'value' => '400', 'text' => '400' ],
							[ 
								'value' => '500',
								'text' => sprintf( '500 (%s)',
									__( 'The maximum value allowed by Yandex Market', 'wc-import-yandex' )
								)
							]
						]
					]
				],
				[ 
					'opt_name' => 'vat',
					'def_val' => '6',
					'mark' => 'public',
					'required' => false,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'VAT rate', 'wc-import-yandex' ),
						'desc' => '[vat] - ' . __(
							'The rate must correspond to the tax system that you specified when registering in Yandex Market',
							'wc-import-yandex'
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => '6', 'text' => __( 'Not subject to VAT', 'wc-import-yandex' ) ],
							[ 'value' => '5', 'text' => '0%' ],
							[ 'value' => '2', 'text' => '10%' ],
							[ 'value' => '7', 'text' => '20%' ]
						],
						'tr_class' => 'ip2y_tr'
					]
				],
				[ 
					'opt_name' => 'prefix_shop_sku',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Prefix for product ID', 'wc-import-yandex' ),
						'desc' => __(
							'Since you cannot change the ID of previously uploaded products on Yandex Market, this option may be useful at the debugging stage',
							'wc-import-yandex'
						),
						'placeholder' => 'test-',
						'tr_class' => 'ip2y_tr'
					]
				],
				[ 
					'opt_name' => 'source_shop_sku',
					'def_val' => 'id',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Product ID', 'wc-import-yandex' ),
						'desc' => '[shopSku]',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'id',
								'text' => sprintf( '%s / %s',
									__( 'Product ID', 'wc-import-yandex' ),
									__( 'Variation ID', 'wc-import-yandex' )
								)
							],
							[ 'value' => 'sku', 'text' => __( 'Product SKU', 'wc-import-yandex' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'old_price',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => false,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Old price', 'wc-import-yandex' ),
						'desc' => __(
							'In oldprice indicates the old price of the goods, which must necessarily be higher than the new price (price)',
							'wc-import-yandex'
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'wc-import-yandex' ) ]
						],
						'tr_class' => 'ip2y_tr'
					]
				],
				[ 
					'opt_name' => 'manufacturer_countries',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Manufacturer countries', 'wc-import-yandex' ),
						'desc' => '[manufacturerCountries]',
						'woo_attr' => true,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'vendor',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => sprintf( '%s (%s)',
							__( 'Vendor', 'wc-import-yandex' ),
							__( 'brand name', 'wc-import-yandex' ),
						),
						'desc' => '[vendor]',
						'woo_attr' => true,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'vendorcode',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Vendor Code', 'wc-import-yandex' ),
						'desc' => '[vendorCode] - ' . __( 'The article of the product from the vendor', 'wc-import-yandex' ),
						'woo_attr' => true,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => 'sku', 'text' => __( 'Product SKU', 'wc-import-yandex' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'barcodes',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Barcodes', 'wc-import-yandex' ),
						'desc' => '[barcodes]',
						'woo_attr' => true,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => 'sku', 'text' => __( 'Substitute from SKU', 'wc-import-yandex' ) ],
							[ 'value' => 'post_meta', 'text' => __( 'Substitute from post meta', 'wc-import-yandex' ) ],
							[ 
								'value' => 'ean-for-woocommerce',
								'text' => __( 'Substitute from', 'wc-import-yandex' ) . ' EAN for WooCommerce'
							],
							[ 
								'value' => 'germanized',
								'text' => __( 'Substitute from', 'wc-import-yandex' ) . ' WooCommerce Germanized'
							]
						],
						'tr_class' => 'ip2y_tr'
					]
				],
				[ 
					'opt_name' => 'barcode_post_meta',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => '',
						'desc' => '',
						'placeholder' => __( 'Name post meta', 'wc-import-yandex' )
					]
				],
				[ 
					'opt_name' => 'description',
					'def_val' => 'fullexcerpt',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Description of the product', 'wc-import-yandex' ),
						'desc' => sprintf( '[description] - %s',
							__( 'The source of the description', 'wc-import-yandex' )
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 
								'value' => 'excerpt',
								'text' => __( 'Only Excerpt description', 'wc-import-yandex' )
							],
							[ 
								'value' => 'full',
								'text' => __( 'Only Full description', 'wc-import-yandex' )
							],
							[ 
								'value' => 'excerptfull',
								'text' => __( 'Excerpt or Full description', 'wc-import-yandex' )
							],
							[ 
								'value' => 'fullexcerpt',
								'text' => __( 'Full or Excerpt description', 'wc-import-yandex' )
							],
							[ 
								'value' => 'excerptplusfull',
								'text' => __( 'Excerpt plus Full description', 'wc-import-yandex' )
							],
							[ 
								'value' => 'fullplusexcerpt',
								'text' => __( 'Full plus Excerpt description', 'wc-import-yandex' )
							]
						],
						'tr_class' => 'ip2y_tr'
					]
				],
				[ 
					'opt_name' => 'var_desc_priority',
					'def_val' => 'enabled',
					'mark' => 'public',
					'required' => false,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __(
							'The varition description takes precedence over others',
							'wc-import-yandex'
						),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'wc-import-yandex' ) ]
						]
					]
				],
				[ 
					'opt_name' => 'add_product_text_to_desc',
					'def_val' => 'before',
					'mark' => 'public',
					'required' => false,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __(
							'Add text to the product description',
							'wc-import-yandex'
						),
						'desc' => sprintf( '%s! %s',
							__( 'Important', 'wc-import-yandex' ),
							__(
								'You need to fill in the field below',
								'wc-import-yandex'
							)
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 
								'value' => 'before',
								'text' => __( 'Add before the main description', 'wc-import-yandex' )
							],
							[ 
								'value' => 'after',
								'text' => __( 'Add after the main description', 'wc-import-yandex' )
							]
						]
					]
				],
				[ 
					'opt_name' => 'text_product_text_to_desc',
					'def_val' => '',
					'mark' => 'public',
					'required' => false,
					'type' => 'textarea',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => '',
						'desc' => __( 'This text will be added to all products', 'wc-import-yandex' ),
						'placeholder' => __(
							'This text will be added to the product description',
							'wc-import-yandex'
						)
					]
				],
				[ 
					'opt_name' => 'params_arr',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Include these attributes in the import', 'wc-import-yandex' ),
						'desc' => sprintf( '%s: %s. %s',
							__( 'Hint', 'wc-import-yandex' ),
							__(
								'To select multiple values, hold down the (ctrl) button on Windows or (cmd) on a Mac',
								'wc-import-yandex'
							),
							__(
								'To deselect, press and hold (ctrl) or (cmd), click on the marked items',
								'wc-import-yandex'
							)
						),
						'woo_attr' => true,
						'default_value' => false,
						'key_value_arr' => [],
						'multiple' => true,
						'size' => '8',
						'tr_class' => 'ip2y_tr'
					]
				],
				[ 
					'opt_name' => 'sync_product_amount',
					'def_val' => 'enabled',
					'mark' => 'public',
					'required' => false,
					'type' => 'select',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => sprintf( '%s',
							__( 'Import the amount of the product', 'wc-import-yandex' )
						),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'wc-import-yandex' ) ]
						],
						'tr_class' => 'ip2y_tr'
					]
				],
				[ 
					'opt_name' => 'warehouse_id',
					'def_val' => '',
					'mark' => 'public',
					'required' => true,
					'type' => 'text',
					'tab' => 'main_tab',
					'data' => [ 
						'label' => __( 'Warehouse ID', 'wc-import-yandex' ),
						'desc' => '[warehouseId]',
						'placeholder' => ''
					]
				],
				[ 
					'opt_name' => 'how_skip_products',
					'def_val' => 'archive',
					'mark' => 'public',
					'required' => false,
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => sprintf( '%s',
							__( 'Delete or archive?', 'wc-import-yandex' )
						),
						'desc' => sprintf( '%s?',
							__(
								'Delete or archive products that were previously imported using the plugin and that you want to exclude from the list of imported products on Yandex Market',
								'wc-import-yandex'
							)
						),
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'archive', 'text' => __( 'Archive', 'wc-import-yandex' ) ],
							[ 'value' => 'delete', 'text' => __( 'Delete', 'wc-import-yandex' ) ]
						],
						'tr_class' => ''
					]
				],
				[ 
					'opt_name' => 'skip_missing_products',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => false,
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => sprintf( '%s (%s)',
							__( 'Skip missing products', 'wc-import-yandex' ),
							__( 'except for products for which a pre-order is permitted', 'wc-import-yandex' )
						),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'wc-import-yandex' ) ]
						],
						'tr_class' => ''
					]
				],
				[ 
					'opt_name' => 'skip_backorders_products',
					'def_val' => 'disabled',
					'mark' => 'public',
					'required' => false,
					'type' => 'select',
					'tab' => 'filtration_tab',
					'data' => [ 
						'label' => __( 'Skip backorders products', 'wc-import-yandex' ),
						'desc' => '',
						'woo_attr' => false,
						'key_value_arr' => [ 
							[ 'value' => 'disabled', 'text' => __( 'Disabled', 'wc-import-yandex' ) ],
							[ 'value' => 'enabled', 'text' => __( 'Enabled', 'wc-import-yandex' ) ]
						]
					]
				]
			];
		} else {
			$this->data_arr = $data_arr;
		}

		$this->data_arr = apply_filters( 'ip2y_f_set_default_feed_settings_result_arr', $this->get_data_arr() );
	}

	/**
	 * Get the plugin data array
	 * 
	 * @return array
	 */
	public function get_data_arr() {
		return $this->data_arr;
	}

	/**
	 * Get data for tabs
	 * 
	 * @param string $whot
	 * 
	 * @return array	Example: array([0] => opt_key1, [1] => opt_key2, ...)
	 */
	public function get_data_for_tabs( $whot = '' ) {
		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			// echo get_array_as_string($this->get_data_arr(), '<br/>');
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "main_tab":
					case "filtration_tab":
						if ( $this->get_data_arr()[ $i ]['tab'] === $whot ) {
							$arr = $this->get_data_arr()[ $i ]['data'];
							$arr['opt_name'] = $this->get_data_arr()[ $i ]['opt_name'];
							$arr['tab'] = $this->get_data_arr()[ $i ]['tab'];
							$arr['type'] = $this->get_data_arr()[ $i ]['type'];
							$res_arr[] = $arr;
						}
						break;
					case "api_tab":
						if ( $this->get_data_arr()[ $i ]['tab'] === 'api_tab' ) {
							$arr = $this->get_data_arr()[ $i ]['data'];
							$arr['opt_name'] = $this->get_data_arr()[ $i ]['opt_name'];
							$arr['tab'] = $this->get_data_arr()[ $i ]['tab'];
							$arr['type'] = $this->get_data_arr()[ $i ]['type'];
							$res_arr[] = $arr;
						}
						break;
					default:
						if ( $this->get_data_arr()[ $i ]['tab'] === $whot ) {
							$arr = $this->get_data_arr()[ $i ]['data'];
							$arr['opt_name'] = $this->get_data_arr()[ $i ]['opt_name'];
							$arr['tab'] = $this->get_data_arr()[ $i ]['tab'];
							$arr['type'] = $this->get_data_arr()[ $i ]['type'];
							$res_arr[] = $arr;
						}
				}
			}
			// echo get_array_as_string($res_arr, '<br/>');
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	/**
	 * Get plugin options name
	 * 
	 * @param string $whot
	 * 
	 * @return array	Example: array([0] => opt_key1, [1] => opt_key2, ...)
	 */
	public function get_opts_name( $whot = '' ) {
		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					default:
						$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
				}
			}
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	/**
	 * Get plugin options name and default date (array)
	 * 
	 * @param string $whot
	 * 
	 * @return array	Example: array(opt_name1 => opt_val1, opt_name2 => opt_val2, ...)
	 */
	public function get_opts_name_and_def_date( $whot = 'all' ) {
		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					default:
						$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
				}
			}
			return $res_arr;
		} else {
			return $res_arr;
		}
	}

	/**
	 * Get plugin options name and default date (stdClass object)
	 * 
	 * @param string $whot
	 * 
	 * @return array<stdClass>
	 */
	public function get_opts_name_and_def_date_obj( $whot = 'all' ) {
		$source_arr = $this->get_opts_name_and_def_date( $whot );

		$res_arr = [];
		foreach ( $source_arr as $key => $value ) {
			$obj = new stdClass();
			$obj->name = $key;
			$obj->opt_def_value = $value;
			$res_arr[] = $obj; // unit obj
			unset( $obj );
		}
		return $res_arr;
	}
}