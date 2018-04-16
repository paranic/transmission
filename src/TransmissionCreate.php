<?php

namespace inSpot\Transmission;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TransmissionCreate
{
    // Allow this torrent to only be used with the specified tracker(s)
    private $private;
    // Save the generated .torrent to this filename
    private $outfile;
    // Set how many KiB each piece should be, overriding the preferred default
    private $piecesize;
    // Add a comment
    private $comment;
    // Add a tracker's announce URL
    private $tracker;
    // Source folder of witch torrent will be created
    private $source_folder;

    public function __construct($properties)
    {
        foreach ($properties as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /**
     * Create a torrent file.
     *
     * Create a .torrent file inside a destination folder and return folder's path.
     *
     * @return string Torrent file path.
     */

    public function createTorrentFile()
    {
        if (!$this->tracker) {
            echo 'Tracker is null.' . PHP_EOL;

            return;
        }


        if (!$this->source_folder) {
            echo 'Source folder is null.' . PHP_EOL;

            return;
        }

        if (!file_exists($this->source_folder)) {
            echo 'Source folder not exists.' . PHP_EOL;

            return;
        }

        if (!$this->outfile) {
            // No dir given, create a temporary one and add timestap to torrent's name
            $date = new \DateTime();
            $this->outfile = tempnam('/tmp', 'torrent_' . $date->getTimestamp());
        }

        if (!is_writable(dirname($this->outfile))) {
            echo 'Output file is not writable.' . PHP_EOL;

            return;
        }

        $command = 'transmission-create';

        if ($this->private) {
            $command .= ' -p ';
        }

        if ($this->outfile) {
            $command .= ' -o ' . escapeshellarg($this->outfile);
        }

        if ($this->piecesize) {
            $command .= ' -s ' . (int)$this->piecesize;
        }

        if ($this->comment) {
            $command .= ' -c "' . $this->comment . '"';
        }

        if ($this->tracker) {
            $command .= ' -t ' . $this->tracker;
        }

        $command .= ' ' . escapeshellarg($this->source_folder);

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this->outfile;
    }
}
