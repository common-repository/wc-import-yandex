<?php
/**
 * The class will help you connect your store to Yandex Market using Yandex Market API
 *
 * @package                 Import Products to Yandex
 * @subpackage              
 * @since                   0.3.0
 * 
 * @version                 0.5.0 (21-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @param   WC_Product            $product - Required
 * @param   string                $actions - Required
 * @param   WC_Product_Variation  $offer - Required
 * @param   int                   $variation_count - Required
 * @param   string                $feed_id - Optional
 *
 * @depends                 classes:    IP2Y_Api
 *                                      IP2Y_Error_Log
 *                          traits:     IP2Y_T_Common_Get_CatId
 *                                      IP2Y_T_Common_Skips
 *                          methods:    
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

final class IP2Y_Api_Helper_Variable {
	use IP2Y_T_Common_Get_CatId;
	use IP2Y_T_Common_Skips;

	/**
	 * @var WC_Product
	 */
	protected $product;
	/**
	 * @var WC_Product_Variation
	 */
	protected $offer;
	/**
	 * Variation count
	 * @var int
	 */
	protected $variation_count;
	/**
	 * Feed ID
	 * @var string
	 */
	protected $feed_id;
	/**
	 * Result array
	 * @var array
	 */
	protected $result_arr = [];
	/**
	 * Skip reasons array
	 * @var array
	 */
	protected $skip_reasons_arr = [];

	/**
	 * The class will help you connect your store to Yandex Market using Yandex Market API. methodes
	 * 
	 * @param WC_Product $product
	 * @param string $actions - It can take values `set_products_prices`, `set_product_stocks`, `product_upd`, 
	 * `product_del`, `product_archive`, `product_unarchive`
	 * @param WC_Product_Variation $offer - Required
	 * @param int $variation_count - Required
	 * @param string $feed_id - Feed ID
	 * 
	 */
	public function __construct( $product, $actions, $offer, $variation_count, $feed_id = '1' ) {
		new IP2Y_Error_Log(
			sprintf( 'FEED № %1$s; %2$s product_id = %3$s offer_id = %4$s, actions = %5$s; Файл: %6$s; Строка: %7$s',
				$this->get_feed_id(),
				'Устанавливаем данные вариативного товара',
				$product->get_id(),
				$offer->get_id(),
				$actions,
				'class-ip2y-api-helper-variable.php',
				__LINE__
			)
		);
		$this->product = $product;
		$this->feed_id = $feed_id;
		$this->offer = $offer;
		$this->variation_count = $variation_count;
		$this->set_category_id();
		$this->get_skips();
		switch ( $actions ) {
			case 'set_products_prices':
				$this->set_products_prices();
				if ( ! empty( $this->get_skip_reasons_arr() ) ) {
					$this->result_arr = [];
				}
				break;
			case 'set_product_stocks':
				$this->set_product_stocks();
				if ( ! empty( $this->get_skip_reasons_arr() ) ) {
					$this->result_arr = [];
				}
				break;
			case 'product_upd':
				$this->product_upd();
				if ( ! empty( $this->get_skip_reasons_arr() ) ) {
					$this->result_arr = [];
				}
				break;
			case 'product_del':
				$this->product_del();
				break;
			case 'product_archive':
				$this->product_archive();
				break;
			case 'product_unarchive':
				$this->product_unarchive();
				break;
		}
	}

	/**
	 * Set products prices
	 * 
	 * @return void
	 */
	public function set_products_prices() {
		/**
		 * $product->get_price() - актуальная цена (равна sale_price или regular_price если sale_price пуст)
		 * $product->get_regular_price() - обычная цена
		 * $product->get_sale_price() - цена скидки
		 */

		$result_arr = [];
		$result_arr['offerId'] = $this->get_shop_sku();

		$price = $this->get_offer()->get_price();
		$regular_price = $this->get_offer()->get_regular_price();
		$sale_price = $this->get_offer()->get_sale_price();

		if ( $price > 0 && $price == $sale_price ) { // скидка есть
			$price_arr = [ 
				'value' => (float) $sale_price,
				'currencyId' => $this->get_shop_currency(),
				'discountBase' => $regular_price
			];
		} else { // скидки нет
			$price_arr = [ 
				'value' => (float) $regular_price,
				'currencyId' => $this->get_shop_currency()
			];
		}

		$price_arr = apply_filters(
			'yp2y_f_variable_prices',
			$price_arr,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		$result_arr['price'] = $price_arr;
		$this->result_arr = $result_arr;
	}

	/**
	 * Get shop currency to send to the API
	 * 
	 * @return string
	 */
	public function get_shop_currency() {
		$currency_arr = [ 
			'RUR', 'USD', 'EUR', 'UAH', 'AUD', 'GBP', 'BYR', 'BYN', 'DKK', 'ISK', 'KZT', 'CAD', 'CNY', 'NOK', 'XDR',
			'SGD', 'TRY', 'SEK', 'CHF', 'JPY', 'AZN', 'ALL', 'DZD', 'AOA', 'ARS', 'AMD', 'AFN', 'BHD', 'BGN', 'BOB',
			'BWP', 'BND', 'BRL', 'BIF', 'HUF', 'VEF', 'KPW', 'VND', 'GMD', 'GHS', 'GNF', 'HKD', 'GEL', 'AED', 'EGP',
			'ZMK', 'ILS', 'INR', 'IDR', 'JOD', 'IQD', 'IRR', 'YER', 'QAR', 'KES', 'KGS', 'COP', 'CDF', 'CRC', 'KWD',
			'CUP', 'LAK', 'LVL', 'SLL', 'LBP', 'LYD', 'SZL', 'LTL', 'MUR', 'MRO', 'MKD', 'MWK', 'MGA', 'MYR', 'MAD',
			'MXN', 'MZN', 'MDL', 'MNT', 'NPR', 'NGN', 'NIO', 'NZD', 'OMR', 'PKR', 'PYG', 'PEN', 'PLN', 'KHR', 'SAR',
			'RON', 'SCR', 'SYP', 'SKK', 'SOS', 'SDG', 'SRD', 'TJS', 'THB', 'TWD', 'BDT', 'TZS', 'TND', 'TMM', 'UGX',
			'UZS', 'UYU', 'PHP', 'DJF', 'XAF', 'XOF', 'HRK', 'CZK', 'CLP', 'LKR', 'EEK', 'ETB', 'RSD', 'ZAR', 'KRW',
			'NAD', 'TL', 'UE'
		];
		$wc_currency = get_woocommerce_currency();
		if ( ! in_array( $wc_currency, $currency_arr ) ) {
			$wc_currency = 'RUR';
		}

		$wc_currency = apply_filters(
			'yp2y_f_variable_value_currency',
			$wc_currency,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		return $wc_currency;
	}

	/**
	 * Set products stocks
	 * 
	 * @return void
	 */
	public function set_product_stocks() {
		$product_stocks_arr = [ 
			'sku' => $this->get_shop_sku(),
			'items' => [ 
				[ 
					'count' => $this->get_stock_amount() // , 
					// 'updatedAt' => '2022-12-29T18:02:01Z'
				]
			]
		];

		$product_stocks_arr = apply_filters(
			'yp2y_f_variable_product_stocks_arr',
			$product_stocks_arr,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		$this->result_arr = $product_stocks_arr;
	}

	/**
	 * Get stock amount
	 * 
	 * @return int
	 */
	public function get_stock_amount() {
		if ( false === $this->get_offer()->is_in_stock() ) {
			// товара нет в наличии
			return 0;
		} else {
			if ( true == $this->get_offer()->get_manage_stock() ) { // если включено управление запасом
				return $this->get_offer()->get_stock_quantity(); // вернём реальные остатки
			} else { // если отключено управление запасом
				if ( $this->get_offer()->get_stock_status() === 'instock' ) {
					return 1;
				} else if ( $this->get_offer()->get_stock_status() === 'outofstock' ) {
					return 0;
				} else { // onbackorder (предзаказ)
					return 0;
				}
			}
		}
	}

	/**
	 * Sets the data for updating the product
	 * 
	 * @return void
	 */
	public function product_upd() {
		// обязательны // ? исправить offerId, name, category, pictures, vendor, description
		$shop_sku = $this->get_shop_sku();
		if ( empty( $shop_sku ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'The product does not have a shopSku', 'wc-import-yandex' ),
				'post_id' => $this->get_product()->get_id(),
				'offer_id' => $this->get_offer()->get_id(),
				'file' => 'class-ip2y-yandex-api-helper-variable.php',
				'line' => __LINE__
			] );
			return;
		}

		$category_name = $this->get_category_name(); // $this->get_feed_category_id()

		$pictures_arr = $this->get_pictures();
		if ( empty( $pictures_arr ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'The product does not have a photo', 'wc-import-yandex' ),
				'post_id' => $this->get_product()->get_id(),
				'offer_id' => $this->get_offer()->get_id(),
				'file' => 'class-ip2y-api-helper-variable.php',
				'line' => __LINE__
			] );
			return;
		}

		$description = $this->get_description();
		if ( empty( $description ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'The product does not have a description', 'wc-import-yandex' ),
				'post_id' => $this->get_product()->get_id(),
				'offer_id' => $this->get_offer()->get_id(),
				'file' => 'class-ip2y-api-helper-variable.php',
				'line' => __LINE__
			] );
			return;
		}

		$manufacturer_countries = $this->get_manufacturer_countries();
		if ( empty( $manufacturer_countries ) ) {
			$this->add_skip_reason( [ 
				'reason' => __( 'The product does not have a manufacturer countries', 'wc-import-yandex' ),
				'post_id' => $this->get_product()->get_id(),
				'offer_id' => $this->get_offer()->get_id(),
				'file' => 'class-ip2y-yandex-api-helper-variable.php',
				'line' => __LINE__
			] );
			return;
		}

		$offer_arr = [ 
			'offerId' => $shop_sku,
			'name' => $this->get_name(),
			'category' => $category_name,
			'pictures' => $pictures_arr,
			'description' => $description,
			'manufacturerCountries' => $manufacturer_countries
		];

		$offer_arr = $this->get_videos( $offer_arr );

		$offer_arr = $this->get_weight_dimensions( $offer_arr );
		$offer_arr = $this->get_vendorcode( $offer_arr );
		$offer_arr = $this->get_manuals( $offer_arr );
		$offer_arr = $this->get_vendor( $offer_arr );
		$offer_arr = $this->get_barcodes( $offer_arr );
		$offer_arr = $this->get_tags( $offer_arr );
		$offer_arr = $this->get_shelf_life( $offer_arr );
		$offer_arr = $this->get_life_time( $offer_arr );
		$offer_arr = $this->get_guarantee_period( $offer_arr );
		$offer_arr = $this->get_customs_commodity_code( $offer_arr );
		$offer_arr = $this->get_certificates( $offer_arr );
		$offer_arr = $this->get_box_count( $offer_arr );
		$offer_arr = $this->get_condition( $offer_arr );
		$offer_arr = $this->get_type( $offer_arr );
		$offer_arr = $this->get_downloadable( $offer_arr );
		$offer_arr = $this->get_adult( $offer_arr );
		$offer_arr = $this->get_age( $offer_arr );
		$offer_arr = $this->get_params( $offer_arr );
		$offer_arr = $this->get_purchase_price( $offer_arr );
		$offer_arr = $this->get_additional_expenses( $offer_arr );
		$offer_arr = $this->get_cofinance_price( $offer_arr );

		// @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/updateOfferMappings
		$this->result_arr = [ 
			'offer' => $offer_arr
		];

		$market_sku = $this->get_market_sku();
		if ( ! empty( $market_sku ) ) {
			// 'offer' => [...],
			// 'mapping' => [
			//     "marketSku" => (int) 0
			// ]
			$this->result_arr['mapping']['marketSku'] = (int) $market_sku;
		}

		$this->result_arr = apply_filters(
			'ip2y_f_variable_helper_result_arr',
			$this->result_arr,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
	}

	/**
	 * Get shop SKU
	 * 
	 * @return string
	 */
	public function get_shop_sku() {
		$shop_sku = '';
		$source_shop_sku = common_option_get( 'source_shop_sku', false, $this->get_feed_id(), 'ip2y' );
		if ( $source_shop_sku === 'sku' ) {
			$shop_sku = $this->get_offer()->get_sku();
		}
		if ( empty( $shop_sku ) ) {
			// если у вариации товара вукомерц нет артикула - используем ID вариации
			$shop_sku = (string) $this->get_offer()->get_id();
		}

		$prefix_shop_sku = common_option_get( 'prefix_shop_sku', false, $this->get_feed_id(), 'ip2y' );
		if ( ! empty( $prefix_shop_sku ) ) {
			$shop_sku = $prefix_shop_sku . $shop_sku;
		}

		$shop_sku = apply_filters( 'ip2y_f_variable_shop_sku',
			$shop_sku,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		return $shop_sku;
	}

	/**
	 * Get product name
	 * 
	 * @return string
	 */
	public function get_name() {
		$name = $this->get_product()->get_title();
		$name = apply_filters( 'ip2y_f_variable_name',
			$name,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		return $name;
	}

	/**
	 * Get category name
	 * 
	 * @return string|null
	 */
	public function get_category_name() {
		$category_ids_arr = $this->get_product()->get_category_ids();
		if ( empty( $category_ids_arr ) ) {
			return null;
		} else {
			$this->category_id = (int) $category_ids_arr[0];
			$term = get_term_by( 'id', $category_ids_arr[0], 'product_cat' );
			return $term->name; // get_cat_name($category_ids_arr[0]);
		}
	}

	/**
	 * Get the Picture info 
	 * 
	 * @return array
	 */
	public function get_pictures() {
		$res_arr = [];
		$thumb_id = get_post_thumbnail_id( $this->get_offer()->get_id() );
		if ( empty( $thumb_id ) ) {
			$thumb_id = get_post_thumbnail_id( $this->get_product()->get_id() );
		}
		if ( ! empty( $thumb_id ) ) { // есть картинка у товара
			// если она больше 8 Мб, то пробуем вытащить более мелкие размеры
			if ( filesize( get_attached_file( $thumb_id ) ) > 8388608 ) {
				$thumb_url = wp_get_attachment_image_url( $thumb_id, 'large', true );
				if ( filesize( $thumb_url ) > 8388608 ) {
					$thumb_url = wp_get_attachment_image_url( $thumb_id, 'medium', true );
				}
			} else {
				$thumb_url = wp_get_attachment_image_url( $thumb_id, 'full', true );
			}
			$res_arr[] = $thumb_url;
		}
		$res_arr = apply_filters(
			'yp2y_f_variable_pictures',
			$res_arr,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
			],
			$this->get_feed_id()
		);
		return $res_arr;
	}

	/**
	 * Get product description
	 * 
	 * @return string
	 */
	public function get_description() {
		$description_source = common_option_get( 'description', false, $this->get_feed_id(), 'ip2y' );
		$var_desc_priority = common_option_get( 'var_desc_priority', false, $this->get_feed_id(), 'ip2y' );
		$desc_val = '';

		// вариации
		if ( $var_desc_priority === 'enabled' ) {
			$desc_val = $this->get_offer()->get_description();
		}

		switch ( $description_source ) {
			case "full":
				// сейчас и далее проверка на случай, если описание вариации главнее
				if ( empty( $desc_val ) ) {
					$desc_val = $this->get_product()->get_description();
				}
				break;
			case "excerpt":
				if ( empty( $desc_val ) ) {
					$desc_val = $this->get_product()->get_short_description();
				}
				break;
			case "fullexcerpt":
				if ( empty( $desc_val ) ) {
					$desc_val = $this->get_product()->get_description();
					if ( empty( $desc_val ) ) {
						$desc_val = $this->get_product()->get_short_description();
					}
				}
				break;
			case "excerptfull":
				if ( empty( $desc_val ) ) {
					$desc_val = $this->get_product()->get_short_description();
					if ( empty( $desc_val ) ) {
						$desc_val = $this->get_product()->get_description();
					}
				}
				break;
			case "fullplusexcerpt":
				if ( $var_desc_priority === 'enabled' ) {
					$desc_val = sprintf( '%1$s<br/>%2$s',
						$this->get_offer()->get_description(),
						$this->get_product()->get_short_description()
					);
				} else {
					$desc_val = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_description(),
						$this->get_product()->get_short_description()
					);
				}
				break;
			case "excerptplusfull":
				if ( $var_desc_priority === 'enabled' ) {
					$desc_val = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_short_description(),
						$this->get_offer()->get_description()
					);
				} else {
					$desc_val = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_short_description(),
						$this->get_product()->get_description()
					);
				}
				break;
			default:
				if ( empty( $desc_val ) ) { // проверка на случай, если описание вариации главнее
					$desc_val = $this->get_product()->get_description();
					$desc_val = apply_filters( 'ip2y_f_variable_switchcase_default_description',
						$desc_val,
						[ 
							'description_source' => $description_source,
							'product' => $this->get_product(),
							'offer' => $this->get_offer()
						],
						$this->get_feed_id()
					);
				}
		}

		$desc_val = apply_filters( 'ip2y_f_variable_description',
			$desc_val,
			[ 
				'description_source' => $description_source,
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		// Заменим переносы строк, чтоб не вываливалась ошибка аттача
		// $desc_val = str_replace( [ "\r\n", "\r", "\n", PHP_EOL ], "\\n", $desc_val);
		$desc_val = wp_strip_all_tags( $desc_val );
		// $desc_val = htmlspecialchars($desc_val);
		return $desc_val;
	}

	/**
	 * Get manufacturer countries
	 * 
	 * @return array
	 */
	public function get_manufacturer_countries() {
		$manufacturer_countries = common_option_get( 'manufacturer_countries', false, $this->get_feed_id(), 'ip2y' );
		if ( empty( $manufacturer_countries ) || $manufacturer_countries == 'disabled' ) {
			return [];
		} else {
			$manufacturer_countries = (int) $manufacturer_countries;
			$value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $manufacturer_countries ) );
			if ( empty( $value ) ) {
				$value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $manufacturer_countries ) );
			}
			return explode( ',', $value );
		}
	}

	/**
	 * Get videos array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_videos( $offer_arr ) {
		// 'videos' => [ "string" ],
		return $offer_arr;
	}

	/**
	 * Get weight and dimensions array
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_weight_dimensions( $offer_arr ) {
		$weight_dimensions_arr = [];

		if ( $this->get_offer()->has_dimensions() ) {
			$length = $this->get_offer()->get_length();
			$width = $this->get_offer()->get_width();
			$height = $this->get_offer()->get_height();
		}
		if ( get_post_meta( $this->get_offer()->get_id(), '_ip2y_length', true ) !== '' ) {
			$length = (float) get_post_meta( $this->get_offer()->get_id(), '_ip2y_length', true );
		}
		if ( get_post_meta( $this->get_offer()->get_id(), '_ip2y_width', true ) !== '' ) {
			$width = (float) get_post_meta( $this->get_offer()->get_id(), '_ip2y_width', true );
		}
		if ( get_post_meta( $this->get_offer()->get_id(), '_ip2y_height', true ) !== '' ) {
			$height = (float) get_post_meta( $this->get_offer()->get_id(), '_ip2y_height', true );
		}
		if ( ! empty( $length ) ) { // глубина
			$weight_dimensions_arr['length'] = round( wc_get_dimension( $length, 'cm' ), 3 );
		}
		if ( ! empty( $width ) ) { // ширина
			$weight_dimensions_arr['width'] = round( wc_get_dimension( $width, 'cm' ), 3 );
		}
		if ( ! empty( $height ) ) { // выстоа
			$weight_dimensions_arr['height'] = round( wc_get_dimension( $height, 'cm' ), 3 );
		}

		$weight = $this->get_offer()->get_weight(); // вес
		if ( get_post_meta( $this->get_offer()->get_id(), '_ip2y_weight', true ) !== '' ) {
			$weight = (float) get_post_meta( $this->get_offer()->get_id(), '_ip2y_weight', true );
			$weight = $weight / 1000;
		}
		if ( ! empty( $weight ) ) {
			$weight_dimensions_arr['weight'] = round( wc_get_weight( $weight, 'kg' ), 3 );
		}

		$weight_dimensions_arr = apply_filters( 'ip2y_f_variable_weight_dimensions_arr',
			$weight_dimensions_arr,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $weight_dimensions_arr ) ) {
			$offer_arr['weightDimensions'] = $weight_dimensions_arr;
		}

		return $offer_arr;
	}

	/**
	 * Get vendorcode
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_vendorcode( $offer_arr ) {
		$vybor = common_option_get( 'vendorcode', false, $this->get_feed_id(), 'ip2y' );
		if ( empty( $vybor ) || $vybor == 'disabled' ) {
			$value = '';
		} else if ( $vybor === 'sku' ) {
			$value = $this->get_offer()->get_sku();
		} else {
			$vendorcode_attr_id = (int) $vybor;
			$value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $vendorcode_attr_id ) );
			if ( empty( $value ) ) {
				$value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $vendorcode_attr_id ) );
			}
		}
		$value = apply_filters( 'ip2y_f_variable_vendorcode',
			$value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'vybor' => $vybor
			],
			$this->get_feed_id()
		);
		if ( ! empty( $value ) ) {
			$offer_arr['vendorCode'] = $value;
		}
		return $offer_arr;
	}

	/**
	 * Get manuals array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_manuals( $offer_arr ) {
		// "manuals": [
		//	{
		//		"url": "string",
		//		"title": "string"
		//	}
		// ],
		return $offer_arr;
	}

	/**
	 * Get vendor
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_vendor( $offer_arr ) {
		$vybor = common_option_get( 'vendor', false, $this->get_feed_id(), 'ip2y' );
		if ( empty( $vybor ) || $vybor == 'disabled' ) {
			$value = '';
		} else {
			$vendor_attr_id = (int) $vybor;
			$value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $vendor_attr_id ) );
			if ( empty( $value ) ) {
				$value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $vendor_attr_id ) );
			}
		}
		$value = apply_filters( 'ip2y_f_variable_vendor',
			$value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'vybor' => $vybor
			],
			$this->get_feed_id()
		);
		if ( empty( $value ) ) {
			$offer_arr['vendor'] = 'Нет бренда';
		} else {
			$offer_arr['vendor'] = $value;
		}
		return $offer_arr;
	}

	/**
	 * Get barcodes array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_barcodes( $offer_arr ) {
		// 'barcodes' => [
		//    '46012300000000'
		// ],
		return $offer_arr;
	}

	/**
	 * Get tags
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_tags( $offer_arr ) {
		$result = null;
		if ( get_post_meta( $this->get_product()->get_id(), '_ip2y_tags', true ) !== '' ) {
			$result = (string) get_post_meta( $this->get_product()->get_id(), '_ip2y_tags', true );
			$offer_arr = [ $result ];
		}
		return $offer_arr;
	}

	/**
	 * Get shelf life array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_shelf_life( $offer_arr ) {
		// 'shelfLife' => [
		//   'timePeriod' => 0,
		//   'timeUnit'=>'HOUR',
		//   'comment'=>'string'
		// ],
		return $offer_arr;
	}

	/**
	 * Get life time array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_life_time( $offer_arr ) {
		// 'lifeTime' => [
		//     'timePeriod' => 0,
		//     'timeUnit'=>'HOUR',
		//     'comment'=>'string'
		// ],
		return $offer_arr;
	}

	/**
	 * Get guarantee period array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_guarantee_period( $offer_arr ) {
		// 'guaranteePeriod' => [
		//     'timePeriod' => 0,
		//     'timeUnit'=>'HOUR',
		//     'comment'=>'string'
		// ],
		return $offer_arr;
	}

	/**
	 * Get customs commodity code int
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_customs_commodity_code( $offer_arr ) {
		// 'customsCommodityCode' => 8517610008,
		return $offer_arr;
	}

	/**
	 * Get certificates array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_certificates( $offer_arr ) {
		// 'certificates' => [
		//     'string'
		// ],
		return $offer_arr;
	}

	/**
	 * Get box count int
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_box_count( $offer_arr ) {
		// 'boxCount' => 0,
		return $offer_arr;
	}

	/**
	 * Get condition array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_condition( $offer_arr ) {
		// 'condition' => [
		//     'type'=>'PREOWNED',
		//     'quality'=>'PERFECT',
		//     'reason'=>'string'
		// ],
		return $offer_arr;
	}

	/**
	 * Get type string
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_type( $offer_arr ) {
		$type = 'DEFAULT';
		$type = apply_filters( 'ip2y_f_variable_type',
			$type,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		$offer_arr['type'] = $type;
		return $offer_arr;
	}

	/**
	 * Get downloadable bool
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_downloadable( $offer_arr ) {
		if ( $this->get_product()->is_downloadable() ) {
			$offer_arr['downloadable'] = true;
		} else {
			$offer_arr['downloadable'] = false;
		}
		return $offer_arr;
	}

	/**
	 * Get adult bool
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_adult( $offer_arr ) {
		$offer_arr['adult'] = false;
		return $offer_arr;
	}

	/**
	 * Get age array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_age( $offer_arr ) {
		// 'age' => [
		//     'value' => 0,
		//     'ageUnit'=>'YEAR'
		// ],
		return $offer_arr;
	}

	/**
	 * Get params arr
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_params( $offer_arr ) {
		$result_arr = [];
		$params_arr = maybe_unserialize( univ_option_get( 'params_arr' . $this->get_feed_id() ) );

		if ( ! empty( $params_arr ) ) {
			$attributes = $this->get_product()->get_attributes();
			foreach ( $attributes as $param ) {
				if ( false == $param->get_variation() ) {
					// это обычный атрибут
					$param_val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				} else { // это атрибут вариации
					$param_val = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				}

				// если этот параметр не нужно выгружать - пропускаем
				$variation_id_string = (string) $param->get_id(); // важно, т.к. в настройках id как строки
				if ( ! in_array( $variation_id_string, $params_arr, true ) ) {
					continue;
				}
				$param_name = wc_attribute_label( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				// если пустое имя атрибута или значение - пропускаем
				if ( empty( $param_name ) || empty( $param_val ) ) {
					continue;
				}

				array_push( $result_arr, [ 
					'name' => ucfirst( $param_name ),
					'value' => htmlspecialchars( $param_val )
				] );
			}
		}
		if ( ! empty( $result_arr ) ) {
			$offer_arr['params'] = $result_arr;
		}

		return $offer_arr;
	}

	/**
	 * Set skip reasons
	 * 
	 * @param string $v
	 * 
	 * @return void
	 */
	public function set_skip_reasons_arr( $v ) {
		$this->skip_reasons_arr[] = $v;
	}

	/**
	 * Get skip reasons
	 * 
	 * @return array
	 */
	public function get_skip_reasons_arr() {
		return $this->skip_reasons_arr;
	}

	/**
	 * Add skip reason
	 * 
	 * @param array $reason
	 * 
	 * @return void
	 */
	protected function add_skip_reason( $reason ) {
		if ( isset( $reason['offer_id'] ) ) {
			$reason_string = sprintf(
				'FEED № %1$s; Вариация товара (postId = %2$s, offer_id = %3$s) пропущена. Причина: %4$s; Файл: %5$s; Строка: %6$s',
				$this->feed_id, $reason['post_id'], $reason['offer_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		} else {
			$reason_string = sprintf(
				'FEED № %1$s; Товар с postId = %2$s пропущен. Причина: %3$s; Файл: %4$s; Строка: %5$s',
				$this->feed_id, $reason['post_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		}

		$this->set_skip_reasons_arr( $reason_string );
		new IP2Y_Error_Log( $reason_string );
	}

	/**
	 * Get purchase price array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_purchase_price( $offer_arr ) {
		//  'purchasePrice' => [
		//     'value' => 0,
		//     'currencyId'=>'RUR'
		// ],
		return $offer_arr;
	}

	/**
	 * Get additional price array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_additional_expenses( $offer_arr ) {
		// 'additionalExpenses' => [
		//     'value' => 0,
		//    
		return $offer_arr;
	}

	/**
	 * Get cofinance price array
	 * TODO: сделать
	 * 
	 * @param array $offer_arr
	 * 
	 * @return array
	 */
	public function get_cofinance_price( $offer_arr ) {
		// 'cofinancePrice' => [
		//     'value' => 0,
		//     'currencyId'=>'RUR'
		// ]  
		return $offer_arr;
	}

	/**
	 * Sets the data for delete the product
	 * 
	 * @return void
	 */
	public function product_del() {
		$product_shop_sku_on_yandex = $this->get_shop_sku();
		if ( null !== $product_shop_sku_on_yandex ) {
			array_push( $this->result_arr, $product_shop_sku_on_yandex );
		}
	}

	/**
	 * Sets the data for transferring the product to the archive
	 * 
	 * @return void
	 */
	public function product_archive() {
		$product_shop_sku_on_yandex = $this->get_shop_sku();
		if ( null !== $product_shop_sku_on_yandex ) {
			array_push( $this->result_arr, $product_shop_sku_on_yandex );
		}
	}

	/**
	 * Sets the data for transferring the product from the archive
	 * 
	 * @return void
	 */
	public function product_unarchive() {
		$product_shop_sku_on_yandex = $this->get_shop_sku();
		if ( null !== $product_shop_sku_on_yandex ) {
			array_push( $this->result_arr, $product_shop_sku_on_yandex );
		}
	}

	/**
	 * Get market SKU
	 * 
	 * @return string
	 */
	public function get_market_sku() {
		$result = null;
		if ( get_post_meta( $this->get_product()->get_id(), '_ip2y_market_sku', true ) !== '' ) {
			$result = (string) get_post_meta( $this->get_product()->get_id(), '_ip2y_market_sku', true );
		}
		return $result;
	}

	/* Getters */

	/**
	 * Get product
	 * 
	 * @return WC_Product
	 */
	public function get_product() {
		return $this->product;
	}

	/**
	 * Get product variation
	 * 
	 * @return WC_Product_Variation
	 */

	public function get_offer() {
		return $this->offer;
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	public function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get result
	 * 
	 * @return array
	 */
	public function get_result() {
		return $this->result_arr;
	}
}