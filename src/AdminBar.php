<?php

namespace Innocode\JSONBar;

class AdminBar
{
    /**
     * @var bool
     */
    protected $is_showing;
    /**
     * @var string
     */
    protected $html = '';

    public function check_permissions()
    {
        global $show_admin_bar;

        $show_admin_bar = null;
        $this->is_showing = is_admin_bar_showing();
    }

    /**
     * @return bool
     */
    public function is_showing() : bool
    {
        if ( ! isset( $this->is_showing ) ) {
            return is_admin_bar_showing();
        }

        return $this->is_showing;
    }

    public function init()
    {
        _wp_admin_bar_init();
    }

    public function render()
    {
        add_action( 'admin_bar_menu', function () {
            remove_action( 'wp_before_admin_bar_render', 'wp_customize_support_script' );
        }, PHP_INT_MAX );

        ob_start();
        wp_admin_bar_render();
        $this->html = ob_get_clean();
    }

    /**
     * @return string
     */
    public function get_html() : string
    {
        return $this->html;
    }
}
