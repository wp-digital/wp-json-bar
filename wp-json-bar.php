<?php
/**
 * Plugin Name: JSON Bar
 * Description:
 * Plugin URI: https://github.com/innocode-digital/wp-json-bar
 * Version: 0.2.0
 * Author: Innocode
 * Author URI: https://innocode.com
 * Tested up to: 5.8.2
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Innocode\JSONBar;

if ( ! defined( 'INNOCODE_JSON_BAR' ) ) {
    define( 'INNOCODE_JSON_BAR', 'admin_bar' );
}

define( 'INNOCODE_JSON_BAR_FILE', __FILE__ );
define( 'INNOCODE_JSON_BAR_VERSION', '0.2.0' );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$GLOBALS['innocode_json_bar'] = new JSONBar\Plugin(
        INNOCODE_JSON_BAR,
    INNOCODE_JSON_BAR_FILE,
    INNOCODE_JSON_BAR_VERSION
);
$GLOBALS['innocode_json_bar']->run();
