<?php
if (!class_exists('Rajd_Srz_Station')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Station.php';
}

if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] === 'createstation') {
        check_admin_referer('create-station', '_wpnonce_create-station');

        $station = new Rajd_Srz_Station();
        $station->set_name(sanitize_text_field($_POST['name']));
        $station->set_description(sanitize_textarea_field($_POST['description']));
        $station->set_sort_order(sanitize_textarea_field($_POST['sort_order']));
        $updated_station = $station->create();

        if (is_wp_error($updated_station)) {
            $errors = $updated_station;

            require_once __DIR__ . '/station-new.php';
        } else {
            $success = 'Stacja stworzona.';

            require_once __DIR__ . '/station-view.php';
        }
    } else if ($_REQUEST['action'] === 'editstation') {
        check_admin_referer('edit-station', '_wpnonce_edit-station');

        $station = new Rajd_Srz_Station();
        $station = $station->load($_REQUEST['id']);

        if (is_wp_error($station)) {
            wp_die($station);
        }

        $station->set_name(sanitize_text_field($_POST['name']));
        $station->set_description(sanitize_textarea_field($_POST['description']));
        $station->set_sort_order(sanitize_textarea_field($_POST['sort_order']));
        $updated_station = $station->update();

        if (is_wp_error($updated_station)) {
            $errors = $updated_station;
        } else {
            $success = 'Stacja zaktualizowana.';
        }

        require_once __DIR__ . '/station-view.php';
    }
} else if (isset($_REQUEST['id'])) {
    require_once __DIR__ . '/station-view.php';
} else {
    require_once __DIR__ . '/station-new.php';
}
