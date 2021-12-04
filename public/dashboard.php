<?php
if (!class_exists('Rajd_Srz_Stop')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Stop.php';
}

if (!class_exists('Rajd_Srz_Marker')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Marker.php';
}

$complete = true;
?>
<h2>Twój Rajd</h2>
<?php foreach (Rajd_Srz_Station::load_all() as $station): ?>
<div class="rajd-srz-station">
    <h6>Stacja</h6>
    <h3><?= $station['name']; ?></h3>
    <p class="rajd-srz-description"><?= $station['description']; ?></p>
    <?php foreach (Rajd_Srz_Stop::load_all_by_station_id($station['ID']) as $stop): ?>
    <?php $submitted = Rajd_Srz_Stop::has_user_submitted($stop['ID']); ?>
    <div class="rajd-srz-stop">
        <h6>Przystanek</h6>
        <h4>
            <?php if ($submitted): ?>
                ✅&nbsp;
            <?php else: ?>
                ❔&nbsp;
            <?php endif; ?>
            <?= $stop['name']; ?>
        </h4>
        <?php if (!$submitted): ?>
            <p class="rajd-srz-description"><?= $stop['description']; ?></p>
            <?php $complete = false; ?>
            <button class="rajd-srz-add-marker-button"
                    data-stop-id="<?= $stop['ID']; ?>"
                    style="margin-bottom: 1.5rem;">
                Dodaj pinezkę
            </button>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>
<?php if ($complete): ?>
<hr />
<p>Gratulacje! Ukończyłeś Rajd Srzednickiego!</p>
<?php endif; ?>
<script>
    jQuery('.rajd-srz-add-marker-button').on('click', function () {
        const $createMarker = jQuery('#create_marker');

        if ($createMarker.length) {
            $createMarker.remove();
        }

        const rajdSrzStopId = jQuery(this).attr('data-stop-id');
        const $form = jQuery(`<?php Rajd_Srz_Marker::get_form_html_public() ?>`);
        jQuery(this).after($form);
        <?php
        $editing = true;
        require_once RAJD_SRZ_PLUGIN_DIR . '/includes/marker-map.php'
        ?>
    });
</script>