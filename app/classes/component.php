<?php

class Component {
    private $db;
    private $queries;

    public function __construct($database) {
        $this->db = $database->getConnection();
//        $this->queries = include('queries.php');
    }


    // list of component events
    public function jitsiComponents($jitsi_component, $component_id, $from_time, $until_time) {

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
        $sql = "SELECT jitsi_component, loglevel, time, component_id, event_type, event_param
                FROM
                    jitsi_components
                WHERE
                    jitsi_component = %s
                AND
                    component_id = %s
                AND
                    (time >= '%s 00:00:00' AND time <= '%s 23:59:59')
                ORDER BY
                    time";
        $sql = sprintf($sql, $jitsi_component, $component_id, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


}

?>
