<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Rajd_Srz
 * @subpackage Rajd_Srz/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Rajd_Srz
 * @subpackage Rajd_Srz/public
 * @author     Artur Komoter <artur@komoter.pl>
 */
class Rajd_Srz_Public
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Initialise shortcodes.
     */
    public function init_shortcodes()
    {
        add_shortcode('rajd_srz', [&$this, 'rajd_srz_shortcode']);
    }

    /**
     * @param array|string $attributes
     * @param null $content
     * @param string $tag
     *
     * @return string
     */
    public function rajd_srz_shortcode($attributes = [], $content = null, string $tag = ''): string
    {
        if (is_user_logged_in()) {
            $this->get_dashboard_content();
            return '';
        }

        $attributes = array_change_key_case((array)$attributes, CASE_LOWER);
        $attributes = shortcode_atts([
            'registration_form_id' => '0',
        ], $attributes, $tag);

        $html = '<p>Załóż konto, aby móc uczestniczyć w Rajdzie Srzednickiego.</p>';

        if (!empty($attributes['registration_form_id'])) {
            $html .= do_shortcode('[user_registration_form id="' . $attributes['registration_form_id'] . '"]');
        }

        $html .= '<br/>';
        $html .= '<p>Jeśli posiadasz już konto, zaloguj się.</p>';
        $html .= wp_login_form([
            'echo' => false,
            'form_id' => 'rajd_srz_login'
        ]);

        return $html;
    }

    /**
     * Get dashboard content.
     */
    public function get_dashboard_content()
    {
        include_once dirname(__FILE__) . '/dashboard.php';
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/rajd-srzednickiego.css', [], $this->version, 'all');
        wp_enqueue_style('leaflet_css', RAJD_SRZ_ASSETS_DIR . 'css/leaflet.css', [], $this->version);
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('leaflet_js', RAJD_SRZ_ASSETS_DIR . 'js/leaflet.js', ['jquery'], $this->version);
    }
}