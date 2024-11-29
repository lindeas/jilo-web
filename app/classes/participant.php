<?php

/**
 * class Participant
 *
 * This class provides methods to retrieve information about participants and their related conference data.
 * It supports querying participant details by ID, name, or IP, as well as listing all participants and counting them within a specific time frame.
 */
class Participant {
    /**
     * @var PDO|null $db The database connection instance.
     */
    private $db;

    /**
     * Constructor
     * Initializes the database connection.
     *
     * @param object $database The database object to initialize the connection.
     */
    public function __construct($database) {
        $this->db = $database->getConnection();
    }


    /**
     * Retrieve conferences by participant ID within a specified time period.
     *
     * @param string $participant_id  The participant's ID (endpoint_id).
     * @param string $from_time       The start date (format: 'YYYY-MM-DD'). Defaults to '0000-01-01' if empty.
     * @param string $until_time      The end date (format: 'YYYY-MM-DD'). Defaults to '9999-12-31' if empty.
     * @param int    $offset          The offset for pagination.
     * @param int    $items_per_page  The number of items per page for pagination.
     *
     * @return array List of conferences involving the specified participant ID.
     */
    public function conferenceByParticipantId($participant_id, $from_time, $until_time, $offset=0, $items_per_page='') {

        // time period drill-down
        // FIXME make it similar to the bash version
        if (empty($from_time)) {
            $from_time = '0000-01-01';
        }
        if (empty($until_time)) {
            $until_time = '9999-12-31';
        }

        // this is needed for compatibility with the bash version, so we use '%s' placeholders
        $from_time = htmlspecialchars(strip_tags($from_time));
        $until_time = htmlspecialchars(strip_tags($until_time));

        // list conferences where participant ID (endpoint_id) is found
        $sql = "
SELECT
    pe.time,
    c.conference_id,
    c.conference_name,
    c.conference_host,
    pe.loglevel,
    pe.event_type,
    p.endpoint_id AS participant_id,
    pe.event_param
FROM
    conferences c
LEFT JOIN
    conference_events ce ON c.conference_id = ce.conference_id
LEFT JOIN
    participants p ON c.conference_id = p.conference_id
LEFT JOIN
    participant_events pe ON p.endpoint_id = pe.participant_id
WHERE
    p.endpoint_id = '%s'
AND (pe.time >= '%s 00:00:00' AND pe.time <= '%s 23:59:59')

UNION

SELECT
    ce.time AS event_time,
    c.conference_id,
    c.conference_name,
    c.conference_host,
    ce.loglevel,
    ce.conference_event AS event_type,
    NULL AS participant_id,
    ce.conference_param AS event_param
FROM
    conferences c
LEFT JOIN
    conference_events ce ON c.conference_id = ce.conference_id
WHERE
    participant_id = '%s'
AND (event_time >= '%s 00:00:00' AND event_time <= '%s 23:59:59')

ORDER BY
    pe.time";

        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        $sql = sprintf($sql, $participant_id, $from_time, $until_time, $participant_id, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Retrieve conferences by participant name within a specified time period.
     *
     * @param string $participant_name The participant's name (stats_id).
     * @param string $from_time        The start date (format: 'YYYY-MM-DD'). Defaults to '0000-01-01' if empty.
     * @param string $until_time       The end date (format: 'YYYY-MM-DD'). Defaults to '9999-12-31' if empty.
     * @param int    $offset           The offset for pagination.
     * @param int    $items_per_page   The number of items per page for pagination.
     *
     * @return array List of conferences involving the specified participant name.
     */
    public function conferenceByParticipantName($participant_name, $from_time, $until_time, $offset=0, $items_per_page='') {

        // time period drill-down
        // FIXME make it similar to the bash version
        if (empty($from_time)) {
            $from_time = '0000-01-01';
        }
        if (empty($until_time)) {
            $until_time = '9999-12-31';
        }

        // this is needed for compatibility with the bash version, so we use '%s' placeholders
        $from_time = htmlspecialchars(strip_tags($from_time));
        $until_time = htmlspecialchars(strip_tags($until_time));

        // list conferences where participant name (stats_id) is found
        $sql = "
SELECT
    pe.time,
    c.conference_id,
    c.conference_name,
    c.conference_host,
    pe.loglevel,
    pe.event_type,
    p.endpoint_id AS participant_id,
    pe.event_param
FROM
    conferences c
LEFT JOIN
    conference_events ce ON c.conference_id = ce.conference_id
LEFT JOIN
    participants p ON c.conference_id = p.conference_id
LEFT JOIN
    participant_events pe ON p.endpoint_id = pe.participant_id
WHERE
    pe.event_type = 'stats_id' AND pe.event_param LIKE '%%%s%%'
AND (pe.time >= '%s 00:00:00' AND pe.time <= '%s 23:59:59')

UNION

SELECT
    ce.time AS event_time,
    c.conference_id,
    c.conference_name,
    c.conference_host,
    ce.loglevel,
    ce.conference_event AS event_type,
    NULL AS participant_id,
    ce.conference_param AS event_param
FROM
    conferences c
LEFT JOIN
    conference_events ce ON c.conference_id = ce.conference_id
WHERE
    event_type = 'stats_id' AND event_param LIKE '%%%s%%'
AND (event_time >= '%s 00:00:00' AND event_time <= '%s 23:59:59')

ORDER BY
    pe.time";

        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        $sql = sprintf($sql, $participant_name, $from_time, $until_time, $participant_name, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Retrieve conferences by participant IP within a specified time period.
     *
     * @param string $participant_ip   The participant's IP address.
     * @param string $from_time        The start date (format: 'YYYY-MM-DD'). Defaults to '0000-01-01' if empty.
     * @param string $until_time       The end date (format: 'YYYY-MM-DD'). Defaults to '9999-12-31' if empty.
     * @param int    $offset           The offset for pagination.
     * @param int    $items_per_page   The number of items per page for pagination.
     *
     * @return array List of conferences involving the specified participant IP.
     */
    public function conferenceByParticipantIP($participant_ip, $from_time, $until_time, $offset=0, $items_per_page='') {

        // time period drill-down
        // FIXME make it similar to the bash version
        if (empty($from_time)) {
            $from_time = '0000-01-01';
        }
        if (empty($until_time)) {
            $until_time = '9999-12-31';
        }

        // this is needed for compatibility with the bash version, so we use '%s' placeholders
        $from_time = htmlspecialchars(strip_tags($from_time));
        $until_time = htmlspecialchars(strip_tags($until_time));

        // list conferences where participant IP is found
        $sql = "
SELECT
    pe.time,
    c.conference_id,
    c.conference_name,
    c.conference_host,
    pe.loglevel,
    pe.event_type,
    p.endpoint_id AS participant_id,
    pe.event_param
FROM
    conferences c
LEFT JOIN
    conference_events ce ON c.conference_id = ce.conference_id
LEFT JOIN
    participants p ON c.conference_id = p.conference_id
LEFT JOIN
    participant_events pe ON p.endpoint_id = pe.participant_id
WHERE
    pe.event_type = 'pair selected' AND pe.event_param = '%s'
AND (pe.time >= '%s 00:00:00' AND pe.time <= '%s 23:59:59')

UNION

SELECT
    ce.time AS event_time,
    c.conference_id,
    c.conference_name,
    c.conference_host,
    ce.loglevel,
    ce.conference_event AS event_type,
    NULL AS participant_id,
    ce.conference_param AS event_param
FROM
    conferences c
LEFT JOIN
    conference_events ce ON c.conference_id = ce.conference_id
WHERE
    event_type = 'pair selected' AND event_param = '%s'
AND (event_time >= '%s 00:00:00' AND event_time <= '%s 23:59:59')

ORDER BY
    pe.time";

        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        $sql = sprintf($sql, $participant_ip, $from_time, $until_time, $participant_ip, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Retrieve a list of all participants within a specified time period.
     *
     * @param string $from_time       The start date (format: 'YYYY-MM-DD'). Defaults to '0000-01-01' if empty.
     * @param string $until_time      The end date (format: 'YYYY-MM-DD'). Defaults to '9999-12-31' if empty.
     * @param int    $offset          The offset for pagination.
     * @param int    $items_per_page  The number of items per page for pagination.
     *
     * @return array List of all participants.
     */
    public function participantsAll($from_time, $until_time, $offset=0, $items_per_page='') {

        // time period drill-down
        // FIXME make it similar to the bash version
        if (empty($from_time)) {
            $from_time = '0000-01-01';
        }
        if (empty($until_time)) {
            $until_time = '9999-12-31';
        }

        // this is needed for compatibility with the bash version, so we use '%s' placeholders
        $from_time = htmlspecialchars(strip_tags($from_time));
        $until_time = htmlspecialchars(strip_tags($until_time));

        // list all participants
        $sql = "
SELECT DISTINCT
    p.jitsi_component, p.endpoint_id, p.conference_id
FROM
    participants p
JOIN
    participant_events pe ON p.endpoint_id = pe.participant_id
WHERE
    pe.time >= '%s 00:00:00' AND pe.time <= '%s 23:59:59'
ORDER BY p.id";

        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        $sql = sprintf($sql, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Count the number of participants within a specified time period.
     *
     * @param string $from_time  The start date (format: 'YYYY-MM-DD'). Defaults to '0000-01-01' if empty.
     * @param string $until_time The end date (format: 'YYYY-MM-DD'). Defaults to '9999-12-31' if empty.
     *
     * @return int The number of participants.
     */
    public function participantNumber($from_time, $until_time) {

        // time period drill-down
        // FIXME make it similar to the bash version
        if (empty($from_time)) {
            $from_time = '0000-01-01';
        }
        if (empty($until_time)) {
            $until_time = '9999-12-31';
        }

        // this is needed for compatibility with the bash version, so we use '%s' placeholders
        $from_time = htmlspecialchars(strip_tags($from_time));
        $until_time = htmlspecialchars(strip_tags($until_time));

        // number of participants for time period (if given)
        $sql = "
SELECT COUNT(DISTINCT p.endpoint_id) as participants
FROM
    participants p
LEFT JOIN
    participant_events pe ON p.endpoint_id = pe.participant_id
WHERE
    (pe.time >= '%s 00:00:00' AND pe.time <= '%s 23:59:59')
AND pe.event_type = 'participant joining'";

        $sql = sprintf($sql, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>
