<?php defined( 'ABSPATH' ) || exit;
require_once IP2Y_PLUGIN_DIR_PATH . 'common-libs/icopydoc-useful-functions-1-1-8.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'common-libs/wc-add-functions-1-0-2.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'common-libs/class-icpd-feedback-1-0-3.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'common-libs/class-icpd-promo-1-1-0.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'common-libs/class-icpd-set-admin-notices.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'common-libs/backward-compatibility.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'functions.php';

require_once IP2Y_PLUGIN_DIR_PATH . 'classes/system/class-ip2y.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/system/class-ip2y-interface-hocked.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/system/class-ip2y-data-arr.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/system/class-ip2y-debug-page.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/system/class-ip2y-error-log.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/system/pages/extensions-page/class-ip2y-extensions-page.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/system/pages/settings-page/class-ip2y-settings-page.php';

require_once IP2Y_PLUGIN_DIR_PATH . 'classes/generation/traits/common/trait-ip2y-t-common-get-catid.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/generation/traits/common/trait-ip2y-t-common-skips.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/generation/traits/global/traits-ip2y-global-variables.php';

require_once IP2Y_PLUGIN_DIR_PATH . 'classes/generation/class-ip2y-api.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/generation/class-ip2y-api-helper.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/generation/class-ip2y-api-helper-simple.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/generation/class-ip2y-api-helper-variable.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/generation/class-ip2y-generation-xml.php';

require_once IP2Y_PLUGIN_DIR_PATH . 'classes/system/updates/class-ip2y-plugin-form-activate.php';
require_once IP2Y_PLUGIN_DIR_PATH . 'classes/system/updates/class-ip2y-plugin-upd.php';