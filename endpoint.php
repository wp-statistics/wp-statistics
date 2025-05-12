<?php
define('SHORTINIT', true);

define('BASE_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');

define('WP_STATISTICS_DIR', dirname(__FILE__) . '/');

require_once BASE_PATH . 'wp-load.php';
require_once BASE_PATH . 'wp-includes/pluggable.php';
require_once BASE_PATH . 'wp-includes/query.php';
require_once BASE_PATH . 'wp-includes/l10n.php';
require_once BASE_PATH . 'wp-includes/class-wp-textdomain-registry.php';
require_once BASE_PATH . 'wp-includes/kses.php';
require_once BASE_PATH . 'wp-includes/class-wp-block-parser.php';
require_once BASE_PATH . 'wp-includes/blocks.php';
require_once WP_STATISTICS_DIR . 'vendor/autoload.php';
require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-exclusion.php';
require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-ip.php';
require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-hits.php';

// $GLOBALS['wp_textdomain_registry'] = new WP_Textdomain_Registry();
// $GLOBALS['wp_textdomain_registry']->init();

handleHitRequest();

function handleHitRequest()
{
    try {
        if (WP_STATISTICS\Helper::isRequestSignatureEnabled()) {
            $signature = WP_Statistics\Utils\Request::get('signature');
            $payload   = [
                WP_Statistics\Utils\Request::get('source_type'),
                WP_Statistics\Utils\Request::get('source_id', 0, 'number')
            ];

            if (!WP_Statistics\Utils\Signature::check($payload, $signature)) {
                throw new Exception(__('Invalid signature', 'wp-statistics'), 403);
            }
        }

        WP_STATISTICS\Helper::validateHitRequest();
        WP_STATISTICS\Hits::record();
        wp_send_json_success();
    } catch (Exception $e) {
        var_dump($e);
        echo '<script>console.log(' . json_encode( $e->getMessage() ) . ')</script>';
    }
}

function handleOnlineRequest()
{

}