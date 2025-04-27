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
        global $logObject;
        try {
            // Add time part to dates if not present
            if (strlen($from_time) <= 10) {
                $from_time .= ' 00:00:00';
            }
            if (strlen($until_time) <= 10) {
                $until_time .= ' 23:59:59';
            }

            // list of jitsi component events
            $sql = "SELECT jitsi_component, loglevel, time, component_id, event_type, event_param
                    FROM jitsi_components
                    WHERE time >= :from_time 
                    AND time <= :until_time";

            // Only add component and event filters if they're not the default values
            if ($jitsi_component !== 'jitsi_component') {
                $sql .= " AND LOWER(jitsi_component) = LOWER(:jitsi_component)";
            }
            if ($component_id !== 'component_id') {
                $sql .= " AND component_id = :component_id";
            }
            if ($event_type !== 'event_type') {
                $sql .= " AND event_type LIKE :event_type";
            }

            $sql .= " ORDER BY time";

            if ($items_per_page) {
                $sql .= ' LIMIT :offset, :items_per_page';
            }

            $stmt = $this->db->prepare($sql);

            // Bind parameters only if they're not default values
            if ($jitsi_component !== 'jitsi_component') {
                $stmt->bindValue(':jitsi_component', trim($jitsi_component, "'"));
            }
            if ($component_id !== 'component_id') {
                $stmt->bindValue(':component_id', trim($component_id, "'"));
            }
            if ($event_type !== 'event_type') {
                $stmt->bindValue(':event_type', '%' . trim($event_type, "'") . '%');
            }

            $stmt->bindParam(':from_time', $from_time);
            $stmt->bindParam(':until_time', $until_time);

            if ($items_per_page) {
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->bindParam(':items_per_page', $items_per_page, PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($result)) {
                $logObject->log('info', "Retrieved " . count($result) . " Jitsi component events", ['user_id' => $userId, 'scope' => 'system']);
            }
            return $result;
        } catch (PDOException $e) {
            $logObject->log('error', "Failed to retrieve Jitsi component events: " . $e->getMessage(), ['user_id' => $userId, 'scope' => 'system']);
            return [];
        }
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
        global $logObject;
        try {
            // Add time part to dates if not present
            if (strlen($from_time) <= 10) {
                $from_time .= ' 00:00:00';
            }
            if (strlen($until_time) <= 10) {
                $until_time .= ' 23:59:59';
            }

            // Build the query
            $sql = "SELECT COUNT(*) as total
                    FROM jitsi_components
                    WHERE time >= :from_time
                    AND time <= :until_time";

            // Only add component and event filters if they're not the default values
            if ($jitsi_component !== 'jitsi_component') {
                $sql .= " AND LOWER(jitsi_component) = LOWER(:jitsi_component)";
            }
            if ($component_id !== 'component_id') {
                $sql .= " AND component_id = :component_id";
            }
            if ($event_type !== 'event_type') {
                $sql .= " AND event_type LIKE :event_type";
            }

            $stmt = $this->db->prepare($sql);

            // Bind parameters only if they're not default values
            if ($jitsi_component !== 'jitsi_component') {
                $stmt->bindValue(':jitsi_component', trim($jitsi_component, "'"));
            }
            if ($component_id !== 'component_id') {
                $stmt->bindValue(':component_id', trim($component_id, "'"));
            }
            if ($event_type !== 'event_type') {
                $stmt->bindValue(':event_type', '%' . trim($event_type, "'") . '%');
            }

            $stmt->bindParam(':from_time', $from_time);
            $stmt->bindParam(':until_time', $until_time);

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            $logObject->log('error', "Failed to retrieve component events count: " . $e->getMessage(), ['user_id' => $userId, 'scope' => 'system']);
            return 0;
        }
    }
}
