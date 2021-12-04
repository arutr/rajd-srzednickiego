<?php
if (!class_exists('Rajd_Srz_Station')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Station.php';
}

if (!isset($station)) {
    if (!isset($_REQUEST['id'])) {
        wp_die('Stacja nie istnieje.');
    }

    $station = new Rajd_Srz_Station();
    $station = $station->load($_REQUEST['id']);

    if (is_wp_error($station)) {
        wp_die($station);
    }
}
?>
<div class="wrap">
    <h1 class="wp-heading"><?= $station->get_name(); ?></h1>
    <?php if (isset($errors) && is_wp_error($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors->get_error_messages() as $err): ?>
                    <li><?= $err ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="updated">
            <ul>
                <li><?= $success; ?></li>
            </ul>
        </div>
    <?php endif; ?>
    <div id="ajax-response"></div>
    <form method="post" name="editstation" id="editstation" class="validate" novalidate="novalidate"
          enctype="multipart/form-data">
        <?php if (!isset($_REQUEST['id'])): ?>
            <input type="hidden" name="id" value="<?= $station->get_id(); ?>" />
        <?php endif; ?>
        <input name="action" type="hidden" value="editstation" />
        <?php wp_nonce_field('edit-station', '_wpnonce_edit-station'); ?>
        <table class="form-table" role="presentation">
            <?php Rajd_Srz_Station::get_name_form_html($station->get_name()); ?>
            <?php Rajd_Srz_Station::get_description_form_html($station->get_description()); ?>
        </table>
        <?php submit_button(
                'Aktualizuj pinezkę',
                'primary',
                'editstation',
                true,
                ['id' => 'editstationsub']);
        ?>
    </form>
</div>
<?php
//include_once __DIR__ . '/station-map.php';