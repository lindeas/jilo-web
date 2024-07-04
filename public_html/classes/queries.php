<?php

// all sql queries for the jilo database in one place

return [

    // list of conferences for time period (if given)
    // fields: component, duration, conference ID, conference name, number of participants, name count (the conf name is found), conference host
    'conferences_all_formatted' => "
SELECT DISTINCT
    c.jitsi_component,
    (SELECT ce.time
        FROM conference_events ce
        WHERE
            ce.conference_id = c.conference_id
            AND
            ce.conference_event = 'conference expired')
    AS start,
    (SELECT ce.time
        FROM conference_events ce
        WHERE
            ce.conference_id = c.conference_id
            AND
            ce.conference_event = 'conference created')
    AS end,
    c.conference_id,
    c.conference_name,
    (SELECT COUNT(pe.participant_id) AS participants
        FROM participant_events pe
        WHERE
            pe.event_type = 'participant joining'
            AND
            pe.event_param = c.conference_id),
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
    c.id;",


    // search for a conference by its ID for a time period (if given)
    'conference_by_id' => "
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
    pe.time;"


];

?>
