<?php
require_once __DIR__ . '/includes/Rajd_Srz_Stops_List_Table.php';

if (isset($_REQUEST['action']) && isset($_REQUEST['id'])) {
    $stop = new Rajd_Srz_Stop();
    $stop = $stop->load($_REQUEST['id']);

    if (is_wp_error($stop)) {
        wp_die($stop);
    }

    if ($_REQUEST['action'] === 'delete_stop') {
        $result = $stop->delete();

        if (is_wp_error($result)) {
            wp_die($result);
        }

        $success = 'Przystanek ' . $stop->get_name() . ' został skasowany.';
    } else if ($_REQUEST['action'] === 'move_up' && $stop->get_sort_order() > 1) {
        $stop->set_sort_order($stop->get_sort_order() - 1);
        $result = $stop->update();

        if (is_wp_error($result)) {
            wp_die($result);
        }

        $success = 'Przystanek ' . $stop->get_name() . ' został przesunięty w górę.';
    } else if ($_REQUEST['action'] === 'move_down') {
        $stop->set_sort_order($stop->get_sort_order() + 1);
        $result = $stop->update();

        if (is_wp_error($result)) {
            wp_die($result);
        }

        $success = 'Przystanek ' . $stop->get_name() . ' został przesunięty w dół.';
    }

    unset($stop);
}

$rajd_srz_stops_list_table = new Rajd_Srz_Stops_List_Table();
$rajd_srz_stops_list_table->prepare_items();
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Przystanki</h1>
    <a href="<?php menu_page_url('stop-edit') ?>" class="page-title-action">Dodaj nowy</a>
    <hr class="wp-header-end" />
    <?php if (isset($success)): ?>
        <div class="updated">
            <ul>
                <li><?= $success; ?></li>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="page" value="rajd_srz_stops_search">
        <?php $rajd_srz_stops_list_table->search_box('Szukaj', 'stops_search'); ?>
        <?php $rajd_srz_stops_list_table->display(); ?>
    </form>
</div>