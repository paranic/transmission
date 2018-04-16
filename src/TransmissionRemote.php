<?php

namespace inSpot\Transmission;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use inSpot\Transmission\Models\TorrentInfoModel;

class TransmissionRemote
{

    public function __construct()
    {
    }

    /**
     * Add torrent to transmission.
     *
     * Add a new torrent to transmission and starts it imidiately.
     *
     * @param string $torrent_file The full path of the .torrent file.
     *
     * @return void
     */
    public function add($torrent_file)
    {
        if (!$torrent_file) {
            echo 'No torrent file given.' . PHP_EOL;

            return;
        }

        $command = 'transmission-remote -a ' . escapeshellarg($torrent_file);

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Start a torrent.
     *
     * Start a torrent that is already added to transmission, by given torrent id or torrent hash.
     *
     * @param int|string $torrent_identifier Torrent id or hash.
     *
     * @return void
     */
    public function start($torrent_identifier)
    {
        // Starts downloading given torrent hash
        $command = 'transmission-remote -t ' . $torrent_identifier . ' -s 2>&1';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
    * Stop a torrent.
    *
    * Stop a torrent that is already added to transmission, by given torrent id or torrent hash.
    *
    * @param int|string $torrent_identifier Torrent id or hash.
    *
    * @return void
    */
    public function stop($torrent_identifier)
    {
        // Stops downloading given torrent hash/file(?)
        $command = 'transmission-remote -t ' . $torrent_identifier . ' -S 2>&1';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
    * Reannounce a torrent.
    *
    * Reannounce torrent to tracker.
    *
    * @param string $torrent_hash Torrent file's hash.
    *
    * @return void
    */
    public function reannounce($torrent_identifier)
    {
        $command =  'transmission-remote -t ' . $torrent_identifier . ' --reannounce';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
    * Remove a torrent.
    *
    * Remove a torrent from transmission daemon, by given torrent id or torrent hash.
    *
    * @param int|string $torrent_identifier Torrent id or hash.
    *
    * @return void
    */
    public function remove($torrent_identifier)
    {
        $command = 'transmission-remote -t ' . $torrent_identifier . ' -r';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
    * Remove a torrent and delete data.
    *
    * Remove a torrent from transmission daemon, and delete data, by given torrent id or torrent hash.
    *
    * @param int|string $torrent_identifier Torrent id or hash.
    *
    * @return void
    */
    public function removeAndDelete($torrent_identifier)
    {
        $command = 'transmission-remote -t ' . $torrent_identifier . ' -rad';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
    * Verify a torrent.
    *
    * Verify a torrent, by given torrent id or torrent hash.
    *
    * @param int|string $torrent_identifier Torrent id or hash.
    *
    * @return void
    */
    public function verify($torrent_identifier)
    {
        $command = 'transmission-remote -t ' . $torrent_identifier . ' -v 2>&1';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
    * Set download and upload limits.
    *
    * Set download and upload limit for all torrents.
    * @todo Make a seperate function to set limits for each torrent.
    *
    * @param int|null $download_limit Download limit in KB or null for unlimited.
    * @param int|null $upload_limit Upload limit in KB or null for unlimited.
    *
    * @return void
    */
    public function setTransferLimits($torrent_identifier, $download_limit = null, $upload_limit = null)
    {
        $command = 'transmission-remote -t ' . $torrent_identifier;

        $command .= ((int)$download_limit > 0) ? ' --downlimit ' . (int)$download_limit : ' --no-downlimit';
        $command .= ((int)$upload_limit > 0) ? ' --uplimit ' . (int)$upload_limit : ' --no-uplimit';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
    * Return torrent(s) info.
    *
    * Return torrent(s) detailed information, by given torrent id or torrent hash.
    *
    * @return \inSpot\Transmission\Models\TorrentInfoModel[] Array of TorrentInfoModel.
    */
    public function info($torrent_identifier)
    {
        $command = 'transmission-remote -t ' . $torrent_identifier . ' -i';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        } else {
            $torrent_info_models_parameters = [];

            $torrent_info = explode(PHP_EOL, $process->getOutput());

            foreach ($torrent_info as $line) {
                $line = trim($line);

                if (strpos($line, 'NAME') !==  false) {
                    // Create a new model object when 'NAME' is found
                    $torrent_info_model_parameters = new TorrentInfoModel();
                    array_push($torrent_info_models_parameters, $torrent_info_model_parameters);
                }

                if (strpos($line, 'Id:') !==  false) {
                    $value = explode(' ', $line)[1];
                    $torrent_info_model_parameters->setProperties(['id' => trim($value)]);
                }

                if (strpos($line, 'Name:') !==  false) {
                    $value = explode('Name:', $line)[1];
                    $torrent_info_model_parameters->setProperties(['name' => trim($value)]);
                }

                if (strpos($line, 'Hash:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['hash' => trim($value)]);
                }

                if (strpos($line, 'Magnet:') !==  false) {
                    $value = explode('Magnet:', $line)[1];
                    $torrent_info_model_parameters->setProperties(['magnet' => trim($value)]);
                }

                if (strpos($line, 'State:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['state' => trim($value)]);
                }

                if (strpos($line, 'Location:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['location' => trim($value)]);
                }

                if (strpos($line, 'Percent Done:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['percent_done' => trim($value)]);
                }

                if (strpos($line, 'ETA:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['eta' => trim($value)]);
                }

                if (strpos($line, 'Download Speed:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['download_speed' => trim($value)]);
                }

                if (strpos($line, 'Upload Speed:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['upload_speed' => trim($value)]);
                }

                if (strpos($line, 'Have:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['have' => trim($value)]);
                }

                if (strpos($line, 'Availability:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['availability' => trim($value)]);
                }

                if (strpos($line, 'Total size:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['total_size' => trim($value)]);
                }

                if (strpos($line, 'Downloaded:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['downloaded' => trim($value)]);
                }

                if (strpos($line, 'Uploaded:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['uploaded' => trim($value)]);
                }

                if (strpos($line, 'Ratio:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['ratio' => trim($value)]);
                }

                if (strpos($line, 'Corrupt DL:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['corrupt_dl' => trim($value)]);
                }

                if (strpos($line, 'Error:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['error' => trim($value)]);
                }

                if (strpos($line, 'Peers:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['peers' => trim($value)]);
                }

                if (strpos($line, 'Date added:') !==  false) {
                    $value = explode('Date added:', $line)[1];
                    $torrent_info_model_parameters->setProperties(['date_added' => trim($value)]);
                }

                if (strpos($line, 'Date started:') !==  false) {
                    $value = explode('Date started:', $line)[1];
                    $torrent_info_model_parameters->setProperties(['date_started' => trim($value)]);
                }

                if (strpos($line, 'Seeding Time:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['seeding_time' => trim($value)]);
                }

                if (strpos($line, 'Date created:') !==  false) {
                    $value = explode('Date created:', $line)[1];
                    $torrent_info_model_parameters->setProperties(['date_created' => trim($value)]);
                }

                if (strpos($line, 'Public torrent:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['public_torrent' => trim($value)]);
                }

                if (strpos($line, 'Creator:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['creator' => trim($value)]);
                }

                if (strpos($line, 'Piece Count:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['piece_count' => trim($value)]);
                }

                if (strpos($line, 'Piece Size:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['piece_size' => trim($value)]);
                }

                if (strpos($line, 'Download Limit:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['download_limit' => trim($value)]);
                }

                if (strpos($line, 'Upload Limit:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['upload_limit' => trim($value)]);
                }

                if (strpos($line, 'Ratio Limit:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['ratio_limit' => trim($value)]);
                }

                if (strpos($line, 'Honors Session Limits:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['honors_session_limits' => trim($value)]);
                }

                if (strpos($line, 'Peer limit:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['peer_limit' => trim($value)]);
                }

                if (strpos($line, 'Bandwidth Priority:') !==  false) {
                    $value = explode(':', $line)[1];
                    $torrent_info_model_parameters->setProperties(['bandwidth_priority' => trim($value)]);
                }
            }

            return $torrent_info_models_parameters;
        }
    }
}
