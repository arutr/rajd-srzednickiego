<?php

class Rajd_Srz_Station
{
    /**
     * @var int
     */
    private int $ID;

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
            $wpdb->prepare("SELECT * FROM `rajd_srz_stations` WHERE ID = %d;", $id)
        );

        if (!$row) {
            return new WP_Error('invalid_station', 'Stacja nie istnieje.');
        }

        foreach (get_object_vars($row) as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * Get all stations.
     *
     * @return array
     */
    public static function load_all(): array
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM `rajd_srz_stations` ORDER BY sort_order;", ARRAY_A);
    }

    /**
     * @return $this|WP_Error
     */
    public function create()
    {
        global $wpdb;

        if (empty($this->name)) {
            return new WP_Error('empty_name', 'Nazwa stacji jest wymagana.');
        }

        $this->sort_order = intval($wpdb->get_var("SELECT COUNT(*) FROM `rajd_srz_stations`;")) + 1;

        $rows = $wpdb->insert(RAJD_SRZ_STATIONS_TABLE, wp_unslash([
            'name'        => $this->name,
            'description' => $this->description,
            'sort_order' => $this->sort_order
        ]));

        if ($rows === false) {
            return new WP_Error(
                'db_error',
                'Nie udało się stworzyć stacji. Spróbuj ponownie lub skontaktuj się z nami!'
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
            return new WP_Error('empty_name', 'Nazwa stacji jest wymagana.');
        }

        $rows = $wpdb->update(RAJD_SRZ_STATIONS_TABLE, wp_unslash([
            'name' => $this->name,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
        ]), [
            'ID' => strval($this->ID),
        ]);

        if ($rows === false) {
            return new WP_Error(
                'db_error',
                'Nie udało się zaktualizować stacji. Spróbuj ponownie lub skontaktuj się z nami!'
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

        $rows = $wpdb->delete(RAJD_SRZ_STATIONS_TABLE, [
            'ID' => $this->ID
        ]);

        if ($rows === false) {
            return new WP_Error('db_error', 'Nie udało się skasować stacji.');
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