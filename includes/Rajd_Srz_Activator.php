<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Pinezka_Ak
 * @subpackage Pinezka_Ak/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Rajd_Srz
 * @subpackage Rajd_Srz/includes
 * @author     Artur Komoter <artur@komoter.pl>
 */
class Rajd_Srz_Activator
{
    public static function activate()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        dbDelta("
CREATE TABLE IF NOT EXISTS `rajd_srz_stations` (
    ID BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(1000),
    sort_order INT(10)      
) $charset_collate;\n");
        dbDelta("
CREATE TABLE IF NOT EXISTS `rajd_srz_stops` (
    ID BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    station_id BIGINT(20) UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(1000),
    sort_order INT(10)
) $charset_collate;\n");
        dbDelta("
CREATE TABLE IF NOT EXISTS `rajd_srz_markers` (
    ID BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    stop_id BIGINT(20) UNSIGNED NOT NULL,
    description VARCHAR(1000),
    coordinates VARCHAR(40),
    type VARCHAR(255),
    images VARCHAR(255),
    points INT(10),
    points_criteria VARCHAR(255)
) $charset_collate;\n");
    }
}