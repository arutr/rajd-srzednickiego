<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('Rajd_Srz_Stop')) {
    require_once RAJD_SRZ_PLUGIN_DIR . '/includes/Rajd_Srz_Stop.php';
}

class Rajd_Srz_Stops_List_Table extends WP_List_Table
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct([
            'singular' => 'Przystanek',
            'plural'   => 'Przystanki',
            'ajax'     => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'ID':
            case 'name':
            case 'station_id':
            case 'sort_order':
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
    protected function column_action_move($item): string
    {
        $moveUp = add_query_arg(
            ['action' => 'move_up', 'id' => $item['ID']],
            menu_page_url('stops', false)
        );
        $moveDown = add_query_arg(
            ['action' => 'move_down', 'id' => $item['ID']],
            menu_page_url('stops', false)
        );

        return '
            <a href="' . $moveUp . '"><strong>▲</strong></a>
            <a href="' . $moveDown . '"><strong>▼</strong></a>
        ';
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function column_action_delete($item): string
    {
        $url = add_query_arg(
            ['action' => 'delete_stop', 'id' => $item['ID']],
            menu_page_url('stops', false)
        );

        return '<a href="' . $url . '"><strong>Usuń</strong></a>';
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function column_name($item): string
    {
        $url = add_query_arg('id', $item['ID'], menu_page_url('stop-edit', false));

        return '<strong><a class="row-title" href="' . $url . '">' . $item['name'] . '</a></strong>';
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function column_station_id($item): string
    {
        $station = new Rajd_Srz_Station($item['station_id']);
        $url = add_query_arg('id', $item['station_id'], menu_page_url('station-edit', false));

        return '<a href="' . $url . '">' . $station->get_name() . '</a>';
    }

    /**
     * @inheritDoc
     */
    public function get_columns(): array
    {
        return [
            'ID'         => 'Nr',
            'name'       => __('Name'),
            'station_id' => 'Stacja',
            'sort_order' => 'Lp',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function get_sortable_columns(): array
    {
        return [
            'sort_order' => ['sort_order', false],
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

        $column_keys = join(',', array_keys($this->get_columns()));
        $sql = "SELECT $column_keys FROM rajd_srz_stops WHERE 1=1 $search"
               . $wpdb->prepare("ORDER BY id DESC LIMIT %d OFFSET %d;", $per_page, $offset);
        $items = $wpdb->get_results($sql, ARRAY_A);

        $columns = $this->get_columns();
        $columns['action_move'] = '';
        $columns['action_delete'] = '';
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];
        usort($items, [&$this, 'usort_reorder']);
        $count = $wpdb->get_var("SELECT COUNT(id) FROM rajd_srz_stops WHERE 1=1 {$search};");

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
        $orderby = !empty($_GET['orderby']) ? $_GET['orderby'] : 'sort_order';
        // If no order, default to asc
        $order = !empty($_GET['order']) ? $_GET['order'] : 'asc';

        if ($orderby == 'sort_order') {
            $result = intval($a[$orderby]) - intval($b[$orderby]);
        } else {
            // Determine sort order
            $result = strcmp($a[$orderby], $b[$orderby]);
        }

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : - $result;
    }
}