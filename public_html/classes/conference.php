<?php

class Conference {
    private $db;
    private $table_name = 'conferences';

    public $jitsi_component;
    public $start;
    public $end;
    public $conference_id;
    public $conference_name;
    public $participants;
    public $name_count;
    public $conference_host;

    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->queries = include('queries.php');
    }

    // list of all conferences
    public function conferences_all_formatted() {
        $sql = $this->queries['conferences_all_formatted'];
        $query = $this->db->prepare($sql);
        $query->execute();

        return $query;
    }

}

?>
