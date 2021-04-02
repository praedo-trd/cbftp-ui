<?php

namespace TRD\Handler;

use TRD\DataProvider\IMDBDataProvider;
use TRD\DataProvider\TVMazeDataProvider;
use TRD\Handler\Handler;
use TRD\Processor\ProcessorResponse;

class SimulationHandler extends Handler
{
    public function handle(ProcessorResponse $response)
    {
        $sites = $this->container['models']['sites'];
        $siteList = array();
        foreach ($sites->getData() as $siteName => $info) {
            $siteList[] = $siteName;
        }

        $race = new \TRD\Race\Race($this->container);
        $race->addSites($siteList);
        $result = $race->race($response->data['tag'], $response->data['rlsname']);

        $messages = array();
        $messages[] = 'Results';
        $messages[] = '-----------------------';

        if (sizeof($result->catastrophes)) {
            $messages[] = 'Catastrophes: ' . implode(',', $result->catastrophes);
        }

        if (sizeof($result->validSites) > 0) {
            $messages[] = 'Valid sites: ' . implode(',', $result->validSites);
        }

        $invalidSites = array();
        if (sizeof($result->invalidSites) > 0) {
            foreach ($result->invalidSites as $invalid) {
                if (!in_array($invalid['site'], $result->validSites)) {
                    $invalidSites[$invalid['site']] = $invalid['site'];
                }
            }
        }

        if (sizeof($invalidSites) > 0) {
            $messages[] = 'Invalid sites: ' . implode(',', array_unique(array_keys($invalidSites)));
        }

        $exceptionSites = array();
        if (sizeof($result->exceptions) > 0) {
            foreach ($result->exceptions as $exception) {
                $exceptionSites[$exception['site']] = $exception['site'];
            }
        }

        if (sizeof($result->chain) > 0) {
            $messages[] = 'Chain: ' . implode(',', $result->chain);
        }

        if (sizeof($exceptionSites) > 0) {
            $messages[] = 'Exceptions: ' . implode(',', array_unique(array_keys($exceptionSites)));
            $messages[] = '-----------------------';
        }

        $response->response = implode('|', $messages);

        $command = new \TRD\Processor\ProcessorResponseCommand('SIMULATIONRESULT');
        $response->setCommand($command);

        return $response;
    }
}
