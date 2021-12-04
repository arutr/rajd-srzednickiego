<?php
if (!class_exists('Rajd_Srz_Station')) {
    require_once __DIR__ . '/Rajd_Srz_Station.php';
}

class Rajd_Srz_Stop
{
    /**
     * @var int
     */
    private int $ID;

    /**
     * @var int
     */
    private int $station_id = 0;

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var string
     */
    private string $description = '';

    /**
     * @var int
     */
    private int $sort_order = 0;

    /**
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * @param int $id
     *
     * @return $this|WP_Error
     */
    public function load(int $id)
    {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `rajd_srz_stops` WHERE ID = %d;", $id)
        );

        if (!$row) {
            return new WP_Error('invalid_stop', 'Przystanek nie istnieje.');
        }

        foreach (get_object_vars($row) as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * @param string $station_id
     *
     * @return array
     */
    public static function load_all_by_station_id(string $station_id): array
    {
        global $wpdb;

        return $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM `rajd_srz_stops` WHERE station_id = %d ORDER BY sort_order;", $station_id),
                ARRAY_A
        );
    }

    /**
     * @param string $stop_id
     *
     * @return bool
     */
    public static function has_user_submitted(string $stop_id): bool
    {
        global $wpdb;

        $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM `rajd_srz_markers` WHERE `stop_id` = %d AND `user_id` = %d",
                    $stop_id,
                    get_current_user_id()
                )
        );

        return $row != null;
    }

    /**
     * @return $this|WP_Error
     */
    public function create()
    {
        global $wpdb;

        if (empty($this->name)) {
            return new WP_Error('empty_name', 'Nazwa przystanku jest wymagana.');
        }

        if (empty($this->station_id)) {
            return new WP_Error('empty_station_id', 'Stacja jest wymagana.');
        }

        $this->sort_order = intval($wpdb->get_var("SELECT COUNT(*) FROM `rajd_srz_stops`;")) + 1;

        $rows = $wpdb->insert(RAJD_SRZ_STOPS_TABLE, wp_unslash([
            'station_id'  => $this->station_id,
            'name'        => $this->name,
            'description' => $this->description,
            'sort_order' => $this->sort_order
        ]));

        if ($rows === false) {
            return new WP_Error(
                'db_error',
                'Nie udało się stworzyć przystanku.'
            );
        }

        $this->ID = $wpdb->insert_id;

        return $this;
    }

    /**
     * @return $this|WP_Error
     */
    public function update()
    {
        global $wpdb;

        if (empty($this->name)) {
            return new WP_Error('empty_name', 'Nazwa przystanku jest wymagana.');
        }

        if (empty($this->station_id)) {
            return new WP_Error('empty_station_id', 'Stacja jest wymagana.');
        }

        $rows = $wpdb->update(RAJD_SRZ_STOPS_TABLE, wp_unslash([
            'station_id'  => $this->station_id,
            'name' => $this->name,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
        ]), [
            'ID' => strval($this->ID),
        ]);

        if ($rows === false) {
            return new WP_Error(
                'db_error',
                'Nie udało się zaktualizować przystanku.'
            );
        }

        return $this;
    }

    /**
     * @return bool|WP_Error
     */
    public function delete()
    {
        global $wpdb;

        $rows = $wpdb->delete(RAJD_SRZ_STOPS_TABLE, [
            'ID' => $this->ID
        ]);

        if ($rows === false) {
            return new WP_Error('db_error', 'Nie udało się skasować przystanku.');
        }

        return true;
    }

    /**
     * @return int
     */
    public function get_id(): int
    {
        return $this->ID;
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $value
     */
    public static function get_name_form_html(string $value)
    {
        $label = __('Name');
        $required = __('(required)');
        $value = esc_attr($value);

        echo <<<HTML
<tr class="form-field form-required">
    <th scope="row">
        <label for="name">
            $label
            <span class="description">$required</span>
        </label>
    </th>
    <td>
        <input name="name" type="text" id="name" value="$value" aria-required="true" maxlength="255" />
    </td>
</tr>
HTML;
    }

    /**
     * @return string
     */
    public function get_station_id(): string
    {
        return strval($this->station_id);
    }

    /**
     * @param string $station_id
     */
    public function set_station_id(string $station_id): void
    {
        $this->station_id = intval($station_id);
    }

    /**
     * @param string $value
     */
    public static function get_station_id_form_html(string $value)
    {
        $required = __('(required)');
        $value = esc_attr($value);
        ?>
        <tr class="form-field form-required">
            <th scope="row">
                <label for="station_id">
                    Stacja
                    <span class="description"><?= $required ?></span>
                </label>
            </th>
            <td>
                <select name="station_id" id="station_id" aria-required="true">
                    <option value="">--- Wybierz stację ---</option>
                    <?php foreach (Rajd_Srz_Station::load_all() as $station): ?>
                        <?php $selected = $value == $station['ID'] ? 'selected' : ''; ?>
                        <option value="<?= $station['ID'] ?>" <?= $selected ?>><?= $station['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * @return string
     */
    public function get_description(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function set_description(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param string $value
     */
    public static function get_description_form_html(string $value)
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
     * @return int
     */
    public function get_sort_order(): int
    {
        return $this->sort_order;
    }

    /**
     * @param int $sort_order
     */
    public function set_sort_order(int $sort_order): void
    {
        $this->sort_order = $sort_order;
    }
}