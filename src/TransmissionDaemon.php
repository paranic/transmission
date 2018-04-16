<?php

namespace inSpot\Transmission;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TransmissionDaemon
{

    private $options = [];
    private $transmission_user;
    private $download_dir;

    public function __construct($transmission_user = null)
    {
        if (!$transmission_user) {
            throw new \Exception('You must set transmission_user first parameter');
        } elseif (!$this->isInstalled()) {
            throw new \Exception('Transmission is not installed');
        } else {
            $this->transmission_user = $transmission_user;
        }
    }

    /**
     * Return currently running transmission deamon instance's info.
     *
     * Find and return all instances of transmission daemon currently running.
     * If there are no instances of transmission daemon running, return NULL.
     *
     * @return array|NULL Array containing user and process id of running instances, or NULL if not running.
     */
    public function getProcessInfo()
    {
        // Check if any transmission instances exist
        $command = ("ps aux | grep 'transmission-daemon' | grep -v grep | awk {'print $1,$2'}");

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        } else {
            $processes_info = [];

            $transmission_instances = explode(PHP_EOL, $process->getOutput());

            foreach ($transmission_instances as $process) {
                // This check is done because $process->getOutput() returns an empty array cell at the end
                if ($process) {
                    $process_info = explode(' ', $process);

                    $single_process_info['user'] = $process_info[0];
                    $single_process_info['pid'] = $process_info[1];

                    array_push($processes_info, $single_process_info);
                }
            }
            return $processes_info;
        }
    }

    /**
     * Start the transmission daemon.
     *
     * Check if any instances of transmission is currently running. If yes, an error message is returned.
     *
     * @return void|string
     */
    public function start()
    {
        // Check if other transmission instances are running
        if ($processes = $this->getProcessInfo()) {
            // Log a message stating which applications are running
            foreach ($processes as $process) {
                echo 'Transmission(' . $process['pid'] . '), started by ' . $process['user'] . ' is running.' . PHP_EOL;
            }
        } else {
            $command_options = '';

            // Loop through every option set and create the command to start the daemon
            foreach ($this->options as $option) {
                $command_options .= $option . ' ';
            }

            $command = 'transmission-daemon ' . $command_options;

            $process = new Process($command);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            } else {
                echo 'daemon started' . PHP_EOL;
            }
        }
    }

    /**
     * Kill all transmission daemon instances currently running.
     *
     * Find and try to kill all instances of transmission daemon currently running using their process id.
     *
     * @return void
     */
    public function stop()
    {
        // Check if there are transmission instances currently running
        if ($this->getProcessInfo()) {
            // Loop through list of processes
            foreach ($this->getProcessInfo() as $process) {
                $process_id = $process['pid'];

                if (posix_kill($process_id, SIGKILL)) {
                    echo 'Killed process with pid ' . $process_id . PHP_EOL;
                } else {
                    echo 'Could not kill process with pid ' . $process_id . PHP_EOL;
                }
            }
        } else {
            echo 'There are no transmission instances currently running ' . PHP_EOL;
        }
    }

    /**
     * Set the portmap option.
     *
     * Set the portmap command line parameter option, used in the start command.
     *
     * @param bool true to use portmap or false not to use portmap.
     *
     * @return void
     */
    public function setOptionPortmap($value = true)
    {
        if ($value == true) {
            array_push($this->options, '--portmap');
        } else {
            array_push($this->options, '--no-portmap');
        }
    }

    /**
     * Set -w option from transmission daemon's option list.
     *
     * Determine where to save downloaded data.
     *
     * @param string Download directory for torrents.
     *
     * @return void
     */
    public function setOptionDownloadDir($download_dir)
    {
        if ($this->setDownloadDir($download_dir)) {
            array_push($this->options, '-w ' . $download_dir);
        }
    }

    private function setDownloadDir($download_dir)
    {
        if (!file_exists($download_dir)) {
            if (!mkdir($download_dir)) {
                echo 'Download directory does not exist and could not be created' . PHP_EOL;

                return false;
            }
        } else {
            if (!is_writable($download_dir)) {
                echo 'Download directory is not writable.' . PHP_EOL;

                return false;
            }
        }

        return true;
    }

    /**
     * Set blocklist option from transmission daemon's option list.
     *
     * Set the blocklist command line parameter option, used in the start command.
     *
     * @return void
     */
    public function setOptionBlocklist($value = true)
    {
        if ($value == true) {
            array_push($this->options, '--blocklist');
        } else {
            array_push($this->options, '--no-blocklist');
        }
    }

    /**
     * Check is transmission daemon is installed.
     *
     * Return true if transmission daemon is installed and false if it isn't.
     *
     * @return bool
     */
    private function isInstalled()
    {
        $command = 'which transmission-daemon';

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($command);
        }

        return true;
    }

    /**
     * Check transmission daemon's version.
     *
     * Return transmission daemon's version if transmission daemon is installed
     * and there are no instances of it currently running.
     *
     * @return string
     */
    public function getVersion()
    {
        if (!$this->isInstalled()) {
            echo 'Transmission_Daemon->getVersion() transmission not installed' . PHP_EOL;
        } elseif (!$this->getProcessInfo()) {
            echo 'Transmission_Daemon->getVersion() transmission not running' . PHP_EOL;
        } else {
            $command = 'transmission-daemon --version 2>&1';

            $process = new Process($command);
            $process->run();

            preg_match('/[0-9]+\..*/', $process->getOutput(), $version);

            return $version[0];
        }
    }
}
