<?php

class Conference {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }


    // search/list specific conference ID
    public function conferenceById($conference_id, $from_time, $until_time, $offset=0, $items_per_page='') {

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

        // search for a conference by its ID for a time period (if given)
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
    c.conference_id = '%s'
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
    c.conference_id = '%s'
AND (event_time >= '%s 00:00:00' AND event_time <= '%s 23:59:59')

ORDER BY
    pe.time";

        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        $sql = sprintf($sql, $conference_id, $from_time, $until_time, $conference_id, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // search/list specific conference name
    public function conferenceByName($conference_name, $from_time, $until_time, $offset=0, $items_per_page='') {

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

        // search for a conference by its name for a time period (if given)
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
    c.conference_name = '%s'
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
    c.conference_name = '%s'
AND (event_time >= '%s 00:00:00' AND event_time <= '%s 23:59:59')

ORDER BY
    pe.time";

        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        $sql = sprintf($sql, $conference_name, $from_time, $until_time, $conference_name, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // list of all conferences
    public function conferencesAllFormatted($from_time, $until_time, $offset=0, $items_per_page='') {

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

        // list of conferences for time period (if given)
        // fields: component, duration, conference ID, conference name, number of participants, name count (the conf name is found), conference host
        $sql = "
SELECT DISTINCT
    c.jitsi_component,
    (SELECT COALESCE
        (
            (SELECT ce.time
                FROM conference_events ce
                WHERE
                    ce.conference_id = c.conference_id
                    AND
                    ce.conference_event = 'conference created'
            ),
            (SELECT ce.time
                FROM conference_events ce
                WHERE
                    ce.conference_id = c.conference_id
                    AND
                    ce.conference_event = 'bridge selected'
            )
        )
    )
    AS start,
    (SELECT COALESCE
        (
            (SELECT ce.time
                FROM conference_events ce
                WHERE
                    ce.conference_id = c.conference_id
                    AND
                    (ce.conference_event = 'conference expired' OR ce.conference_event = 'conference stopped')
            ),
            (SELECT pe.time
                FROM participant_events pe
                WHERE
                    pe.event_param = c.conference_id
                ORDER BY pe.time DESC
                LIMIT 1
            )
        )
    )
    AS end,
    c.conference_id,
    c.conference_name,
    (SELECT COUNT(pe.participant_id)
        FROM participant_events pe
        WHERE
            pe.event_type = 'participant joining'
            AND
            pe.event_param = c.conference_id) AS participants,
    name_counts.name_count,
    c.conference_host
FROM
    conferences c
JOIN (
    SELECT
        conference_name,
        COUNT(*) AS name_count
    FROM
        conferences
    GROUP BY
        conference_name
) AS name_counts ON c.conference_name = name_counts.conference_name
JOIN
    conference_events ce ON c.conference_id = ce.conference_id
WHERE (ce.time >= '%s 00:00:00' AND ce.time <= '%s 23:59:59')
ORDER BY
    c.id";

        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        $sql = sprintf($sql, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // number of conferences
    public function conferenceNumber($from_time, $until_time) {

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

        // number of conferences for time period (if given)
        // FIXME sometimes there is no start/end time, find a way around this
        $sql = "
SELECT COUNT(*) AS conferences
    FROM (
    SELECT DISTINCT
        (SELECT COALESCE
            (
                (SELECT ce.time
                    FROM conference_events ce
                    WHERE
                        ce.conference_id = c.conference_id
                        AND
                        ce.conference_event = 'conference created'
                ),
                (SELECT ce.time
                    FROM conference_events ce
                    WHERE
                        ce.conference_id = c.conference_id
                        AND
                        ce.conference_event = 'bridge selected'
                )
            )
        ) AS start,
        (SELECT COALESCE
            (
                (SELECT ce.time
                    FROM conference_events ce
                    WHERE
                        ce.conference_id = c.conference_id
                        AND
                        (ce.conference_event = 'conference expired' OR ce.conference_event = 'conference stopped')
                ),
                (SELECT pe.time
                    FROM participant_events pe
                    WHERE
                        pe.event_param = c.conference_id
                        ORDER BY pe.time DESC
                        LIMIT 1
                )
            )
        ) AS end
        FROM conferences c
        JOIN
            conference_events ce ON c.conference_id = ce.conference_id
        WHERE (start >= '%s 00:00:00' AND end <= '%s 23:59:59')
    ) AS subquery";

        $sql = sprintf($sql, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


}

?>
