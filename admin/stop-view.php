<?php
if (!class_exists('Rajd_Srz_Stop')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Stop.php';
}

if (!isset($stop)) {
    if (!isset($_REQUEST['id'])) {
        wp_die('Przystanek nie istnieje.');
    }

    $stop = new Rajd_Srz_Stop();
    $stop = $stop->load($_REQUEST['id']);

    if (is_wp_error($stop)) {
        wp_die($stop);
    }
}
?>
<div class="wrap">
    <h1 class="wp-heading"><?= $stop->get_name(); ?></h1>
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
    <form method="post" name="editstop" id="editstop" class="validate" novalidate="novalidate"
          enctype="multipart/form-data">
        <?php if (!isset($_REQUEST['id'])): ?>
            <input type="hidden" name="id" value="<?= $stop->get_id(); ?>" />
        <?php endif; ?>
        <input name="action" type="hidden" value="editstop" />
        <?php wp_nonce_field('edit-stop', '_wpnonce_edit-stop'); ?>
        <table class="form-table" role="presentation">
            <?php Rajd_Srz_Stop::get_name_form_html($stop->get_name()); ?>
            <?php Rajd_Srz_Stop::get_station_id_form_html($stop->get_station_id()); ?>
            <?php Rajd_Srz_Stop::get_description_form_html($stop->get_description()); ?>
        </table>
        <?php submit_button(
                'Aktualizuj przystanek',
                'primary',
                'editstop',
                true,
                ['id' => 'editstopsub']);
        ?>
    </form>
</div>
<?php
//include_once __DIR__ . '/stop-map.php';