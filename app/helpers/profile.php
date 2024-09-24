<?php

// get the UTC offset of a specified timezone
function getUTCOffset($timezone) {
    $formattedOffset = '';
    if (isset($timezone)) {

        $datetime = new DateTime("now", new DateTimeZone($timezone));
        $offsetInSeconds = $datetime->getOffset();

        $hours = intdiv($offsetInSeconds, 3600);
        $minutes = ($offsetInSeconds % 3600) / 60;
        $formattedOffset = sprintf("UTC%+03d:%02d", $hours, $minutes); // Format UTC+01:00
    }

    return $formattedOffset;

}

// switch platforms
function switchPlatform($platform_id) {
    // get the current URL and parse it
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $current_url = "$scheme://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url_components = parse_url($current_url);

    // parse query parameters if they exist
    parse_str($url_components['query'] ?? '', $query_params);

    // check if the 'platform' parameter is set
    if (isset($query_params['platform'])) {
        // change the platform to the new platform_id
        $query_params['platform'] = $platform_id;

        // rebuild the query and the URL
        $new_query_string = http_build_query($query_params);
        $new_url = $scheme . '://' . $url_components['host'] . $url_components['path'] . '?' . $new_query_string;

        // return the new URL with the new platform_id
        return $new_url;

    // there is no 'platform', we redirect to front page of the new platform_id
    } else {
        return $current_url;
    }
}

?>
