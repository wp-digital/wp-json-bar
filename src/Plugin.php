<?php

namespace Innocode\JSONBar;

final class Plugin
{
    /**
     * @var RESTEndpoint
     */
    private $rest_endpoint;
    /**
     * @var string
     */
    private $file;
    /**
     * @var string
     */
    private $version;

    /**
     * @param string $endpoint
     * @param string $file
     * @param string $version
     */
    public function __construct( string $endpoint, string $file, string $version )
    {
        $this->rest_endpoint = new RESTEndpoint( $endpoint );
        $this->file = $file;
        $this->version = $version;
    }

    /**
     * @return RESTEndpoint
     */
    public function get_rest_endpoint() : RESTEndpoint
    {
        return $this->rest_endpoint;
    }

    /**
     * @return string
     */
    public function get_file() : string
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function get_version() : string
    {
        return $this->version;
    }

    public function run()
    {
        $rest_endpoint = $this->get_rest_endpoint();

        add_action( 'init', [ $rest_endpoint, 'add_rewrite_endpoints' ] );
        add_action( 'template_redirect', [ $rest_endpoint, 'handle_request' ] );

        add_action( 'admin_bar_init', [ $this, 'enqueue_scripts' ] );
    }

    public function enqueue_scripts()
    {
        if ( is_admin() ) {
            return;
        }

        // Domain mapping processes mu-plugins directory wrong.
        $has_domain_mapping = remove_filter( 'plugins_url', 'domain_mapping_plugins_uri', 1 );

        $suffix = wp_scripts_get_suffix();

        $style_url = plugins_url( "public/css/style$suffix.css", $this->get_file() );
        $admin_bar_script_url = plugins_url( "public/js/admin-bar$suffix.js", $this->get_file() );
        $main_script_url = plugins_url( "public/js/main$suffix.js", $this->get_file() );

        if ( $has_domain_mapping ) {
            add_filter( 'plugins_url', 'domain_mapping_plugins_uri', 1 );
        }

        wp_enqueue_style(
            'innocode-json-bar',
            $style_url,
            [ 'admin-bar' ],
            $this->get_version()
        );

        wp_deregister_script( 'admin-bar' );
        wp_register_script(
            'admin-bar',
            $admin_bar_script_url,
            [ 'hoverintent-js' ],
            $this->get_version(),
            true
        );
        
        wp_enqueue_script(
            'innocode-json-bar',
            $main_script_url,
            [ 'admin-bar' ],
            $this->get_version(),
            true
        );
        wp_add_inline_script(
            'innocode-json-bar',
            'var innocodeJSONBar = ' . json_encode( [
                'endpoint'            => $this->get_rest_endpoint()->get_name(),
                'nonce'               => wp_create_nonce( 'wp_rest' ),
                'permalink_structure' => get_option( 'permalink_structure' ),
                'interval'            => apply_filters( 'innocode_json_bar_polling_interval', 1 ), // Polling interval in seconds.
            ] ),
            'before'
        );
    }
}
