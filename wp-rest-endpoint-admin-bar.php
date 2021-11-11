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

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

add_action( 'init', function () {
    add_rewrite_endpoint( 'admin_bar', EP_ALL );
} );

add_action( 'template_redirect', function () {
    global $wp_query;

    if ( ! isset( $wp_query->query_vars['admin_bar'] ) ) {
        return;
    }

    $result = apply_filters( 'rest_authentication_errors', null );

    if ( is_wp_error( $result ) ) {
        return;
    }

    $http_accept = $_SERVER['HTTP_ACCEPT'] ?? null;
    $content_type = $_SERVER['CONTENT_TYPE'] ?? null;

    if ( wp_is_json_request() ) {
        global $show_admin_bar;

        $show_admin_bar = null;

        $_SERVER['HTTP_ACCEPT'] = '';
        $_SERVER['CONTENT_TYPE'] = '';

        $is_admin_bar_showing = is_admin_bar_showing();

        if ( ! $is_admin_bar_showing ) {
            if ( null !== $http_accept ) {
                $_SERVER['HTTP_ACCEPT'] = $http_accept;
            }

            if ( null !== $content_type ) {
                $_SERVER['CONTENT_TYPE'] = $content_type;
            }

            return;
        }

        _wp_admin_bar_init();
    }

    add_action( 'admin_bar_menu', function () {
        remove_action( 'wp_before_admin_bar_render', 'wp_customize_support_script' );
    }, PHP_INT_MAX );

    ob_start();
    wp_admin_bar_render();
    $admin_bar_html = ob_get_clean();

    if ( null !== $http_accept ) {
        $_SERVER['HTTP_ACCEPT'] = $http_accept;
    }

    if ( null !== $content_type ) {
        $_SERVER['CONTENT_TYPE'] = $content_type;
    }

    foreach ( [
        'X-WP-Nonce'                   => wp_create_nonce( 'wp_rest' ),
        'X-Robots-Tag'                 => 'noindex',
        'X-Content-Type-Options'       => 'nosniff',
        'Access-Control-Allow-Headers' => implode( ', ', [
            'Authorization',
            'X-WP-Nonce',
            'Content-Type',
        ] ),
    ] as $key => $value ) {
        header( sprintf( '%s: %s', $key, $value ) );
    }

    foreach ( wp_get_nocache_headers() as $key => $value ) {
        if ( empty( $value ) ) {
            header_remove( $key );
        } else {
            header( sprintf( '%s: %s', $key, $value ) );
        }
    }

    header_remove( 'X-Pingback' );

    wp_send_json( [
        'html' => $admin_bar_html,
    ] );
} );
