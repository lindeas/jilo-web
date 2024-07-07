<?php

class Participant {
    private $db;
    private $queries;

    public $jitsi_component;
    public $start;
    public $end;
    public $participant_id;
    public $conference_name;
    public $participants;
    public $name_count;
    public $conference_host;

    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->queries = include('queries.php');
    }


    // search/list specific participant ID
    public function conferenceByParticipantId($participant_id, $from_time, $until_time) {

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
        $sql = $this->queries['conference_by_participant_id'];
        $sql = sprintf($sql, $participant_id, $from_time, $until_time, $participant_id, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // search/list specific participant name (stats_id)
    public function conferenceByParticipantName($participant_name, $from_time, $until_time) {

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
        $sql = $this->queries['participant_by_stats_id'];
        $sql = sprintf($sql, $participant_name, $from_time, $until_time, $participant_name, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // search/list specific participant IP
    public function conferenceByParticipantIP($participant_ip, $from_time, $until_time) {

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
        $sql = $this->queries['participant_by_ip'];
        $sql = sprintf($sql, $participant_ip, $from_time, $until_time, $participant_ip, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // list of all conferences
    public function participantsAll($from_time, $until_time) {

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
        $sql = $this->queries['participants_all'];
        $sql = sprintf($sql, $from_time, $until_time);

        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


}

?>
