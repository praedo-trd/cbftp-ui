<?php

namespace TRD\Model;

class Sites extends \TRD\Model\Model
{
    protected $name = 'sites';
    protected $refreshInterval = 60;

    public function getSite($siteName)
    {
        return $this->data->$siteName;
    }

    public function replaceSite($siteName, $data)
    {
        $this->data->$siteName = $data;
    }
    
    public function getSitesArray($returnDisabled = false)
    {
        $sites = [];
        foreach ($this->data as $site => $info) {
            if ($info->enabled or $returnDisabled === true) {
                $sites[] = $site;
            }
        }
        return $sites;
    }
    
    public function isRing($siteName)
    {
        $site = $this->getSite($siteName);
        foreach ($site->sections as $section) {
            if (!empty($section->bnc)) {
                return true;
            }
        }
        return false;
    }

    public function getAffils($siteName)
    {
        return $this->data->$siteName->affils;
    }

    protected function load($path, $skipCache = false)
    {

      // try and get the data from the cache first if possible
        if ($this->cache !== null && !$skipCache) {
            $model = $this->cache->get('trd:models:' . $this->name);
            if (!empty($model)) {
                $this->data = json_decode($model);
                return;
            }
        }

        // pull it from the file system
        $dir = new \DirectoryIterator($_ENV['DATA_PATH'] . '/sites/');
        $data = new \stdClass();
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $siteName = $fileinfo->getBaseName('.json');
                $data->$siteName = json_decode(file_get_contents($fileinfo->getRealPath()));
            }
        }
        $this->data = $data;

        if ($this->cache !== null) {
            $this->cache->set('trd:models:' . $this->name, json_encode($this->data));
        }
    }

    public function save()
    {
        $this->needsRefresh = true;
        foreach ($this->data as $siteName => $siteInfo) {
            file_put_contents($_ENV['DATA_PATH'] . '/sites/' . $siteName . '.json', json_encode($siteInfo, JSON_PRETTY_PRINT));
        }
        if ($this->cache !== null) {
            $this->cache->set('trd:models:' . $this->name, json_encode($this->data));
        }
    }

    public function findValidTags($siteName, $section, $rlsname)
    {
        $site = $this->getSite($siteName);

        $validTags = array();
        foreach ($site->sections as $ss) {

          // need to check isset here as there is a race condition
            // where newly found sections will not be present in the $site object
            // yet for some reason
            if (isset($ss->name) and $ss->name == $section) {
                if (isset($ss->tags)) {
                    foreach ($ss->tags as $tag) {
                        if (preg_match($tag->trigger, $rlsname)) {
                            $validTags[] = $tag->tag;
                        }
                    }
                }
            }
        }
        return $validTags;
    }

    public function addSite($siteName)
    {
        if (!property_exists($this->data, $siteName)) {
            $this->data->{$siteName} = array(
                'enabled' => false
                ,'irc' => array(
                    'channel' => '',
                    'channel_key' => '',
                    'bot' => '',
                    'strings' => array(
                        'newstring' => ''
                        ,'newstring-rls' => ''
                        ,'newstring-section' => ''
                        ,'newstring-isregex' => false
                        ,'endstring' => ''
                        ,'endstring-rls' => ''
                        ,'endstring-section' => ''
                        ,'endstring-isregex' => false
                        ,'prestring' => ''
                        ,'prestring-rls' => ''
                        ,'prestring-section' => ''
                        ,'prestring-isregex' => false
                    )
                )
                ,'sections' => array()
                , 'affils' => array()
                , 'banned_groups' => array()

            );
            $this->save();

            return true;
        }

        return false;
    }

    public function addAffil($siteName, $newAffil)
    {
        // loop through existing and exit if we match
        $currentAffils = $this->data->{$siteName}->affils;
        foreach ($currentAffils as $affil) {
            if ($affil == strtoupper($newAffil)) {
                return false;
            }
        }

        // otherwise it's new!
        $this->data->{$siteName}->affils[] = strtoupper($newAffil);
        $this->save();
        return true;
    }

    public function addSection($siteName, $sectionName)
    {
        $sectionName = trim($sectionName);
      
        // check site exists
        if (!property_exists($this->data, $siteName)) {
            return false;
        }

        if (empty($sectionName)) {
            return false;
        }

        // skip stupid sections
        if (preg_match('/(Req|Backfill|freezone|arch|upload|default|temp|IRC)/i', $sectionName)) {
            return false;
        }

        // skip bad charactered sections
        if (preg_match('/[^a-z0-9-_]+/i', $sectionName)) {
            return false;
        }

        $currentSections = $this->data->{$siteName}->sections;

        foreach ($currentSections as $section) {
            if (strtolower($section->name) == strtolower($sectionName)) {
                return false;
            }
        }
        
        $this->data->{$siteName}->sections[] = array(
            'name' => $sectionName,
            'pretime' => 5
            ,'bnc' => null
            ,'tags' => array()
            ,'skiplists' => array()
            ,'rules' => array()
            ,'dupeRules' => array('source.firstWins' => false, 'source.priority' => '')
        );
        $this->save();
        return true;
    }
    
    public function findAllBNCs($siteName)
    {
        $site = $this->getSite($siteName);
      
        $bncs = [];
        foreach ($site->sections as $section) {
            if (isset($section->bnc) and !empty($section->bnc) and !in_array($section->bnc, $bncs)) {
                $bncs[] = $section->bnc;
            }
        }
      
        if (sizeof($bncs) === 0) {
            $bncs[] = $siteName;
        }
    
        return $bncs;
    }
    
    public function applySkiplistToEverySection($name)
    {
        $c = 0;
        foreach ($this->data as $siteName => $siteInfo) {
            foreach ($siteInfo->sections as $section) {
                if (!in_array($name, $section->skiplists)) {
                    $section->skiplists[] = $name;
                    $c++;
                }
            }
        }
        $this->save();
        return $c;
    }

    public function repairSite($siteName)
    {
        $repaired = false;
        if (!isset($this->data->$siteName->banned_groups)) {
            $this->data->$siteName->banned_groups = [];
            $repaired = true;
        }
        
        if (!isset($this->data->$siteName->credits) or !is_object($this->data->$siteName->credits)) {
            $this->data->$siteName->credits = new \stdClass();
            $repaired = true;
        }

        $this->data->$siteName->affils = array_map('strtoupper', $this->data->$siteName->affils);
        $this->data->$siteName->banned_groups = array_map('strtoupper', $this->data->$siteName->banned_groups);
        $this->save();

        return $repaired;
    }
    
    public function setCredits($siteName, $bnc, $credits)
    {
        $this->data->$siteName->credits->$bnc = $credits;
        $this->save();
    }

    public function repairSection($siteName, $sectionName)
    {
        $currentSections = $this->data->{$siteName}->sections;

        $repaired = false;
        foreach ($currentSections as $section) {
            if ($section->name == $sectionName) {
                if (!property_exists($section, 'pretime')) {
                    $repaired = true;
                    $section->pretime = 5;
                }

                if (!property_exists($section, 'bnc')) {
                    $repaired = true;
                    $section->bnc = null;
                }

                if (!property_exists($section, 'tags')) {
                    $repaired = true;
                    $section->tags = array();
                }

                if (!property_exists($section, 'dupeRules') or $section->dupeRules === null) {
                    $repaired = true;
                    if ($section->dupeRules === null) {
                        $section->dupeRules = array(
                        'source.firstWins' => false,
                        'source.priority' => ''
                      );
                    }
                }

                if (!property_exists($section, 'skiplists')) {
                    $repaired = true;
                    $section->skiplists = array();
                }

                if (!property_exists($section, 'rules')) {
                    $repaired = true;
                    $section->rules = array();
                }

                $this->save();

                break;
            }
        }
        return $repaired;
    }
}
