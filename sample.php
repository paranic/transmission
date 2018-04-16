<?php

require_once __DIR__ . '/vendor/autoload.php';

use inSpot\Transmission\TransmissionDaemon;
use inSpot\Transmission\TransmissionRemote;
use inSpot\Transmission\TransmissionCreate;
use inSpot\Transmission\TransmissionShow;

try {
    $transmission_daemon = new TransmissionDaemon('sysop');
    $transmission_daemon->setOptionPortmap();
    // $transmission_daemon->setOptionBlocklist();
    $transmission_daemon->setOptionDownloadDir('/storage/torrent.data');

    $transmission_daemon->stop();

    /* Daemon, start() */
    echo 'Testing TransmissionDaemon->start()' . PHP_EOL;
    $transmission_daemon->start();
    sleep(2);
    echo 'TransmissionDaemon->start() was successfull' . PHP_EOL;
    sleep(2);

     /* Daemon, getProcessInfo() */
    echo 'Testing TransmissionDaemon->getProcessInfo()' . PHP_EOL;
    sleep(2);
    var_dump($transmission_daemon->getProcessInfo());
    sleep(2);
    echo 'TransmissionDaemon->getProcessInfo() was successfull' . PHP_EOL;
    sleep(2);

     /* Daemon, createTorrentFile() */
    echo 'Testing TransmissionCreate->createTorrentFile()' . PHP_EOL;
    $transmission_create = new TransmissionCreate([
        'private' => '-p',
        'outfile' => '/projects/torrents/sample_torrents/file.torrent',
        'piecesize' => '1024',
        'comment' => 'test_comm',
        'tracker' => 'http://182.176.139.129:6969/announce',
        'source_folder' => '/projects/torrents/torrent_data/fifa.rar'
    ]);
    $torrent_file = $transmission_create->createTorrentFile();
    var_dump($torrent_file);
    sleep(2);
    echo 'TransmissionCreate->createTorrentFile() was successfull' . PHP_EOL;
    sleep(2);

    /* Show, fromFile() */
    echo 'Testing TransmissionShow->fromFile()' . PHP_EOL;
    $transmission_show = new TransmissionShow();
    var_dump($transmission_show->fromFile($torrent_file));
    sleep(2);
    echo 'TransmissionShow->fromFile() was successfull' . PHP_EOL;
    sleep(2);

    /* Show, fromStream() */
    echo 'Testing TransmissionShow->fromStream()' . PHP_EOL;
    sleep(2);
    var_dump($transmission_show->fromStream(file_get_contents($torrent_file)));
    sleep(2);
    echo 'TransmissionShow->fromStream() was successfull' . PHP_EOL;
    sleep(2);

    $transmission_remote = new TransmissionRemote();
    /* Remote, add() */
    echo 'Testing TransmissionRemote->add()' . PHP_EOL;
    sleep(2);
    $transmission_remote->add('/projects/torrents/sample_torrents/zoogle.torrent');
    sleep(2);
    echo 'TransmissionRemote->add() was successfull' . PHP_EOL;
    sleep(2);

    /* Remote verify */
    echo 'Testing TransmissionRemote->verify()' . PHP_EOL;
    sleep(2);
    $transmission_remote->verify('9ce2ab615df97d43fd748aecdfa32e6ff5f92c62');
    sleep(2);
    echo 'TransmissionRemote->verify() was successfull' . PHP_EOL;
    sleep(2);

    /* Remote, stop() */
    echo 'Testing TransmissionRemote->stop()' . PHP_EOL;
    sleep(2);
    $transmission_remote->stop('9ce2ab615df97d43fd748aecdfa32e6ff5f92c62');
    sleep(2);
    echo 'TransmissionRemote->stop() was successfull' . PHP_EOL;
    sleep(2);

    /* Remote, start() */
    echo 'Testing TransmissionRemote->start()' . PHP_EOL;
    sleep(2);
    $transmission_remote->start('9ce2ab615df97d43fd748aecdfa32e6ff5f92c62');
    sleep(2);
    echo 'TransmissionRemote->start() was successfull' . PHP_EOL;
    sleep(2);

    /* Remote, reannounce() */
    echo 'Testing TransmissionRemote->reannounce()' . PHP_EOL;
    sleep(2);
    $transmission_remote->reannounce('9ce2ab615df97d43fd748aecdfa32e6ff5f92c62');
    sleep(2);
    echo 'TransmissionRemote->reannounce() was successfull' . PHP_EOL;
    sleep(2);

    /* Remote, setTransferLimits() */
    echo 'Testing TransmissionRemote->setTransferLimits()' . PHP_EOL;
    sleep(2);
    $transmission_remote->setTransferLimits('9ce2ab615df97d43fd748aecdfa32e6ff5f92c62', '32', '32');
    sleep(2);
    echo 'TransmissionRemote->setTransferLimits() was successfull' . PHP_EOL;
    sleep(2);

    /* Remote, info(all) */
    echo 'Testing TransmissionRemote->info()' . PHP_EOL;
    sleep(2);
    var_dump($transmission_remote->info('all'));
    sleep(2);
    echo 'TransmissionRemote->info() was successfull' . PHP_EOL;
    sleep(2);

    /* Remote, info(identifier) */
    echo 'Testing TransmissionRemote->info(identifier)' . PHP_EOL;
    sleep(2);
    var_dump($transmission_remote->info('9ce2ab615df97d43fd748aecdfa32e6ff5f92c62'));
    sleep(2);
    echo 'TransmissionRemote->info(identifier) was successfull' . PHP_EOL;
    sleep(2);

    /* Remote, remove(identifier) */
    echo 'Testing TransmissionRemote->remove()' . PHP_EOL;
    sleep(2);
    $transmission_remote->remove('9ce2ab615df97d43fd748aecdfa32e6ff5f92c62');
    sleep(2);
    echo 'TransmissionRemote->remove() was successfull' . PHP_EOL;
    sleep(2);

    /* Add torrent again to test removeAndDelete() */
    echo 'Adding torrent again' . PHP_EOL;
    sleep(2);
    $transmission_remote->add('/projects/torrents/sample_torrents/zoogle.torrent');
    sleep(2);
    echo 'Torrent add was successfull' . PHP_EOL;
    sleep(2);

    /* Remote, remove and delete() */
    echo 'Testing TransmissionRemote->removeAndDelete()' . PHP_EOL;
    sleep(2);
    $transmission_remote->removeAndDelete('9ce2ab615df97d43fd748aecdfa32e6ff5f92c62');
    sleep(2);
    echo 'TransmissionRemote->removeAndDelete() was successfull' . PHP_EOL;
} catch (Exception $e) {
    var_dump($e->getMessage()) . PHP_EOL;
}
