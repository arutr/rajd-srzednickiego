<?php
if (!class_exists('Rajd_Srz_Marker')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Marker.php';
}

if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] === 'editmarker') {
        check_admin_referer('edit-marker', '_wpnonce_edit-marker');

        $marker = new Rajd_Srz_Marker();
        $marker = $marker->load($_REQUEST['id']);

        if (is_wp_error($marker)) {
            wp_die($marker);
        }

        $marker->set_description(sanitize_textarea_field($_POST['description']));
        $marker->set_coordinates(sanitize_text_field($_POST['coordinates']));
        $marker->set_type(sanitize_text_field($_POST['type']));

        if (isset($_POST['points_criteria'])) {
            $marker->set_points_criteria($_POST['points_criteria']);
        }

        $updated_marker = $marker->update();

        if (is_wp_error($updated_marker)) {
            $errors = $updated_marker;
        } else {
            $success = 'Pinezka zaktualizowana.';
        }
    }
}

require_once __DIR__ . '/marker-view.php';
