<?php

$config['rss_stream'] = array(
    'RssFeed#1' => array(
        'link' => 'http://rssFeed1.com/rss',
        'depth' => 2,
        'children' => array('channel','item'),
        'field' => array('title','link')
    ),
);
$config['watch_dir'] = '/tank/Torrents/';
$config['log_file'] = '/home/administrator/RssReader/log.txt';
$config['download_dir'] = '/tank/Series/';
$config['downloaded_items_file'] = '/home/administrator/RssReader/downloaded.txt';
$config['look_for'] = array(
    'Ubuntu Hacks' => array(
        'season' => 2,
        'episode' => 4,
        'download_dir' => '/tank/Series/Ubuntu.hacks/'
    ),
    'FreeBSD tips' => array(
        'download_dir' => '/tank/Series/Arrow/'
    )
);
$config['client_type'] = 'transmission-remote';
$config['client_path'] = '/usr/local/bin/transmission-remote';
$config['client_username'] = 'transmission';
$config['client_password'] = 'password';
