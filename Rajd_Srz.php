<?php
/**
 * Rajd Srzednickiego
 *
 * @package           RajdSrzednickiego
 * @author            APK IT
 * @copyright         2021 APK IT
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Rajd Srzednickiego
 * Version:           1.0.0
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
/*
Rajd Srzednickiego is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Rajd Srzednickiego is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Rajd Srzednickiego. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

const RAJD_SRZ_VERSION = '1.0.0';
const RAJD_SRZ_MARKERS_TABLE = 'rajd_srz_markers';
const RAJD_SRZ_STATIONS_TABLE = 'rajd_srz_stations';
const RAJD_SRZ_STOPS_TABLE = 'rajd_srz_stops';

const RAJD_SRZ_PLUGIN_DIR = WP_PLUGIN_DIR . '/rajd-srzednickiego';
define('RAJD_SRZ_ASSETS_DIR', plugins_url('/assets/', __FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_rajd_srz()
{
    require_once plugin_dir_path(__FILE__) . 'includes/Rajd_Srz_Activator.php';
    Rajd_Srz_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_rajd_srz()
{
    require_once plugin_dir_path(__FILE__) . 'includes/Rajd_Srz_Deactivator.php';
    Rajd_Srz_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_rajd_srz');
register_deactivation_hook(__FILE__, 'deactivate_rajd_srz');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/Rajd_Srz.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rajd_srz()
{
    $plugin = new Rajd_Srz();
    $plugin->run();
}

run_rajd_srz();