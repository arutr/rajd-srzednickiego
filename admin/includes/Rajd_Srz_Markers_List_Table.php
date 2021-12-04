<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('Rajd_Srz_Marker')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Marker.php';
}

if (!class_exists('Rajd_Srz_Stop')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Stop.php';
}

class Rajd_Srz_Markers_List_Table extends WP_List_Table
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct([
            'singular' => 'Pinezka',
            'plural' => 'Pinezki',
            'ajax' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'ID':
            case 'user_id':
            case 'stop_id':
            case 'type':
                return $item[$column_name];
            default:
                return '';
        }
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function column_action_edit($item): string
    {
        $viewUrl = add_query_arg(['page' => 'marker-edit', 'id' => $item['ID']]);

        return '<a href="' . $viewUrl .'"><strong>' . __('Edit') . '</strong></a>';
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function column_action_delete($item): string
    {
        $deleteUrl = add_query_arg(['action' => 'delete_marker', 'id' => $item['ID']]);

        return '<a href="' . $deleteUrl .'"><strong>' . __('Delete') . '</strong></a>';
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function column_name($item): string
    {
        $url = add_query_arg('id', $item['ID'], menu_page_url('marker-edit', false));

        return '<strong><a class="row-title" href="' . $url . '">' . $item['name'] . '</a></strong>';
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function column_user_id($item): string
    {
        $author = new WP_User($item['user_id']);

        return $author->nickname;
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function column_stop_id($item): string
    {
        $stop = new Rajd_Srz_Stop($item['stop_id']);
        $url = add_query_arg('id', $item['stop_id'], menu_page_url('stop-edit', false));

        return '<a href="' . $url . '">' . $stop->get_name() . '</a>';
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function column_type($item): string
    {
        return Rajd_Srz_Marker::get_type_label($item['type']);
    }

    /**
     * @inheritDoc
     */
    public function get_columns(): array
    {
        return [
            'ID'      => 'Nr',
            'user_id' => __('Author'),
            'stop_id' => 'Przystanek',
            'type'    => __('Type'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function get_sortable_columns(): array
    {
        return [
            'ID'        => ['ID', false],
            'stop_id'   => ['stop_id', false],
            'type'      => ['type', false],
        ];
    }

    /**
     * @inheritDoc
     */
    public function prepare_items()
    {
        global $wpdb;

        $per_page = 20;
        $current_page = $this->get_pagenum();

        if ($current_page > 1) {
            $offset = $per_page * ($current_page - 1);
        } else {
            $offset = 0;
        }

        $search = '';

        // search by marker name
        if (!empty($_REQUEST['s'])) {
            $search = "AND name LIKE '%" . esc_sql($wpdb->esc_like($_REQUEST['s'])) . "%'";
        }

        $type = '';

        if (!empty($_REQUEST['type']) && $_REQUEST['type'] == 'mine') {
            $type = 'AND user_id = ' . get_current_user_id() . ' ';
        }

        $column_keys = join(',', array_keys($this->get_columns()));
        $sql = "SELECT $column_keys FROM rajd_srz_markers WHERE 1=1 $search $type"
               . $wpdb->prepare("ORDER BY id DESC LIMIT %d OFFSET %d;", $per_page, $offset);
        $items = $wpdb->get_results($sql, ARRAY_A);

        $columns = $this->get_columns();
        $columns['action_edit'] = '';
        $columns['action_delete'] = '';
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];
        usort($items, [&$this, 'usort_reorder']);
        $count = $wpdb->get_var("SELECT COUNT(id) FROM rajd_srz_markers WHERE 1=1 {$search};");

        $this->items = $items;

        // Set the pagination
        $this->set_pagination_args([
            'total_items' => $count,
            'per_page'    => $per_page,
            'total_pages' => ceil($count / $per_page),
        ]);
    }

    /**
     * @param $a
     * @param $b
     *
     * @return float|int
     */
    private function usort_reorder($a, $b)
    {
        // If no sort, default to title
        $orderby = !empty($_GET['orderby']) ? $_GET['orderby'] : 'ID';
        // If no order, default to asc
        $order = !empty($_GET['order']) ? $_GET['order'] : 'desc';

        if ($orderby == 'ID') {
            $result = intval($a[$orderby]) - intval($b[$orderby]);
        } else {
            // Determine sort order
            $result = strcmp($a[$orderby], $b[$orderby]);
        }

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }
}