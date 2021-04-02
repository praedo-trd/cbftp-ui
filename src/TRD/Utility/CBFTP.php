<?php

namespace TRD\Utility;

use GuzzleHttp\Client;

class CBFTP
{
    private $host = null;
    private $port = null;
    private $password = null;
  
    public function __construct($host, $port, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
    }
  
    public function sendCommand($command, $args = [])
    {
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $message = sprintf('%s %s %s', $this->password, $command, implode(' ', $args));
        socket_sendto($sock, $message, strlen($message), 0, $this->host, $this->port);
        socket_close($sock);
    }
    
    public function prepare($tag, $rlsname, $chain, $downloadOnly = null)
    {
        $args = [$tag, $rlsname, $this->joinChain($chain)];
        if ($downloadOnly !== null) {
            $args[] = $this->joinChain($downloadOnly);
        }
        $this->sendCommand('prepare', $args);
    }
    
    public function race($tag, $rlsname, $chain, $downloadOnly = null)
    {
        $args = [$tag, $rlsname, $this->joinChain($chain)];
        if ($downloadOnly !== null) {
            $args[] = $this->joinChain($downloadOnly);
        }
        $this->sendCommand('race', $args);
    }
    
    public function rawCapture($command, $sites = [], $path = '/')
    {
        $client = new Client(['base_uri' => 'https://'.$this->host.':'.$this->port]);
        try {
            $json = [
              "command" => $command,
              "sites_all" => empty($sites),       // run on all sites
              "path" => $path,    // the path to cwd to before running command
              "timeout" => 10,           // max wait before failing
              "async" => false
            ];
            
            if (!empty($sites)) {
                $json['sites'] = $sites;
            }
          
            $response = $client->request('POST', '/raw', [
              'json' => $json,
              'verify' => false,
               'auth' => ['', $this->password]
           ]);
            
            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), true);
            }
        } catch (\Exception $e) {
            return [];
        }
        return [];
    }
    
    public static function siteHasSection($siteSections, $section)
    {
        foreach ($siteSections as $s) {
            if ($s['name'] === $section) {
                return true;
            }
        }
        return false;
    }
    
    public function getSiteInfo($site)
    {
        $client = new Client(['base_uri' => 'https://'.$this->host.':'.$this->port]);
        try {
            $response = $client->request('GET', "/sites/$site", [
              'verify' => false,
               'auth' => ['', $this->password]
           ]);
            
            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), true);
            }
        } catch (\Exception $e) {
            return [];
        }
        return [];
    }
    
    private function joinChain($chain)
    {
        return implode(',', $chain);
    }
    
    public static function parseStatToCredits($statResponse)
    {
        if (preg_match('/(?:Credits|C|Creds):\s{0,}([\d\.]+)\s{0,}(MB|MiB|GB|GiB|TB|TiB)/ism', $statResponse, $creditsMatches)) {
            $multiplier = 1;
            switch ($creditsMatches[2]) {
              case 'MB':
                $multiplier = 1.024;
                break;
              case 'GB':
                $multiplier = 1.024 * 1024;
                break;
              case 'GiB':
                $multiplier = 1024;
                break;
              case 'TB':
                $multiplier = 1.024 * 1024 * 1024;
              break;
              case 'TiB':
                $multiplier = 1024 * 1024;
              break;
            }
            return (int)$creditsMatches[1] * $multiplier;
        }
        return 0;
    }
}
