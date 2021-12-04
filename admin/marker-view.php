<?php
if (!class_exists('Rajd_Srz_Marker')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Marker.php';
}

if (!isset($marker)) {
    if (!isset($_REQUEST['id'])) {
        wp_die('Pinezka nie istnieje.');
    }

    $marker = new Rajd_Srz_Marker();
    $marker = $marker->load($_REQUEST['id']);

    if (is_wp_error($marker)) {
        wp_die($marker);
    }
}
?>
<div class="wrap">
    <h1 class="wp-heading"><?= $marker->get_id(); ?></h1>
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
    <form method="post" name="editmarker" id="editmarker" class="validate" novalidate="novalidate"
          enctype="multipart/form-data">
        <?php if (!isset($_REQUEST['id'])): ?>
            <input type="hidden" name="id" value="<?= $marker->get_id(); ?>" />
        <?php endif; ?>
        <input name="action" type="hidden" value="editmarker" />
        <?php wp_nonce_field('edit-marker', '_wpnonce_edit-marker'); ?>
        <table class="form-table" role="presentation">
            <?php Rajd_Srz_Marker::get_description_form_html_admin($marker->get_description()); ?>
            <?php Rajd_Srz_Marker::get_coordinates_form_html($marker->get_coordinates()); ?>
            <?php Rajd_Srz_Marker::get_type_form_html_admin($marker->get_type()); ?>
            <?php Rajd_Srz_Marker::get_image_form_html($marker->get_images()); ?>
            <?php Rajd_Srz_Marker::get_points_criteria_form_html($marker->get_points_criteria()); ?>
        </table>
        <?php submit_button(
                'Aktualizuj pinezkę',
                'primary',
                'editmarker',
                true,
                ['id' => 'editmarkersub']);
        ?>
    </form>
</div>
<script>
<?php
$editing = true;
require_once RAJD_SRZ_PLUGIN_DIR . '/includes/marker-map.php'
?>
</script>
