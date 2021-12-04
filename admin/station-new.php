<div class="wrap">
    <h1 class="wp-heading">Nowa stacja</h1>
    <?php if (isset($errors) && is_wp_error($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors->get_error_messages() as $err): ?>
                    <li><?= $err ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?= $msg ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (isset($add_user_errors) && is_wp_error($add_user_errors)): ?>
        <div class="error">
            <?php foreach ($add_user_errors->get_error_messages() as $message): ?>
                <p><?= $message ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div id="ajax-response"></div>
    <form method="post" name="createstation" id="createstation" class="validate" novalidate="novalidate"
          enctype="multipart/form-data">
        <input name="action" type="hidden" value="createstation" />
        <?php wp_nonce_field('create-station', '_wpnonce_create-station'); ?>
        <?php
        // Load up the passed data, else set to a default.
        $creating = isset($_POST['createstation']);

        $new_station_name = $creating && isset($_POST['name']) ? wp_unslash($_POST['name']) : '';
        $new_station_description = $creating && isset($_POST['description']) ? wp_unslash($_POST['description']) : '';
        ?>
        <table class="form-table" role="presentation">
            <?php Rajd_Srz_Station::get_name_form_html($new_station_name, true); ?>
            <?php Rajd_Srz_Station::get_description_form_html($new_station_description, true); ?>
        </table>
        <?php submit_button('Dodaj nową stację', 'primary', 'createstation', true, ['id' => 'createstationsub']); ?>
    </form>
</div>
<?php
$editing = true;
//include_once __DIR__ . '/station-map.php';