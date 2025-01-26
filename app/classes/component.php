<?php

/**
 * class Component
 *
 * Provides methods to interact with Jitsi component events in the database.
 */
class Component {
    /**
     * @var PDO|null $db The database connection instance.
     */
    private $db;

    /**
     * Component constructor.
     * Initializes the database connection.
     *
     * @param object $database The database object to initialize the connection.
     */
    public function __construct($database) {
        $this->db = $database->getConnection();
    }


    /**
     * Retrieves Jitsi component events based on various filters.
     *
     * @param string $jitsi_component The Jitsi component name.
     * @param int $component_id The component ID.
     * @param string $event_type The type of event to filter by.
     * @param string $from_time The start date in 'YYYY-MM-DD' format.
     * @param string $until_time The end date in 'YYYY-MM-DD' format.
     * @param int $offset The offset for pagination.
     * @param int $items_per_page The number of items to retrieve per page.
     *
     * @return array The list of Jitsi component events or an empty array if no results.
     */
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
        $sql = "SELECT jitsi_component, loglevel, time, component_id, event_type, event_param
                FROM jitsi_components
                WHERE LOWER(jitsi_component) = LOWER(%s)
                AND component_id = %s";
        if ($event_type != '' && $event_type != 'event_type') {
            $sql .= " AND event_type LIKE '%%%s%%'";
        }
        $sql .= " AND (time >= '%s 00:00:00' AND time <= '%s 23:59:59') ORDER BY time";

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

    /**
     * Gets the total count of components events matching the filter criteria
     * 
     * @param string $jitsi_component The Jitsi component name.
     * @param int $component_id The component ID.
     * @param string $event_type The type of event to filter by.
     * @param string $from_time The start date in 'YYYY-MM-DD' format.
     * @param string $until_time The end date in 'YYYY-MM-DD' format.
     * 
     * @return int The total count of matching components
     */
    public function getComponentEventsCount($jitsi_component, $component_id, $event_type, $from_time, $until_time) {
        // time period drill-down
        if (empty($from_time)) {
            $from_time = '0000-01-01';
        }
        if (empty($until_time)) {
            $until_time = '9999-12-31';
        }
        $from_time = htmlspecialchars(strip_tags($from_time));
        $until_time = htmlspecialchars(strip_tags($until_time));

        // Build the query
        $sql = "SELECT COUNT(*) as total
                FROM jitsi_events
                WHERE time >= :from_time
                AND time <= :until_time
                AND LOWER(jitsi_component) = LOWER(:jitsi_component)
                AND component_id) = :component_id
                AND LOWER(event_type) = LOWER(:event_type)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':from_time', $from_time);
            $stmt->bindParam(':until_time', $until_time);
            $stmt->bindParam(':jitsi_component', $jitsi_component);
            $stmt->bindParam(':component_id', $component_id);
            $stmt->bindParam(':event_type', $event_type);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Error in getComponentCount: " . $e->getMessage());
            return 0;
        }
    }
}

?>
