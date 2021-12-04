<?php
require_once __DIR__ . '/includes/Rajd_Srz_Markers_List_Table.php';

if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] === 'delete_marker' && isset($_REQUEST['id'])) {
        $marker = new Rajd_Srz_Marker();
        $marker = $marker->load($_REQUEST['id']);

        if (is_wp_error($marker)) {
            wp_die($marker);
        }

        $result = $marker->delete();

        if (is_wp_error($result)) {
            wp_die($result);
        }

        $success = 'Pinezka została skasowana.';
        unset($marker);
    }
}

$rajd_srz_markers_list_table = new Rajd_Srz_Markers_List_Table();
$rajd_srz_markers_list_table->prepare_items();
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Pinezki</h1>
    <hr class="wp-header-end" />
    <?php if (isset($success)): ?>
        <div class="updated">
            <ul>
                <li><?= $success; ?></li>
            </ul>
        </div>
    <?php endif; ?>
    <?php $rajd_srz_markers_list_table->views(); ?>
    <form method="post">
        <?php $rajd_srz_markers_list_table->display(); ?>
    </form>
</div>