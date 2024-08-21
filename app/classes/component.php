<?php

class Component {
    private $db;
    private $queries;

    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->queries = include('queries.php');
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
        $sql = 'SELECT jitsi_component, loglevel, time, component_id, event_type, event_param
                FROM
                    jitsi_components
                WHERE
                    jitsi_component = :jitsi_component
                AND
                    component_id = :component_id
                AND
                    (time >= :from_time AND time <= :until_time)
                ORDER BY
                    time';

        $query = $this->db->prepare($sql);
        $query->execute([
            ':jitsi_component'		=> $jitsi_component,
            ':component_id'		=> $component_id,
            ':from_time'		=> $from_time . ' 00:00:00',
            ':until_time'		=> $until_time . ' 23:59:59',
        ]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


}

?>
