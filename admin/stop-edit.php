<?php
if (!class_exists('Rajd_Srz_Stop')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Stop.php';
}

if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] === 'createstop') {
        check_admin_referer('create-stop', '_wpnonce_create-stop');

        $stop = new Rajd_Srz_Stop();
        $stop->set_name(sanitize_text_field($_POST['name']));
        $stop->set_description(sanitize_textarea_field($_POST['description']));
        $stop->set_station_id(sanitize_text_field($_POST['station_id']));
//        $stop->set_coordinates(sanitize_text_field($_POST['coordinates']));
        $updated_stop = $stop->create();

        if (is_wp_error($updated_stop)) {
            $errors = $updated_stop;

            require_once __DIR__ . '/stop-new.php';
        } else {
            $success = 'Przystanek stworzony.';

            require_once __DIR__ . '/stop-view.php';
        }
    } else if ($_REQUEST['action'] === 'editstop') {
        check_admin_referer('edit-stop', '_wpnonce_edit-stop');

        $stop = new Rajd_Srz_Stop();
        $stop = $stop->load($_REQUEST['id']);

        if (is_wp_error($stop)) {
            wp_die($stop);
        }

        $stop->set_name(sanitize_text_field($_POST['name']));
        $stop->set_description(sanitize_textarea_field($_POST['description']));
        $stop->set_station_id(sanitize_text_field($_POST['station_id']));
//        $stop->set_coordinates(sanitize_text_field($_POST['coordinates']));

        $updated_stop = $stop->update();

        if (is_wp_error($updated_stop)) {
            $errors = $updated_stop;
        } else {
            $success = 'Przystanek zaktualizowany.';
        }

        require_once __DIR__ . '/stop-view.php';
    }
} else if (isset($_REQUEST['id'])) {
    require_once __DIR__ . '/stop-view.php';
} else {
    require_once __DIR__ . '/stop-new.php';
}
