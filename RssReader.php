<?php

include('config.php');

class RssReader {
    
    private $config;
    private $lookFor = array();
    private $downloadedItems = array();
    private $toDownload = array();
    
    function __construct($config) {
        $this->config = $config;
        $this->generateLookFor();
        $this->generateDownloadedItems();
    }
    
    function execute() {
        foreach ($this->config['rss_url'] as $rssUrl) {
            try {
                $this->log(sprintf('Looking at feed %s.',$rssUrl));
                $this->parseRssXml($this->getRssStream($rssUrl));
                $this->addTorrents();
            } catch (exception $e) {
                $this->log($e->getMessage());
            }
        }
    }
    
    function generateDownloadedItems() {
        if (file_exists($this->config['downloaded_items_file'])) {
            $this->downloadedItems = unserialize(file_get_contents($this->config['downloaded_items_file']));
            if (!is_array($this->downloadedItems)) {
                $this->downloadedItems = array();
            }
        }
    }
    
    function saveDownloadedItems() {
        file_put_contents($this->config['downloaded_items_file'], serialize($this->downloadedItems));
    }
    
    function generateLookFor() {
        foreach ($this->config['look_for'] as $title => $info) {
            $this->lookFor[strtolower(str_replace(array(' ','.'), '', $title))] = $info;
        }
    }
    
    function getRssStream($rssUrl) {
        if (!$rssData = file_get_contents($rssUrl)) {
            throw new Exception("Could not connect to RSS feed.");
        }
        try {
            return @new SimpleXMLElement($rssData);
        } catch (exception $e) {
            throw new Exception($e->getMessage());
            return false;
        }
    }
    
    function getRssItemInfo($item) {
        $n = array(3);
        if (preg_match("/(.+)S([0-9]+)E([0-9]+).+x264/i",$item->title,$n)) {
            $title = strtolower(str_replace(array(' ','.'), '', $n[1]));
            $season = $n[2];
            $episode = $n[3];
            return array(
                'title' => strtolower(str_replace(array(' ','.'), '', $n[1])),
                'real_title' => sprintf("%s" ,$item->title),
                'season' => sprintf("%d", $n[2]),
                'episode' => sprintf("%d", $n[3]),
                'link' => str_replace(' ', '%20', $item->link)
            );
        }
        return false;
    }
    
    function lookingFor($item) {
        if (!isset($item['type'])) {
            $item['type'] = 'series';
        }
        switch ($item['type']) {
            case 'series':
                if (array_key_exists($item['title'], $this->lookFor)) {
                    $this->log(sprintf(" - Found %s:", $item['real_title']));
                    if ($this->lookFor[$item['title']]['season'] < $item['season']) {
                        $this->log(sprintf(" -- Season not of interest (%d).",
                            $item['real_title'],
                            $this->lookFor[$item['title']]['season']
                        ));
                        return false;
                    }
                    if (isset($this->downloadedItems[$item['title']][$item['season']][$item['episode']])) {
                        $this->log(sprintf(" -- Already downloaded (%s).",
                            date("Y-m-d H:i:s", $this->downloadedItems[$item['title']][$item['season']][$item['episode']])
                        ));
                        return false;
                    }
                    $this->log(sprintf(" -- Adding to download list.", $item['real_title']));
                    return true;
                }
                break;
            default:
                
                break;
        }
    }
    
    function parseRssXml($rssXml) {
        $n = array(3);
        foreach ($rssXml->channel->item as $item) {
            if ($itemInfo = $this->getRssItemInfo($item)) {
                if ($this->lookingFor($itemInfo)) {
                    $this->toDownload[] = $itemInfo;
                }
            }
        }
    }
    
    function addTorrents() {
        $downloaded = false;
        if (!empty($this->toDownload)) {
            foreach ($this->toDownload as $item) {
                switch ($this->config['client_type']) {
                    case 'transmission-remote':
                        $dir = (isset($this->lookFor[$item['title']]['dir'])) ? $this->lookFor[$item['title']]['dir'] : $dir = $this->config['download_dir'];
                        $cmd = sprintf("%s -n %s:%s -a '%s' -w %s",
                            $this->config['client_path'],
                            $this->config['client_username'],
                            $this->config['client_password'],
                            $item['link'],
                            $dir
                        );
                        $out = '1';
                        exec($cmd, $out);
                        $this->log(sprintf("Executing %s (%s)", $cmd, $out[0]));
                        if (strpos($out[0],'success') != false) {
                            $this->downloadedItems[$item['title']][$item['season']][$item['episode']] = time();
                            $downloaded = true;
                        }
                        break;
                    default:
                        /* Watch dir code here */
                        break;
                }
            }
            if ($downloaded) {
                $this->saveDownloadedItems();
            }
            
        }
        else {
            $this->log('Nothing to download.');
        }
    }
    
    function log($msg) {
        printf("%s : %s\n", date('Y-m-d H:i:s'), $msg);
    }
    
}

$rssReader = new RssReader($config);
$rssReader->execute();