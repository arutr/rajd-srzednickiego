<?php
if (!class_exists('Rajd_Srz_Marker')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Marker.php';
}
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Rajd_Srz
 * @subpackage Rajd_Srz/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rajd_Srz
 * @subpackage Rajd_Srz/admin
 * @author     Artur Komoter <artur@komoter.pl>
 */
class Rajd_Srz_Admin
{
    const MENU_SLUG_MARKERS = 'markers';
    const MENU_SLUG_STATIONS = 'stations';
    const MENU_SLUG_STOPS = 'stops';

    const MENU_POSITION_START = 26;
    const MENU_POSITION_STATIONS = self::MENU_POSITION_START + 1;
    const MENU_POSITION_STOPS = self::MENU_POSITION_STATIONS + 1;
    const MENU_POSITION_MARKERS = self::MENU_POSITION_STOPS + 1;

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
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Insert separators in admin menu.
     */
    public function insert_menu_separator()
    {
        global $menu;

        $menu[self::MENU_POSITION_START] = ['', 'read', '', '', 'wp-menu-separator'];
    }

    /**
     * Create admin menu pages.
     */
    public function get_menu()
    {
        // Stations
        add_menu_page(
            'Stacje',
            'Stacje',
            'read',
            self::MENU_SLUG_STATIONS,
            [&$this, 'get_stations_page_content'],
            'dashicons-post-status',
            self::MENU_POSITION_STATIONS
        );
        add_submenu_page(
            self::MENU_SLUG_STATIONS,
            'Stacja',
            'Dodaj nową',
            'read',
            'station-edit',
            [&$this, 'get_station_edit_page_content']
        );

        // Stops
        add_menu_page(
            'Przystanki',
            'Przystanki',
            'read',
            self::MENU_SLUG_STOPS,
            [&$this, 'get_stops_page_content'],
            'dashicons-post-status',
            self::MENU_POSITION_STOPS
        );
        add_submenu_page(
            self::MENU_SLUG_STOPS,
            'Przystanek',
            'Dodaj nową',
            'read',
            'stop-edit',
            [&$this, 'get_stop_edit_page_content']
        );

        // Markers
        add_menu_page(
            'Pinezki',
            'Pinezki',
            'read',
            self::MENU_SLUG_MARKERS,
            [&$this, 'get_markers_page_content'],
            'dashicons-post-status',
            self::MENU_POSITION_MARKERS
        );
        add_submenu_page(
            null,
            'Pinezka',
            'Pinezka',
            'read',
            'marker-edit',
            [&$this, 'get_marker_edit_page_content']
        );
    }

    /**
     * Get Stations menu page.
     */
    public function get_stations_page_content()
    {
        include_once dirname(__FILE__) . '/stations.php';
    }

    /**
     * Get New Station submenu page.
     */
    public function get_station_edit_page_content()
    {
        include_once dirname(__FILE__) . '/station-edit.php';
    }

    /**
     * Get Stops menu page.
     */
    public function get_stops_page_content()
    {
        include_once dirname(__FILE__) . '/stops.php';
    }

    /**
     * Get New Station submenu page.
     */
    public function get_stop_edit_page_content()
    {
        include_once dirname(__FILE__) . '/stop-edit.php';
    }

    /**
     * Get Markers menu page.
     */
    public function get_markers_page_content()
    {
        include_once dirname(__FILE__) . '/markers.php';
    }

    /**
     * Get Marker Edit page.
     */
    public function get_marker_edit_page_content()
    {
        include_once dirname(__FILE__) . '/marker-edit.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style('leaflet_css', RAJD_SRZ_ASSETS_DIR . 'css/leaflet.css', [], $this->version);
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('leaflet_js', RAJD_SRZ_ASSETS_DIR . 'js/leaflet.js', ['jquery'], $this->version);
    }

    /**
     * Create Marker POST request handler.
     */
    public function create_marker()
    {
        check_admin_referer('create_marker', '_wpnonce_create_marker');

        $marker = new Rajd_Srz_Marker();
        $marker->set_description(sanitize_textarea_field($_POST['description']));
        $marker->set_coordinates(sanitize_text_field($_POST['coordinates']));
        $marker->set_type(sanitize_text_field($_POST['type']));
        $marker->set_stop_id(sanitize_text_field($_POST['stop_id']));
        $updated_marker = $marker->create();

        if (is_wp_error($updated_marker)) {
            wp_die($updated_marker);
        } else {
            wp_safe_redirect(wp_get_referer());
        }
    }
}