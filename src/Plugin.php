<?php

namespace Innocode\RESTEndpointAdminBar;

final class Plugin
{
    /**
     * @var RESTEndpoint
     */
    private $rest_endpoint;

    /**
     * @param string $endpoint
     */
    public function __construct( string $endpoint )
    {
        $this->rest_endpoint = new RESTEndpoint( $endpoint );
    }

    /**
     * @return RESTEndpoint
     */
    public function get_rest_endpoint() : RESTEndpoint
    {
        return $this->rest_endpoint;
    }

    public function run()
    {
        $rest_controller = $this->get_rest_endpoint();

        add_action( 'init', [ $rest_controller, 'add_rewrite_endpoints' ] );
        add_action( 'template_redirect', [ $rest_controller, 'handle_request' ] );
    }
}
