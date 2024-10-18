<?php

class Server {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    // get Jilo Server status
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
