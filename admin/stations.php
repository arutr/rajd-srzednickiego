<?php
require_once __DIR__ . '/includes/Rajd_Srz_Stations_List_Table.php';

if (isset($_REQUEST['action']) && isset($_REQUEST['id'])) {
    $station = new Rajd_Srz_Station();
    $station = $station->load($_REQUEST['id']);

    if (is_wp_error($station)) {
        wp_die($station);
    }

    if ($_REQUEST['action'] === 'delete_station') {
        $result = $station->delete();

        if (is_wp_error($result)) {
            wp_die($result);
        }

        $success = 'Stacja ' . $station->get_name() . ' została skasowana.';
    } else if ($_REQUEST['action'] === 'move_up' && $station->get_sort_order() > 1) {
        $station->set_sort_order($station->get_sort_order() - 1);
        $result = $station->update();

        if (is_wp_error($result)) {
            wp_die($result);
        }

        $success = 'Stacja ' . $station->get_name() . ' została przesunięta w górę.';
    } else if ($_REQUEST['action'] === 'move_down') {
        $station->set_sort_order($station->get_sort_order() + 1);
        $result = $station->update();

        if (is_wp_error($result)) {
            wp_die($result);
        }

        $success = 'Stacja ' . $station->get_name() . ' została przesunięta w dół.';
    }

    unset($station);
}

$rajd_srz_stations_list_table = new Rajd_Srz_Stations_List_Table();
$rajd_srz_stations_list_table->prepare_items();
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Stacje</h1>
    <a href="<?php menu_page_url('station-edit') ?>" class="page-title-action">Dodaj nową</a>
    <hr class="wp-header-end" />
    <?php if (isset($success)): ?>
        <div class="updated">
            <ul>
                <li><?= $success; ?></li>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="page" value="rajd_srz_stations_search">
        <?php $rajd_srz_stations_list_table->search_box('Szukaj', 'stations_search'); ?>
        <?php $rajd_srz_stations_list_table->display(); ?>
    </form>
</div>