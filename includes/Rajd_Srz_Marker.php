<?php

class Rajd_Srz_Marker
{
    /**
     * @var int
     */
    private int $ID = 0;

    /**
     * @var int
     */
    private int $user_id = 0;

    /**
     * @var int
     */
    private int $stop_id = 0;

    /**
     * @var string
     */
    private string $description = '';

    /**
     * @var string
     */
    private string $coordinates = '';

    /**
     * @var string
     */
    private string $type = '';

    /**
     * @var array
     */
    private array $images = [];

    /**
     * @var int
     */
    private int $points = 0;

    /**
     * @var array
     */
    private array $points_criteria = [];

    /**
     * @param int $id
     *
     * @return $this|WP_Error
     */
    public function load(int $id)
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `rajd_srz_markers` WHERE ID = %d;", $id)
        );

        if (!$row) {
            return new WP_Error('invalid_marker', 'Pinezka nie istnieje.');
        }

        foreach (get_object_vars($row) as $key => $value) {
            if (!empty($value)) {
                if (is_array($this->$key)) {
                    $this->$key = explode(',', $value);
                } else {
                    $this->$key = $value;
                }
            }
        }

        return $this;
    }

    /**
     * @return array|WP_Error
     */
    private function handle_image_upload()
    {
        if ($_FILES['marker-image']['size'][0]) {
            $uploads = $_FILES['marker-image'];
            $this->images = [];

            foreach ($uploads['name'] as $key => $value) {
                $_FILES['_marker-image'] = [
                    'name'     => $uploads['name'][$key],
                    'type'     => $uploads['type'][$key],
                    'tmp_name' => $uploads['tmp_name'][$key],
                    'error'    => $uploads['error'][$key],
                    'size'     => $uploads['size'][$key],
                ];
                $image_attachment_id = media_handle_upload('_marker-image', 0);

                if (is_wp_error($image_attachment_id)) {
                    /** @var WP_Error $image_attachment_id */
                    return $image_attachment_id;
                }

                $this->images[] = $image_attachment_id;
            }
        }

        return $this->images;
    }

    /**
     * @return $this|WP_Error
     */
    public function create()
    {
        global $wpdb;
        $error = $this->handle_image_upload();

        if (is_wp_error($error)) {
            return $error;
        }

        $rows = $wpdb->insert(RAJD_SRZ_MARKERS_TABLE, wp_unslash([
            'user_id'         => get_current_user_id(),
            'stop_id'         => $this->stop_id,
            'description'     => $this->description,
            'coordinates'     => $this->coordinates,
            'type'            => $this->type,
            'images'          => join(',', $this->images) ?? '',
            'points'          => $this->points,
            'points_criteria' => $this->points_criteria,
        ]));

        if ($rows === false) {
            return new WP_Error('db_error', 'Nie udało się stworzyć pinezki. Spróbuj ponownie lub skontaktuj się z nami!');
        }

        $this->user_id = get_current_user_id();
        $this->ID = $wpdb->insert_id;

        return $this;
    }

    /**
     * @return $this|WP_Error
     */
    public function update()
    {
        global $wpdb;
        $error = $this->handle_image_upload();

        if (is_wp_error($error)) {
            return $error;
        }

        if ($this->points_criteria) {
            $this->calculate_points();
        }

        $rows = $wpdb->update(RAJD_SRZ_MARKERS_TABLE, wp_unslash([
            'description'     => $this->description,
            'coordinates'     => $this->coordinates,
            'type'            => $this->type,
            'images'          => join(',', $this->images) ?? '',
            'points'          => $this->points,
            'points_criteria' => join(',', $this->points_criteria) ?? '',
        ]), [
            'ID' => $this->ID,
        ]);

        if ($rows === false) {
            return new WP_Error('db_error', 'Nie udało się zaktualizować pinezki. Spróbuj ponownie lub skontaktuj się z nami!');
        }

        return $this;
    }

    /**
     * @return bool|WP_Error
     */
    public function delete()
    {
        global $wpdb;

        $rows = $wpdb->delete(RAJD_SRZ_MARKERS_TABLE, [
            'ID' => $this->ID,
        ]);

        if ($rows === false) {
            return new WP_Error('db_error', 'Nie udało się skasować pinezki. Spróbuj ponownie lub skontaktuj się z nami!');
        }

        return true;
    }

    /**
     * Get marker entry HTML form.
     */
    public static function get_form_html_public()
    {
        ?>
        <form method="post"
              name="create_marker"
              id="create_marker"
              class="validate"
              enctype="multipart/form-data"
              action="<?= get_admin_url(null, 'admin-post.php') ?>">
            <input name="action" type="hidden" value="create_marker" />
            <?php wp_nonce_field('create_marker', '_wpnonce_create_marker'); ?>
            <input name="stop_id" type="hidden" value="${rajdSrzStopId}" />
            <p class="rajd-srz-form-description">
                <label for="description"><strong>Opis</strong></label>
                <small>Opisz miejsca powiązane z przystankiem. Jeśli miejsca zawierają tablice upamiętniające, zamieść treść tablic.</small>
                <?php self::get_description_form_element(); ?>
            </p>
            <p class="rajd-srz-form-coordinates">
                <label for="coordinates"><strong>Położenie</strong></label>
                <small>Zaznacz na poniższej mapie położenie przystanku. W przypadku kilku miejsc, wybierz punkt pomiędzy miejscami.</small>
                <?php self::get_coordinates_form_element(); ?>
            </p>
            <p class="rajd-srz-form-type">
                <label for="type"><strong>Rodzaj</strong></label>
                <?php self::get_type_form_element(); ?>
            </p>
            <p class="rajd-srz-form-images">
                <label for="images"><strong>Zdjęcia</strong></label>
                <?php self::get_image_form_element(); ?>
            </p>
            <button type="submit" class="button"><?= __('Submit'); ?></button>
        </form>
        <?php
    }

    /**
     * @return int
     */
    public function get_id(): int
    {
        return $this->ID;
    }

    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function get_description(): string
    {
        return $this->description;
    }

    /**
     * @param string $value
     */
    public static function get_description_form_element(string $value = '')
    {
        ?>
        <textarea class="textarea-wrap"
                  name="description"
                  id="description"
                  maxlength="1000"
                  rows="10"
                  aria-required="true"
                  required="required"><?= $value; ?></textarea>
        <?php
    }

    /**
     * @param string $value
     */
    public static function get_description_form_html_admin(string $value)
    {
        $descriptionLabel = __('Description');
        $value = esc_attr($value);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="description">
                    <?= $descriptionLabel; ?>
                </label>
            </th>
            <td>
                <textarea class="textarea-wrap" name="description"
                          id="description" maxlength="1000" rows="10"><?= $value; ?></textarea>
            </td>
        </tr>
        <?php
    }

    /**
     * @param string $description
     */
    public function set_description(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function get_coordinates(): string
    {
        return $this->coordinates;
    }

    /**
     * @param string $value
     */
    public static function get_coordinates_form_element(string $value = '')
    {
        ?>
        <div id="marker-location-map" style="height: 400px; width: 95%;"></div>
        <input name="coordinates" type="hidden" id="coordinates" value="<?= $value; ?>" />
        <?php
    }

    /**
     * @param string $value
     */
    public static function get_coordinates_form_html(string $value)
    {
        $value = esc_attr($value);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="coordinates">
                    Miejsce pinezki
                </label>
            </th>
            <td>
                <?php self::get_coordinates_form_element($value); ?>
            </td>
        </tr>
        <?php
    }

    /**
     * @param string $coordinates
     */
    public function set_coordinates(string $coordinates): void
    {
        $this->coordinates = $coordinates;
    }

    /**
     * @return string
     */
    public function get_type(): string
    {
        return $this->type;
    }

    /**
     * @param string $value
     */
    public static function get_type_form_element(string $value = '')
    {
        ?>
        <select name="type" id="type" aria-required="true" required="required">
            <option value="">--- Wybierz rodzaj pinezki ---</option>
            <?php foreach (self::get_types() as $typeValue => $typeLabel): ?>
                <?php $selected = $value == $typeValue ? 'selected' : ''; ?>
                <option value="<?= $typeValue ?>" <?= $selected ?>>
                    <?= $typeLabel ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * @param string $value
     */
    public static function get_type_form_html_admin(string $value)
    {
        $value = esc_attr($value);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="type">
                    Rodzaj
                </label>
            </th>
            <td>
                <?php self::get_type_form_element($value); ?>
            </td>
        </tr>
        <?php
    }

    /**
     * @return string[]
     */
    public static function get_types(): array
    {
        return [
            'grave'     => 'Grób',
            'building'  => 'Budynek',
            'structure' => 'Budowla',
            'place'     => 'Miejsce',
            'other'     => 'Inne',
        ];
    }

    /**
     * @param $type
     *
     * @return string
     */
    public static function get_type_label($type): string
    {
        $markerTypes = self::get_types();

        return $markerTypes[$type] ?? '';
    }

    /**
     * @param string $type
     */
    public function set_type(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function get_images(): array
    {
        return $this->images;
    }

    public static function get_image_form_element()
    {
        ?>
        <input name="marker-image[]" type="file" id="images" accept="image/png, image/jpeg" multiple aria-required="true" />
        <?php
    }

    /**
     * @param array $values
     */
    public static function get_image_form_html(array $values)
    {
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="image">Zdjęcia miejsca</label>
            </th>
            <td>
                <?php if (!empty($values)): ?>
                    <?php foreach ($values as $value): ?>
                        <img src="<?= wp_get_attachment_url($value); ?>" alt="Marker image"
                             style="max-height: 400px; max-width: 95%;" />
                        <br />
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php self::get_image_form_element(); ?>
            </td>
        </tr>
        <?php
    }

    /**
     * @param array $images
     */
    public function set_images(array $images)
    {
        $this->images = $images;
    }

    /**
     * @return int
     */
    public function get_points(): int
    {
        return $this->points;
    }

    /**
     * @return int
     */
    public function calculate_points(): int
    {
        $options = self::get_points_criteria_options();
        $this->points = array_reduce($this->points_criteria, function ($carry, $item) use ($options)
        {
            return $carry + $options[$item]['points'];
        }, 0);

        if (in_array(
            get_user_meta(wp_get_current_user()->ID, 'user_registration_type', true),
            ['Grupa', 'Rodzina']
        )) {
            $this->points *= 2;
        }

        return $this->points;
    }

    /**
     * @param int $points
     */
    public function set_points(int $points): void
    {
        $this->points = $points;
    }

    /**
     * @return array
     */
    public function get_points_criteria(): array
    {
        return $this->points_criteria;
    }

    /**
     * @return array[]
     */
    public static function get_points_criteria_options(): array
    {
        return [
            'standard'                 => [
                'points' => 1,
                'label'  => 'Zgodnie ze zrealizowanym planem Rajdu za każdy przystanek element zrealizowanego planu.',
            ],
            'station_stop_information' => [
                'points' => 5,
                'label'  => 'Wskazanie i opisanie dodatkowej informacji znalezionej w zasobach www.srzednicki.org dla Stacji i nowych i niewymienionych Przystanków',
            ],
            'historical_information'   => [
                'points' => 10,
                'label'  => 'Nowe udokumentowane informacje historyczne dotyczące życia Stanisława Pomian Srzednickiego',
            ],
        ];
    }

    /**
     * @param array $values
     */
    public static function get_points_criteria_form_html(array $values)
    {
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="points_criteria">
                    Punktacja
                </label>
            </th>
            <td>
                <?php foreach (self::get_points_criteria_options() as $key => $optionValue): ?>
                    <?php $checked = in_array($key, $values) ? 'checked' : ''; ?>
                    <label for="<?= $key ?>">
                        <input type="checkbox" name="points_criteria[]" id="<?= $key ?>"
                               value="<?= $key ?>" <?= $checked ?> />
                        <strong><?= $optionValue['points'] ?> pkt</strong>
                        <span><?= $optionValue['label'] ?></span>
                    </label>
                    <br />
                <?php endforeach; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * @param array $points_criteria
     */
    public function set_points_criteria(array $points_criteria)
    {
        $this->points_criteria = $points_criteria;
    }

    /**
     * @return int
     */
    public function get_stop_id(): int
    {
        return $this->stop_id;
    }

    /**
     * @param int $stop_id
     */
    public function set_stop_id(int $stop_id): void
    {
        $this->stop_id = $stop_id;
    }
}