<?php
/**
 * The class will help you connect your store to Yandex Market using Yandex Market API
 *
 * @package                 Import Products to Yandex
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 0.5.0 (21-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://yandex.ru/dev/market/partner-api/doc/ru/
 *                          https://oauth.yandex.ru/client/new
 *
 * @param       array       $args_arr - Optional
 *
 * @depends                 classes:    IP2Y_Api_Helper
 *                                      IP2Y_Error_Log
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

final class IP2Y_Api {
	/**
	 * Идентификатор приложения ClientID (из приложения)
	 * @var string
	 */
	protected $client_id;
	/**
	 * Токен который выдаётся нам в случае успешной авторизации вида y0_AgAA************Vog
	 * @var string
	 */
	protected $access_token;
	/**
	 * Номер кампании (со страницы https://partner.market.yandex.ru/supplier/XXXXXXX/api/settings)
	 * @var string
	 */
	protected $campaign_id;
	/**
	 * ID кабинета (https://partner.market.yandex.ru/business/XXXXXXX/)
	 * @var string
	 */
	protected $businesses_id;
	/**
	 * Секретный ключ, которым будет подписан jwt-токен (из приложения)
	 * @var string
	 */
	protected $client_secret;

	/**
	 * Добавляет к url запроса GET-параметр для дебага
	 * @var string
	 */
	protected $debug;
	/**
	 * Feed ID
	 * @var string
	 */
	protected $feed_id = '1';

	/**
	 * The main class for working with the Yandex API
	 * 
	 * @param array $args_arr - Optional
	 */
	public function __construct( $args_arr = [] ) {
		if ( isset( $args_arr['client_id'] ) ) {
			$this->client_id = $args_arr['client_id'];
		} else {
			$this->client_id = common_option_get( 'client_id', false, $this->get_feed_id(), 'ip2y' );
		}
		if ( isset( $args_arr['access_token'] ) ) {
			$this->access_token = $args_arr['access_token'];
		} else {
			$this->access_token = common_option_get( 'access_token', false, $this->get_feed_id(), 'ip2y' );
		}
		if ( isset( $args_arr['campaign_id'] ) ) {
			$this->campaign_id = $args_arr['campaign_id'];
		} else {
			$this->campaign_id = common_option_get( 'campaign_id', false, $this->get_feed_id(), 'ip2y' );
		}
		if ( isset( $args_arr['businesses_id'] ) ) {
			$this->businesses_id = $args_arr['businesses_id'];
		} else {
			$this->businesses_id = common_option_get( 'businesses_id', false, $this->get_feed_id(), 'ip2y' );
		}
		if ( isset( $args_arr['client_secret'] ) ) {
			$this->client_secret = $args_arr['client_secret'];
		} else {
			$this->client_secret = common_option_get( 'client_secret', false, $this->get_feed_id(), 'ip2y' );
		}
		if ( isset( $args_arr['debug'] ) ) {
			$this->debug = $args_arr['debug'];
		}
		if ( isset( $args_arr['feed_id'] ) ) {
			$this->feed_id = $args_arr['feed_id'];
		}

		add_action( 'parse_request', [ $this, 'listen_request' ] ); // Хук парсера запросов
	}

	/**
	 * Listen request button
	 * 
	 * @return void
	 */
	public function listen_request() {
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			if ( isset( $_GET['code'] ) && isset( $_GET['state'] ) ) {
				// Формирование параметров (тела) POST-запроса с указанием кода подтверждения
				$postfields_arr = [ 
					'grant_type' => 'authorization_code',
					'code' => sanitize_key( $_GET['code'] ),
					'client_id' => $this->get_client_id(),
					'client_secret' => $this->get_client_secret()
				];

				$answer_arr = $this->response_to_yandex(
					'https://oauth.yandex.ru/token',
					$postfields_arr,
					[ 
						'Content-type' => 'application/x-www-form-urlencoded'
					],
					'POST',
					[],
					'http_build_query'
				);

				// Если при получении токена произошла ошибка
				if ( isset( $answer_arr['body_answer']->errors ) ) {
					new IP2Y_Error_Log(
						sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
							$this->get_feed_id(),
							'При получении токена произошла ошибка',
							$answer_arr['body_answer']->errors[0]->code,
							$answer_arr['body_answer']->errors[0]->message,
							'class-ip2y-api.php',
							__LINE__
						)
					);
				}

				// в случае успеха возвращает:
				// ["access_token"]=> string(61) "y0_AgAAAA*******AiyyAAAAAg"  - OAuth-токен с запрошенными правами
				// ["expires_in"]=> int(3146536) - Время жизни токена в секундах
				// ["refresh_token"]=> string(124) "1:f8cTB1HXd:a-qcA*******viyQ" 
				// ["token_type"]=> string(6) "bearer"
				$access_token = $answer_arr['body_answer']->access_token;

				// Токен, который можно использовать для продления срока жизни соответствующего OAuth-токена
				// https://tech.yandex.ru/oauth/doc/dg/reference/refresh-client-docpage/#refresh-client
				// $refreshToken = $response->refresh_token;		
				// Сохраняем токен в сессии
				// $_SESSION['yaToken'] = [ 'access_token' => $access_token, 'refresh_token' => $refreshToken ];
				common_option_upd( 'access_token', $access_token, 'no', $this->get_feed_id(), 'ip2y' );

				printf( '<script type="text/javascript"> var hash = window.location.hash;
					var t = hash.replace("#", "&");	var url = \'%s\'; window.location.href = url + t;</script>',
					get_site_url( null, '/wp-admin/admin.php?page=ip2y-import&tab=api_tab&feed_id=1' )
				);
			} elseif ( isset( $_GET['error'] ) ) { // Если при авторизации произошла ошибка
				throw new Exception( 'При авторизации произошла ошибка. Error: ' . sanitize_key( $_GET['error'] )
					. '. Error description: ' . sanitize_key( $_GET['error_description'] ) );
			}
		}
	}

	/**
	 * Возвращает список магазинов, к которым имеет доступ пользователь — владелец авторизационного токена
	 * 
	 * @version			0.1.0
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/campaigns/getCampaigns
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *			и:
	 *					['body_answer'] - object
	 *				или:
	 * 					['errors'] - array 
	 * 						- [0]["code"] => int(101)
	 *						- [0]["message"] => string(37)
	 */
	public function get_campaigns() {
		$result = [ 
			'status' => false
		];

		$params_arr = [];

		$answer_arr = $this->response_to_yandex(
			'https://api.partner.market.yandex.ru/campaigns',
			$params_arr,
			$this->get_headers_arr(),
			'GET',
			[],
			'http_build_query'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			new IP2Y_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Ошибка получения списка магазинов',
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					'class-ip2y-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		}
		// в случае успеха yandex возвращает:
		// [status] => (boolean)1
		// [http_code] => (integer)200
		// [body_answer] => (object)
		// ---[campaigns] => (array)---
		// ------[0] => (object)------
		// ---------[domain] => (string)iCopyDoc
		// ---------[id] => (integer)84006121
		// ---------[clientId] => (integer)107702234
		// ---------[business] => (object)---------
		// ------------[id] => (integer)71124214
		// ------------[name] => (string)iCopyDoc
		// ---------[placementType] => (string)FBS
		// ---[pager] => (object)---
		// ------[total] => (integer)1
		// ------[from] => (integer)1
		// ------[to] => (integer)1
		// ------[currentPage] => (integer)1
		// ------[pagesCount] => (integer)1
		// ------[pageSize] => (integer)1

		$result = [ 
			'status' => true,
			'body_answer' => $answer_arr['body_answer']
		];

		return $result;
	}

	/**
	 * Возвращает список товаров в каталоге с параметрами каждого товара
	 * 
	 * @version			0.1.0 
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/getOfferMappings
	 * 
	 * @param	array	$your_sku_on_yandex - Aртикулы товаров на нашем сайте, например: `test-cat-12312-black-white`
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id импортированного товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function get_offer_card( $your_sku_on_yandex ) {
		$mapping_array = [];

		$result = [ 
			'status' => false
		];

		$data = [ 
			'offerIds' => $your_sku_on_yandex
		];

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/businesses/%s/offer-mappings',
				$this->get_businesses_id()
			),
			$data,
			$this->get_headers_arr(),
			'POST',
			[],
			'json_encode'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			new IP2Y_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Ошибка получения списка магазинов',
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					'class-ip2y-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		} else {
			if ( isset( $answer_arr['body_answer']->result->offerMappings )
				&& ! empty( $answer_arr['body_answer']->result->offerMappings )
			) {
				for ( $i = 0; $i < count( $answer_arr['body_answer']->result->offerMappings ); $i++ ) {
					// товар в личном кабинете на маркете есть. проверим, есть ли карточка
					$mapping_obj = $answer_arr['body_answer']->result->offerMappings[ $i ]->mapping;
					$card_status = $answer_arr['body_answer']->result->offerMappings[ $i ]->offer->cardStatus;
					if ( is_object( $mapping_obj ) && property_exists( $mapping_obj, 'marketSku' ) ) {
						// если карточка создана
						$market_sku = $mapping_obj->marketSku;
					} else {
						$market_sku = null;
					}
					if ( property_exists( $mapping_obj, 'marketModelId' ) ) {
						// если карточка создана
						$market_model_id = $mapping_obj->marketModelId;
						$market_category_id = $mapping_obj->marketCategoryId;
					} else {
						$market_model_id = null;
						$market_category_id = null;
					}
					array_push( $mapping_array,
						[ 
							'market_sku' => $market_sku,
							'market_model_id' => $market_model_id,
							'market_category_id' => $market_category_id,
							'market_card_status' => $card_status
						]
					);
				}
			} else {
				// товара в личном кабинете на маркете вообще нет
				array_push( $mapping_array,
					[ 
						'market_sku' => null,
						'market_model_id' => null,
						'market_category_id' => null,
						'market_card_status' => null
					]
				);
			}
		}

		$result = [ 
			'status' => true,
			'mapping_array' => $mapping_array,
			'body_answer' => $answer_arr['body_answer']->result
		];

		return $result;
	}

	/**
	 * Синхронизация товаров
	 * 
	 * @version			0.1.0
	 * @see				
	 * 
	 * @param	int		$product_id - Required
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_sync( $product_id ) {
		$answer_arr = [ 
			'status' => false,
			'skip_reasons' => []
		];

		$helper = new IP2Y_Api_Helper();
		$helper->set_product_data( $product_id, 'product_upd' );
		if ( ! empty( $helper->get_skip_reasons_arr() ) ) {
			// $answer_arr['skip_reasons'] = $helper->get_skip_reasons_arr();
			array_push( $answer_arr['skip_reasons'], $helper->get_skip_reasons_arr() );
		}

		$product_sku_list_arr = $helper->get_product_sku_list_arr();
		// $shop_sku_arr = $helper->get_shop_sku_arr();

		for ( $i = 0; $i < count( $product_sku_list_arr ); $i++ ) {
			$your_sku_on_yandex = $product_sku_list_arr[ $i ]['your_sku_on_yandex'];
			// $sku_on_yandex = $product_sku_list_arr[ $i ]['market_sku_on_yandex'];
			$post_id_on_wp = $product_sku_list_arr[ $i ]['post_id_on_wp'];
			$have_get_result = $product_sku_list_arr[ $i ]['have_get_result'];
			// $prod_id_on_yandex = get_post_meta( $post_id_on_wp, '_ip2y_prod_id_on_yandex', true );
			if ( (int) $product_id == (int) $post_id_on_wp ) {
				$msg = 'Простой товар';
			} else {
				$msg = 'Вариация товара';
			}

			if ( false === $have_get_result && ! empty( $your_sku_on_yandex ) ) {
				$how_skip_products = common_option_get( 'how_skip_products', false, $this->get_feed_id(), 'ip2y' );
				if ( $how_skip_products === 'delete' ) {

					// этот товар надо удалить
					$res_d = $this->product_del( [ $your_sku_on_yandex ] );
					if ( true == $res_d['status'] ) {
						$helper->set_product_exists(
							$post_id_on_wp,
							[ 
								'market_sku_on_yandex' => '',
								'product_id_on_yandex' => '',
								'product_archive_status' => '',
								'your_sku_on_yandex' => ''
							]
						);
						echo 'успешно удалили';
					} else {
						// ! ошибка удаления товара
						new IP2Y_Error_Log(
							sprintf( 'FEED № %1$s; ERROR: %2$s (product_id = %3$s, post_id_on_wp = %4$s) %5$s; Файл: %6$s; Строка: %7$s',
								$this->get_feed_id(),
								$msg,
								$product_id,
								$post_id_on_wp,
								'Ошибка удаления',
								'class-ip2y-api.php',
								__LINE__
							)
						);
					}
				} else {
					// этот товар надо перенести в архив
					$res_d = $this->product_archive( [ $your_sku_on_yandex ] );
					if ( true == $res_d['status'] ) {
						$helper->set_product_exists( $post_id_on_wp, [ 'product_archive_status' => 'archived' ] );
					} else {
						// ! ошибка переноса товара в архив
						new IP2Y_Error_Log(
							sprintf( 'FEED № %1$s; ERROR: %2$s (product_id = %3$s, post_id_on_wp = %4$s) %5$s; Файл: %6$s; Строка: %7$s',
								$this->get_feed_id(),
								$msg,
								$product_id,
								$post_id_on_wp,
								'Ошибка переноса в архив',
								'class-ip2y-api.php',
								__LINE__
							)
						);
					}
				}
			}

			usleep( 100000 ); // притормозим на 0,1 секунды

			if ( true === $have_get_result ) {
				// этот товар надо создать / обновить
				if ( get_post_meta( $post_id_on_wp, '_ip2y_prod_archive_status', true ) === 'archived' ) {
					// но прежде этот товар надо разархивировать
					$res_d = $this->product_unarchive( [ $your_sku_on_yandex ] );
					if ( true == $res_d['status'] ) {
						$helper->set_product_exists( $post_id_on_wp, [ 'product_archive_status' => '' ] );
					} else {
						// ! ошибка разархивации товара
						new IP2Y_Error_Log(
							sprintf( 'FEED № %1$s; ERROR: %2$s (product_id = %3$s, post_id_on_wp = %4$s) %5$s; Файл: %6$s; Строка: %7$s',
								$this->get_feed_id(),
								$msg,
								$product_id,
								$post_id_on_wp,
								'Ошибка восстановления товара из архива',
								'class-ip2y-api.php',
								__LINE__
							)
						);
					}
				}

				usleep( 100000 ); // притормозим на 0,1 секунды

				// отправляем данные о товаре
				$answer_product_upd_arr = $this->product_upd(
					$helper->get_product_data()
				);

				usleep( 100000 ); // притормозим на 0,1 секунды

				if ( true === $answer_product_upd_arr['status'] ) {
					// TODO: Тут надо подумать, чтобы учитывались для вариативных товаров тоже
					// TODO: Возможно добавить ещё и учёт всех этапов создания товара
					$answer_arr['skip_reasons'] = true; 
					new IP2Y_Error_Log(
						sprintf( 'FEED № %1$s; %2$s product_id = %3$s; Файл: %4$s; Строка: %5$s',
							$this->get_feed_id(),
							'Обновляем остатки',
							$product_id,
							'class-ip2y-api.php',
							__LINE__
						)
					);
					// обновим остатки
					$sync_product_amount = common_option_get( 'sync_product_amount', false, $this->get_feed_id(), 'ip2y' );

					if ( $sync_product_amount === 'enabled' ) {
						$helper_amount = new IP2Y_Api_Helper();
						$helper_amount->set_product_data( $product_id, 'set_product_stocks' );
						$res_d = $this->update_products_stocks( $helper_amount->get_product_data() );
						if ( true === $res_d['status'] ) {
							$res_d['status'] = true;
						} else {
							// ! ошибка обновления остатков
							new IP2Y_Error_Log(
								sprintf( 'FEED № %1$s; ERROR: %2$s (product_id = %3$s, post_id_on_wp = %4$s) %5$s; Файл: %6$s; Строка: %7$s',
									$this->get_feed_id(),
									$msg,
									$product_id,
									$post_id_on_wp,
									'Ошибка обновления остатков',
									'class-ip2y-api.php',
									__LINE__
								)
							);
						}
					}

					// обновим цены
					$helper_prices = new IP2Y_Api_Helper();
					$helper_prices->set_product_data( $product_id, 'set_products_prices' );
					$res_d = $this->set_products_prices( $helper_prices->get_product_data() );
					if ( true === $res_d['status'] ) {
						$res_d['status'] = true;
					} else {
						// ! ошибка обновления цен
						new IP2Y_Error_Log(
							sprintf( 'FEED № %1$s; ERROR: %2$s (product_id = %3$s, post_id_on_wp = %4$s) %5$s; Файл: %6$s; Строка: %7$s',
								$this->get_feed_id(),
								$msg,
								$product_id,
								$post_id_on_wp,
								'Ошибка обновления цен',
								'class-ip2y-api.php',
								__LINE__
							)
						);
					}
				} else {
					// ! ошибка создания/обновления товара
					new IP2Y_Error_Log(
						sprintf( 'FEED № %1$s; ERROR: %2$s (product_id = %3$s, post_id_on_wp = %4$s) %5$s; Файл: %6$s; Строка: %7$s',
							$this->get_feed_id(),
							$msg,
							$product_id,
							$post_id_on_wp,
							'ошибка создания/обновления',
							'class-ip2y-api.php',
							__LINE__
						)
					);

					// $answer_arr['skip_reasons'] = $helper->get_skip_reasons_arr();
					array_push( $answer_arr['skip_reasons'], $helper->get_skip_reasons_arr() );
					$res_d = $this->product_del( [ $your_sku_on_yandex ] );
					if ( true == $res_d['status'] ) {
						$helper->set_product_exists(
							$post_id_on_wp,
							[ 
								'market_sku_on_yandex' => '',
								'product_id_on_yandex' => '',
								'product_archive_status' => '',
								'your_sku_on_yandex' => ''
							]
						);
					} else {
						// ! ошибка удаления товара
						new IP2Y_Error_Log(
							sprintf( 'FEED № %1$s; ERROR: %2$s (product_id = %3$s, post_id_on_wp = %4$s) %5$s; Файл: %6$s; Строка: %7$s',
								$this->get_feed_id(),
								$msg,
								$product_id,
								$post_id_on_wp,
								'Ошибка удаления',
								'class-ip2y-api.php',
								__LINE__
							)
						);
					}
				}
			}
		}

		for ( $i = 0; $i < count( $product_sku_list_arr ); $i++ ) {
			$your_sku_on_yandex = $product_sku_list_arr[ $i ]['your_sku_on_yandex'];
			$mapping_arr = $this->get_offer_card( [ $your_sku_on_yandex ] );

			if ( ! empty( $mapping_arr['mapping_array'] ) ) {
				for ( $y = 0; $y < count( $mapping_arr['mapping_array'] ); $y++ ) {
					$helper->set_product_exists( $post_id_on_wp, [ 
						'market_sku_on_yandex' => $mapping_arr['mapping_array'][ $y ]['market_sku'], // marketSku
						'product_id_on_yandex' => $mapping_arr['mapping_array'][ $y ]['market_model_id'], // marketModelId
						// 'product_archive_status' => '',
						// 'your_sku_on_yandex' => ''
					] );

					if ( count( $mapping_arr['mapping_array'] ) > 1 ) {
						// если элементов больше чем один (вариативный товар)
						// TODO: В этом месте нужно делать склейку вариаций по категориям
						// $market_cat_id = 13858259; // $answer_arr['market_category_id']
						// $this->product_combine_variants( $answer_arr['market_category_id']
					}
				}
			}
		}
		return $answer_arr;
	}

	/**
	 * Добавление товара
	 * 
	 * @version			0.1.0 
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/updateOfferMappings
	 * 
	 * @param	array	$product_data - Required
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id импортированного товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_upd( $product_data ) {
		$result = [ 
			'status' => false
		];

		$data = [ 
			'offerMappings' => $product_data // [ 0 => $product_data ]
		];

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/businesses/%s/offer-mappings/update',
				$this->get_businesses_id()
			),
			$data,
			$this->get_headers_arr(),
			'POST',
			[],
			'json_encode'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			new IP2Y_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Ошибка добавления/обновления товара',
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					'class-ip2y-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		}

		$result = [ 
			'status' => true
		];

		return $result;
	}

	/**
	 * Обновление цен на товары
	 * 
	 * @version			0.1.0
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/updateBusinessPrices
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *			или:
	 * 					['errors'] - array 
	 * 						- [0]["code"] => int(101)
	 *						- [0]["message"] => string(37)
	 */
	public function set_products_prices( $prices_arr = [] ) {
		$result = [ 
			'status' => false
		];

		$data = [ 
			'offers' => $prices_arr
		];

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/businesses/%s/offer-prices/updates',
				$this->get_businesses_id()
			),
			$data,
			$this->get_headers_arr(),
			'POST',
			[],
			'json_encode'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			new IP2Y_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Ошибка установки цен на товар',
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					'class-ip2y-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		}

		$result = [ 
			'status' => true
		];

		return $result;
	}

	/**
	 * Обновление отстатков
	 * 
	 * @version			0.1.0
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/stocks/updateStocks
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *			или:
	 * 					['errors'] - array 
	 * 						- [0]["code"] => int(101)
	 *						- [0]["message"] => string(37)
	 */
	public function update_products_stocks( $skus_arr = [] ) {
		$result = [ 
			'status' => false
		];

		$data = [ 
			'skus' => $skus_arr
		];

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/campaigns/%s/offers/stocks',
				$this->get_campaign_id()
			),
			$data,
			$this->get_headers_arr(),
			'PUT',
			[],
			'json_encode'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			new IP2Y_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Ошибка обновления остатков товара',
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					'class-ip2y-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		}

		$result = [ 
			'status' => true
		];

		return $result;
	}

	/**
	 * Удаление товара
	 * 
	 * @version			0.1.0
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/deleteOffers
	 * 
	 * @param	array	$offer_your_sku_arr - Required - Type: string[] Ваш SKU — идентификатор товара в вашей системе.
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_del( $offer_your_sku_arr = [] ) {
		$result = [ 
			'status' => false
		];

		$data = [ 
			'offerIds' => $offer_your_sku_arr
		];

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/businesses/%s/offer-mappings/delete',
				$this->get_businesses_id()
			),
			$data,
			$this->get_headers_arr(),
			'POST',
			[],
			'json_encode'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			new IP2Y_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Ошибка удаления товара',
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					'class-ip2y-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		}

		$result = [ 
			'status' => true
		];

		return $result;
	}

	/**
	 * Архивация товара
	 * 
	 * @version			0.1.0
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/addOffersToArchive
	 * 
	 * @param	array	$offer_your_sku_arr - Required - Type: string[] Ваш SKU — идентификатор товара в вашей системе.
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_archive( $offer_your_sku_arr = [] ) {
		$result = [ 
			'status' => false
		];

		$data = [ 
			'offerIds' => $offer_your_sku_arr
		];

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/businesses/%s/offer-mappings/archive',
				$this->get_businesses_id()
			),
			$data,
			$this->get_headers_arr(),
			'POST',
			[],
			'json_encode'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			new IP2Y_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Ошибка переноса товара в архив',
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					'class-ip2y-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		}

		$result = [ 
			'status' => true
		];

		return $result;
	}

	/**
	 * Восстановление товаров из архива
	 * 
	 * @version			0.1.0
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/business-assortment/deleteOffersFromArchive
	 * 
	 * @param	array	$offer_your_sku_arr - Required - Type: string[] Ваш SKU — идентификатор товара в вашей системе.
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_unarchive( $offer_your_sku_arr = [] ) {
		$result = [ 
			'status' => false
		];

		$data = [ 
			'offerIds' => $offer_your_sku_arr
		];

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/businesses/%s/offer-mappings/unarchive',
				$this->get_businesses_id()
			),
			$data,
			$this->get_headers_arr(),
			'POST',
			[],
			'json_encode'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			new IP2Y_Error_Log(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Ошибка восстановления товаров из архива',
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					'class-ip2y-api.php',
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		}

		$result = [ 
			'status' => true
		];

		return $result;
	}

	/**
	 * Объединение товаров на одной карточке // TODO: Пока не пашет
	 * 
	 * @version			0.1.0
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/step-by-step/content#combine-variants
	 * 
	 * @param	array	$ids_arr - Required
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *					['product_id'] - string - id удалённого товара
	 *			или:
	 * 					['errors'] - array 
	 * 						- ["error_code"] => int(101)
	 *						- ["error_msg"] => string(37)
	 *						- ["request_params"] => NULL
	 */
	public function product_combine_variants( $market_cat_id, $ids_arr = [] ) {
		$result = [ 
			'status' => false
		];

		$data = [ 
			'offerIds' => []
		];

		for ( $i = 0; $i < count( $ids_arr ); $i++ ) {
			$product_id = $ids_arr[ $i ];
			$helper = new IP2Y_Api_Helper();
			$helper->set_product_data( $product_id, 'product_del' );
			$data['offerIds'] = $helper->get_product_data();
		}

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/businesses/%s/offer-mappings/delete',
				$this->get_businesses_id()
			),
			$data,
			$this->get_headers_arr(),
			'POST',
			[],
			'json_encode'
		);

		return $result;
	}

	/**
	 * Отправка запросов курлом
	 * 
	 * @version			0.1.0
	 * @see				https://snipp.ru/php/curl
	 * 
	 * @param	string	$request_url - Required
	 * @param	array	$postfields_arr - Optional
	 * @param	array	$headers_arr - Optional
	 * @param	string	$request_type - Optional
	 * @param	array	$pwd_arr - Optional
	 * @param	string	$encode_type - Optional
	 * @param	int		$timeout - Optional
	 * @param	string	$proxy - Optional // example: '165.22.115.179:8080
	 * @param	bool	$debug - Optional
	 * @param	string	$sep - Optional
	 * @param	string	$useragent - Optional
	 * 
	 * @return 	array	keys: errors, status, http_code, body, header_request, header_answer
	 * 
	 */
	private function response_to_yandex(
		$request_url,
		$postfields_arr = [],
		$headers_arr = [],
		$request_type = 'POST',
		$pwd_arr = [],
		$encode_type = 'json_encode',
		$timeout = 40,
		$proxy = '',
		$debug = false,
		$sep = PHP_EOL,
		$useragent = 'PHP Bot'
	) {
		if ( ! empty( $this->get_debug() ) ) {
			$request_url = $request_url . '?dbg=' . $this->get_debug();
		}

		/** 
		 * if (!empty($pwd_arr)) {
		 *	if (isset($pwd_arr['login']) && isset($pwd_arr['pwd'])) {
		 *		$userpwd = $pwd_arr['login'].':'.$pwd_arr['pwd']; // 'логин:пароль'
		 *		curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
		 *	}
		 * }
		 **/

		$answer_arr = [];
		$answer_arr['request_url'] = $request_url;
		$answer_arr['body_request'] = null;
		if ( $request_type !== 'GET' ) {
			switch ( $encode_type ) {
				case 'json_encode':
					$answer_arr['body_request'] = wp_json_encode( $postfields_arr );
					break;
				case 'http_build_query':
					$answer_arr['body_request'] = http_build_query( $postfields_arr );
					break;
				case 'dont_encode':
					$answer_arr['body_request'] = $postfields_arr;
					break;
				default:
					$answer_arr['body_request'] = wp_json_encode( $postfields_arr );
			}
		}

		new IP2Y_Error_Log( sprintf( 'FEED № %1$s; %2$s %3$s; Файл: %4$s; Строка: %5$s',
			$this->get_feed_id(),
			'Отправляем запрос к',
			$request_url,
			'class-ip2y-api.php',
			__LINE__
		) );
		new IP2Y_Error_Log( $headers_arr );
		new IP2Y_Error_Log( $answer_arr['body_request'] );

		$args = [ 
			'body' => $answer_arr['body_request'],
			'method' => $request_type,
			'timeout' => $timeout,
			// 'redirection' => '5',
			'user-agent' => $useragent,
			// 'httpversion' => '1.0',
			// 'blocking'    => true,
			'headers' => $headers_arr,
			'cookies' => []
		];
		usleep( 300000 ); // притормозим на 0,3 секунды
		$result = wp_remote_request( $request_url, $args );

		new IP2Y_Error_Log(
			sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				'Получен ответ сервера',
				'class-ip2y-api.php',
				__LINE__
			)
		);
		new IP2Y_Error_Log( wp_json_encode( $result ) );

		if ( is_wp_error( $result ) ) {
			$answer_arr['errors'] = $result->get_error_message(); // $result->get_error_code();
			$answer_arr['body_answer'] = null;
		} else {
			$answer_arr['status'] = true; // true - получили ответ
			// Разделение полученных HTTP-заголовков и тела ответа
			$response_body = $result['body'];
			$http_code = $result['response']['code'];
			$answer_arr['http_code'] = $http_code;

			if ( $http_code == 200 ) {
				// Если HTTP-код ответа равен 200, то возвращаем отформатированное тело ответа в формате JSON
				$decoded_body = json_decode( $response_body );
				$answer_arr['body_answer'] = $decoded_body;
			} else {
				// Если тело ответа не пустое, то производится попытка декодирования JSON-кода
				if ( ! empty( $response_body ) ) {
					$decoded_body = json_decode( $response_body );
					if ( $decoded_body != null ) {
						// Если ответ содержит тело в формате JSON, 
						// то возвращаем отформатированное тело в формате JSON
						$answer_arr['body_answer'] = $decoded_body;
					} else {
						// Если не удалось декодировать JSON либо тело имеет другой формат, 
						// то возвращаем преобразованное тело ответа
						$answer_arr['body_answer'] = htmlspecialchars( $response_body );
					}
				} else {
					$answer_arr['body_answer'] = null;
				}
			}
			// Вывод необработанных HTTP-заголовков запроса и ответа
			// $answer_arr['header_request'] = curl_getinfo($curl, CURLINFO_HEADER_OUT); // Заголовки запроса
			$answer_arr['header_answer'] = $result['headers']; // Заголовки ответа
		}

		// var_dump($answer_arr['body_answer']);
		return $answer_arr;
	}

	/* Getters */

	/**
	 * Summary of get_headers_arr
	 * 
	 * @return array
	 */
	private function get_headers_arr() {
		return [ 
			'Content-Type' => 'application/json',
			'Cache-Control' => 'no-cache',
			'Authorization' => sprintf( 'Bearer %s', $this->get_token() )
		];
	}

	/**
	 * Summary of get_sig
	 * 
	 * @param array $params_arr
	 * 
	 * @return array
	 */
	private function get_sig( $params_arr ) {
		return [];
	}

	/**
	 * Summary of conv_arr_as_str
	 * 
	 * @param array $array
	 * 
	 * @return string
	 */
	private function conv_arr_as_str( $array ) {
		ksort( $array );
		$string = "";
		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) ) {
				$string .= $key . "=" . $this->conv_arr_as_str( $val );
			} else {
				$string .= $key . "=" . $val;
			}
		}
		return $string;
	}

	/**
	 * Get client_id
	 * 
	 * @return string
	 */
	private function get_client_id() {
		return $this->client_id;
	}

	/**
	 * Get client_secret
	 * 
	 * @return string
	 */
	private function get_client_secret() {
		return $this->client_secret;
	}

	/**
	 * Get campaign_id
	 * 
	 * @return string
	 */
	private function get_campaign_id() {
		return $this->campaign_id;
	}

	/**
	 * Возвращает ID кабинета
	 * 
	 * @return string
	 */
	private function get_businesses_id() {
		return (string) $this->businesses_id;
	}

	/**
	 * Get token
	 * 
	 * @return string
	 */
	private function get_token() {
		return $this->access_token;
	}

	/**
	 * Get the debug string
	 * 
	 * @return string
	 */
	private function get_debug() {
		return $this->debug;
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	private function get_feed_id() {
		return $this->feed_id;
	}
}