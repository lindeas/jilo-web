<?php

/**
 * Class Server
 *
 * Handles server-related operations, including retrieving server status.
 */
class Server {
    /**
     * @var PDO|null $db The database connection instance.
     */
    private $db;

    /**
     * Server constructor.
     *
     * @param object $database An instance of a database connection handler.
     */
    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Checks the status of a Jilo server by sending a GET request to its health endpoint.
     *
     * @param string $host The server hostname or IP address (default: '127.0.0.1').
     * @param int $port The port on which the server is running (default: 8080).
     * @param string $endpoint The health check endpoint path (default: '/health').
     * @return bool True if the server returns a 200 OK status, otherwise false.
     */
    public function getServerStatus($host = '127.0.0.1', $port = 8080, $endpoint = '/health') {
        $url = "http://$host:$port$endpoint";
        $options = [
            'http' => [
                'method'    => 'GET',
                'timeout'   => 3,
            ],
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        // We check the response if it's 200 OK
        if ($response !== false && isset($http_response_header) && strpos($http_response_header[0], '200 OK') !== false) {
            return true;
        }

        // If it's not 200 OK
        return false;
    }

}

?>
