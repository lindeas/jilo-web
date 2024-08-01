<?php

// all sql queries for the jilo database in one place

return [

    // number of conferences for time period (if given)
    'conference_number' => "
SELECT COUNT(c.conference_id) as conferences
FROM
    conferences c
LEFT JOIN
    conference_events ce ON c.conference_id = ce.conference_id
WHERE
    (ce.time >= '%s 00:00:00' AND ce.time <= '%s 23:59:59')
AND ce.conference_event = 'conference created'",


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
    pe.time;",


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
            ce.conference_event = 'conference created')
    AS start,
    (SELECT ce.time
        FROM conference_events ce
        WHERE
            ce.conference_id = c.conference_id
            AND
            ce.conference_event = 'conference expired')
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
    pe.time;",


    // search for a conference by its name for a time period (if given)
    'conference_by_name' => "
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
    pe.time;",


    // number of participants for time period (if given)
    'participant_number' => "
SELECT COUNT(DISTINCT p.endpoint_id) as participants
FROM
    participants p
LEFT JOIN
    participant_events pe ON p.endpoint_id = pe.participant_id
WHERE
    (pe.time >= '%s 00:00:00' AND pe.time <= '%s 23:59:59')
AND pe.event_type = 'participant joining'",


    // list all participants
    'participants_all' => "
SELECT DISTINCT
    p.jitsi_component, p.endpoint_id, p.conference_id
FROM
    participants p
JOIN
    participant_events pe ON p.endpoint_id = pe.participant_id
WHERE
    pe.time >= '%s 00:00:00' AND pe.time <= '%s 23:59:59'
ORDER BY p.id;",


    // list conferences where participant ID (endpoint_id) is found
    'conference_by_participant_id' => "
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
    pe.time;",


    // list conferences where participant name (stats_id) is found
    'participant_by_stats_id' => "
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
    pe.time;",


    // list conferences where participant IP is found
    'participant_by_ip' => "
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
    pe.time;",


    // list of jitsi component events
    'jitsi_components' => "
SELECT jitsi_component, loglevel, time, component_id, event_type, event_param
FROM
    jitsi_components
WHERE
    jitsi_component = %s
AND
    component_id = %s
AND
    (time >= '%s 00:00:00' AND time <= '%s 23:59:59')
ORDER BY
    time;",


];

?>
