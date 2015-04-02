<?php

$config['rss_url'] = array('https://url1.com','https://url1.com');
$config['torrent_dir'] = '/tank/Torrents/';
$config['log_file'] = '/home/administrator/RssReader/log.txt';
$config['download_dir'] = '/tank/Series/';
$config['downloaded_items_file'] = '/home/administrator/RssReader/downloaded.txt';
$config['look_for'] = array(
    'Ubuntu Hacks' => array(
        'season' => 2,
        'dir' => '/tank/Series/Ubuntu.hacks/'
    ),
    'FreeBSD tips' => array(
        'dir' => '/tank/Series/Arrow/'
    )
);
$config['client_type'] = 'transmission-remote';
$config['client_path'] = '/usr/local/bin/transmission-remote';
$config['client_username'] = 'transmission';
$config['client_password'] = 'password';
