<?php

namespace inSpot\Transmission;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use inSpot\Transmission\Models\TorrentFileInfoModel;

class TransmissionShow
{

    public function __construct()
    {
    }

    /**
     * Get torrent hash from .torrent file.
     *
     * Execute transmission-show and parse output to get torrent hash.
     *
     * @param string $torrent_file Referrenced .torrent file.
     *
     * @return string
     */
    public function fromFile($torrent_file)
    {
        $command = 'transmission-show ' . escapeshellarg($torrent_file);

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        } else {
            $torrent_file_info_model_parameters = [];

            $torrent_info = explode(PHP_EOL, $process->getOutput());

            foreach ($torrent_info as $line) {
                $line = trim($line);

                if (strpos($line, 'Name:') !== false) {
                    $value = explode(':', $line)[1];
                    $torrent_file_info_model_params['name'] = trim($value);
                }

                if (strpos($line, 'Hash:') !== false) {
                    $value = explode(':', $line)[1];
                    $torrent_file_info_model_params['hash'] = trim($value);
                }

                if (strpos($line, 'Created by:') !== false) {
                    $value = explode(':', $line)[1];
                    $torrent_file_info_model_params['created_by'] = trim($value);
                }

                if (strpos($line, 'Created on:') !== false) {
                    $value = explode('Created on:', $line)[1];
                    $torrent_file_info_model_params['created_on'] = trim($value);
                }

                if (strpos($line, 'Comment:') !== false) {
                    $value = explode(':', $line)[1];
                    $torrent_file_info_model_params['comment'] = trim($value);
                }

                if (strpos($line, 'Piece Count:') !== false) {
                    $value = explode(':', $line)[1];
                    $torrent_file_info_model_params['piece_count'] = trim($value);
                }

                if (strpos($line, 'Piece Size:') !== false) {
                    $value = explode(':', $line)[1];
                    $torrent_file_info_model_params['piece_size'] = trim($value);
                }

                if (strpos($line, 'Total Size:') !== false) {
                    $value = explode(':', $line)[1];
                    $torrent_file_info_model_params['total_size'] = trim($value);
                }

                if (strpos($line, 'Privacy:') !== false) {
                    $value = explode(':', $line)[1];
                    $torrent_file_info_model_params['privacy'] = trim($value);
                }
            }
            $torrent_file_info_model = new TorrentFileInfoModel($torrent_file_info_model_params);

            return $torrent_file_info_model;
        }
    }

    /**
     * Get torrent hash from torrent file data
     *
     * Create .torrent file from $file_data and calls getHashFromFile to get torrent hash.
     *
     * @param string $torrent_file_data Referrenced .torrent file.
     *
     * @return string
     */
    public function fromStream($torrent_file_data)
    {
        if ($torrent_file_data) {
            // Create temporary file
            $temp_torrent_file = tempnam('/tmp', 'torrent_');

            // Store torrent file's data in it
            file_put_contents($temp_torrent_file, $torrent_file_data);

            // Get hash from temporary file
            $torrent_file_info_model = $this->fromFile($temp_torrent_file);

            unlink($temp_torrent_file);

            return $torrent_file_info_model;
        }
    }
}
