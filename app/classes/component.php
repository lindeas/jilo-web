<?php

class Component {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }


    // list of component events
    public function jitsiComponents($jitsi_component, $component_id, $event_type, $from_time, $until_time, $offset=0, $items_per_page='') {

        // time period drill-down
        // FIXME make it similar to the bash version
        if (empty($from_time)) {
            $from_time = '0000-01-01';
        }
        if (empty($until_time)) {
            $until_time = '9999-12-31';
        }
        $from_time = htmlspecialchars(strip_tags($from_time));
        $until_time = htmlspecialchars(strip_tags($until_time));

        // list of jitsi component events
        $sql = "
SELECT
    jitsi_component, loglevel, time, component_id, event_type, event_param
FROM
    jitsi_components
WHERE
    jitsi_component = %s
AND
    component_id = %s";
        if ($event_type != '' && $event_type != 'event_type') {
            $sql .= "
AND
    event_type LIKE '%%%s%%'";
        }
        $sql .= "
AND
    (time >= '%s 00:00:00' AND time <= '%s 23:59:59')
ORDER BY
    time";

        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        // FIXME this needs to be done with bound params instead of sprintf
        if ($event_type != '' && $event_type != 'event_type') {
            $sql = sprintf($sql, $jitsi_component, $component_id, $event_type, $from_time, $until_time);
            $sql = str_replace("LIKE '%'", "LIKE '%", $sql);
            $sql = str_replace("'%'\nAND", "%' AND", $sql);
        } else {
            $sql = sprintf($sql, $jitsi_component, $component_id, $from_time, $until_time);
        }

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


}

?>
