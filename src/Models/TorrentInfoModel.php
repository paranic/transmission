<?php

namespace inSpot\Transmission\Models;

class TorrentInfoModel
{

    private $id;
    private $name;
    private $hash;
    private $magnet;
    private $state;
    private $location;
    private $percent_done;
    private $eta;
    private $download_speed;
    private $upload_speed;
    private $have;
    private $availability;
    private $total_size;
    private $downloaded;
    private $uploaded;
    private $ratio;
    private $corrupt_dl;
    private $error;
    private $peers;
    private $date_added;
    private $date_started;
    private $seeding_time;
    private $date_created;
    private $public_torrent;
    private $creator;
    private $piece_count;
    private $piece_size;
    private $download_limit;
    private $upload_limit;
    private $ratio_limit;
    private $honors_session_limits;
    private $peer_limit;
    private $bandwidth_priority;

    public function __construct()
    {
    }

    public function setProperties($properties)
    {
        foreach ($properties as $property => $value) {
            $this->{$property} = $value;
        }
    }
}
