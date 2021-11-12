<?php
/**
 * Plugin Name: REST Endpoint for Admin Bar
 * Description: IMPORTANT: It's in development!
 * Plugin URI: https://github.com/innocode-digital/wp-rest-endpoint-admin-bar
 * Version: 0.0.1
 * Author: Innocode
 * Author URI: https://innocode.com
 * Tested up to: 5.8.2
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Innocode\RESTEndpointAdminBar;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$GLOBALS['innocode_rest_endpoint_admin_bar'] = new RESTEndpointAdminBar\Plugin(
    defined( 'INNOCODE_REST_ENDPOINT_ADMIN_BAR' )
        ? INNOCODE_REST_ENDPOINT_ADMIN_BAR
        : 'admin_bar'
);
$GLOBALS['innocode_rest_endpoint_admin_bar']->run();
