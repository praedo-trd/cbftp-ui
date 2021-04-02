<?php

namespace TRD\DataProvider;

use TRD\Utility\ReleaseName;

class BoxOfficeMojoDataProvider extends \TRD\DataProvider\DataProvider
{
    protected $namespace = 'bom';
    protected $imdbData = array();

    public function getDefaults($existing = array())
    {
        $info = $existing;
        $info['year'] = '';
        $info['screens_us'] = 0;
        $info['screens_uk'] = 0;
        $info['has_screens'] = false;
        $info['limited'] = false;
        $info['wide'] = false;
        return $info;
    }

    public static function getDeprecated()
    {
        return array();
    }

    public function lookup($rlsname, $forceRefresh = false, $imdbData = array())
    {
        if (empty($imdbData)) {
            return new DataProviderResponse(false, []);
        }
        
        $this->imdbData = $imdbData;

        $title = ReleaseName::getName($rlsname);

        // TODO: try and extract year, and even country to help better lookup
        $cacheResponse = parent::lookup($title, $forceRefresh);
        if ($cacheResponse->result !== false) {
            return $cacheResponse;
        }

        $data = $this->lookupById($imdbData['id']);

        if (isset($data['id'])) {
            parent::save($title, $data);
            return new DataProviderResponse(true, $data, $cacheResponse->dataImmutable, $cacheResponse->approved);
        } else {
            $data = $this->getDefaults(array());
            return new DataProviderResponse(false, $data, $cacheResponse->dataImmutable, $cacheResponse->approved);
        }
    }

    public function lookupById($id)
    {
        $info = array();

        $html = $this->load('https://www.boxofficemojo.com/title/' . urlencode($id), true);
        $info = $this->extractData($html, $id);

        return $info;
    }

    private function extractData($html, $id = null)
    {
        // Steps required
        // Navigate to page and go to "Original release"
        // Find the URLs for "Domestic" and "United Kingdom"
        // Extract "Widest release" total for both

        $info = $this->getDefaults(array('id' => $id, 'url' => 'http://www.boxofficemojo.com/title/' . $id));
        preg_match('/\/releasegroup\/([a-z\d]+)/ims', $html, $originalReleaseId);
        if (empty($originalReleaseId)) {
            return;
        }

        // get year
        preg_match('/<h1.*?>(.*?)<\/h1>/ims', $html, $yearMatch);
        if (!empty($yearMatch)) {
            $getYear = strip_tags($yearMatch[1]);
            preg_match('/\((\d{4})\)/ims', $getYear, $actualYearMatch);
            if (!empty($actualYearMatch)) {
                $info['year'] = intval($actualYearMatch[1]);
            }
        }

        $originalReleaseId = $originalReleaseId[1];

        $html = $this->load('https://www.boxofficemojo.com/releasegroup/' . urlencode($originalReleaseId), true);

        // get domestic and uk urls
        preg_match('/value="\/release\/(.*?)\/"[^>]*?>Domestic<\/option>/ims', $html, $domestic);
        preg_match('/value="\/release\/(\S+)\/"[^>]*?>United Kingdom<\/option>/ims', $html, $uk);
        if (!empty($domestic)) {
            $info['screens_us'] = $this->getScreensFromRelease($domestic[1]);
        }
        if (!empty($uk)) {
            $info['screens_uk'] = $this->getScreensFromRelease($uk[1]);
        }


        if (
         ($this->imdbData['country'] == 'USA' and $info['screens_us'] >= 500)
         or
         ($this->imdbData['country'] == 'UK' and $info['screens_uk'] >= 250)
       ) {
            $info['wide'] = true;
        }

        if (
          $info['wide'] === false and (
         ($this->imdbData['country'] == 'USA' and $info['screens_us'] > 0 and $info['screens_us'] < 500)
         or
         ($this->imdbData['country'] == 'UK' and $info['screens_uk'] > 0 and $info['screens_uk'] < 250)
       )) {
            $info['limited'] = true;
        }

        if ($info['screens_us'] === 0 and $info['screens_uk'] === 0) {
            $info['limited'] = false;
        } else {
            $info['has_screens'] = true;
        }

        return $info;
    }

    private function getScreensFromRelease($releaseId)
    {
        $html = $this->load('https://www.boxofficemojo.com/release/' . urlencode($releaseId) . '/weekend/', true);
        preg_match('/Widest\s+Release<\/span><span>([\d,]+)/ism', $html, $matches);
        if (!empty($matches)) {
            return intval(str_replace(',', '', $matches[1]));
        }
        return 0;
    }
}
