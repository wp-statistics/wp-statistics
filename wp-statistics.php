<?php
/**
 * Plugin Name: WP Statistics
 * Plugin URI: https://wp-statistics.com/
 * GitHub Plugin URI: https://github.com/wp-statistics/wp-statistics
 * Description: Get website traffic insights with GDPR/CCPA compliant, privacy-friendly analytics. Includes visitor data, stunning graphs, and no data sharing.
 * Version: 15.0
 * Author: VeronaLabs
 * Author URI: https://veronalabs.com/
 * Text Domain: wp-statistics
 * Domain Path: /resources/languages
 * Requires at least: 5.3
 * Requires PHP: 7.4
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Premium Compatibility Check
|--------------------------------------------------------------------------
|
| If WP Statistics Premium is active, it includes all Free features.
| Free should stay dormant and display a notice to deactivate.
|
*/
require_once __DIR__ . '/src/premium-compatibility.php';

if (wp_statistics_is_premium_active()) {
    wp_statistics_init_premium_compatibility(__FILE__);
    return;
}

/*
|--------------------------------------------------------------------------
| Load Constants
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/src/constants.php';

/*
|--------------------------------------------------------------------------
| Load Composer Autoloader
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Load Global Functions
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/src/functions.php';

/*
|--------------------------------------------------------------------------
| Initialize Plugin
|--------------------------------------------------------------------------
*/
WP_Statistics\Bootstrap::init();
