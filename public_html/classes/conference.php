<?php

class Conference {
    private $db;
    private $queries;

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
    public function conferencesAllFormatted($from_time,$until_time) {

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
        $sql = $this->queries['conferences_all_formatted'];
        $sql = sprintf($sql, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>
