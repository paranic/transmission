<?php

namespace inSpot\Transmission\Models;

class TorrentFileInfoModel
{

    private $name;
    private $hash;
    private $created_by;
    private $created_on;
    private $comment;
    private $piece_count;
    private $piece_size;
    private $total_size;
    private $privacy;

    public function __construct($properties)
    {
        foreach ($properties as $property => $value) {
            $this->{$property} = $value;
        }
    }
}
