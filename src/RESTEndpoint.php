<?php

namespace Innocode\JSONBar;

use WP_Error;

class RESTEndpoint
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $real_headers = [];
    /**
     * @var AdminBar
     */
    private $admin_bar;

    /**
     * @param string $name
     */
    public function __construct( string $name )
    {
        $this->name = $name;
        $this->admin_bar = new AdminBar();
    }

    /**
     * @return string
     */
    public function get_name() : string
    {
        return $this->name;
    }

    /**
     * @return AdminBar
     */
    public function get_admin_bar() : AdminBar
    {
        return $this->admin_bar;
    }

    /**
     * @return string|null
     */
    public function get_route() : ?string
    {
        return get_query_var( $this->get_name(), null );
    }

    /**
     * @return array
     */
    public function get_real_headers() : array
    {
        return $this->real_headers;
    }

    /**
     * @param string $key
     * @return array
     */
    public function hide_real_header( string $key ) : array
    {
        if ( isset( $_SERVER[ $key ] ) ) {
            $this->real_headers[ $key ] = $_SERVER[ $key ];
            $_SERVER[ $key ] = '';
        }

        return $this->get_real_headers();
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get_real_header( string $key ) : ?string
    {
        return $this->real_headers[ $key ] ?? null;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function restore_real_header( string $key ) : ?string
    {
        $value = $this->get_real_header( $key );

        if ( null !== $value ) {
            $_SERVER[ $key ] = $value;
            unset( $this->real_headers[ $key ] );
        }

        return $value;
    }

    public function add_rewrite_endpoints()
    {
        add_rewrite_endpoint( $this->get_name(), EP_ALL );
    }

    public function handle_request()
    {
        if ( null === $this->get_route() ) {
            return;
        }

        $this->headers();

        $result = $this->check_authentication();

        if ( is_wp_error( $result ) ) {
            $this->error( $result );
        }

        $admin_bar = $this->get_admin_bar();

        if ( wp_is_json_request() ) {
            $this->hide_real_header( 'HTTP_ACCEPT' );
            $this->hide_real_header( 'CONTENT_TYPE' );

            $admin_bar->check_permissions();

            if ( $admin_bar->is_showing() ) {
                $admin_bar->init();
                $admin_bar->render();
            }

            $this->restore_real_header( 'HTTP_ACCEPT' );
            $this->restore_real_header( 'CONTENT_TYPE' );
        } else {
            $admin_bar->check_permissions();

            if ( $admin_bar->is_showing() ) {
                $admin_bar->render();
            }
        }

        if ( ! $admin_bar->is_showing() ) {
            $this->error( new WP_Error(
                'rest_forbidden',
                __( 'Sorry, you are not allowed to do that.' ),
                [ 'status' => rest_authorization_required_code() ]
            ) );
        }

        wp_send_json( [
            'html' => $admin_bar->get_html(),
        ] );
    }

    /**
     * @return WP_Error|null|bool
     */
    protected function check_authentication()
    {
        return apply_filters( 'rest_authentication_errors', null );
    }

    protected function headers()
    {
        if ( headers_sent() ) {
            return;
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
    }

    /**
     * @param WP_Error $error
     */
    protected function error( WP_Error $error )
    {
        $response = rest_convert_error_to_response( $error );

        wp_send_json( $response->get_data(), $response->get_status() );
    }
}
