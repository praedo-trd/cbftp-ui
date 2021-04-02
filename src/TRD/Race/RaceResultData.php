<?php

namespace TRD\Race;

use TRD\Parser\RuleData;

class RaceResultData
{
    private $data = array();
    private $dataProviderData = array();
    private $dupeData = array();

    public function __construct($initialValues)
    {
        foreach ($initialValues as $k => $v) {
            $this->attachData($k, $v);
        }
    }

    public function attachDataProviderResponse($namespace, \TRD\DataProvider\DataProviderResponse $dataProviderResponse)
    {
        $this->dataProviderData[$namespace] = $dataProviderResponse;
    }

    public function attachData($k, $v)
    {
        $this->data[$k] = $v;
    }

    public function attachDupeSource($source)
    {
        $this->dupeData[] = $source;
    }

    public function toRuleData()
    {
        $ruleData = new RuleData();
        foreach ($this->data as $k => $v) {
            $ruleData->set($k, $v);
        }
        foreach ($this->dataProviderData as $namespace => $response) {
            $ruleData->setData($namespace, $response->getData());
        }

        $dupeGroups = $dupeGroupsInternal = $dupeGroupsNonInternal = [];
        foreach ($this->dupeData as $source) {
            $dupeGroups[] = $source->getGroup();
            if ($source->isInternal()) {
                $dupeGroupsInternal[] = $source->getGroup();
            } else {
                $dupeGroupsNonInternal[] = $source->getGroup();
            }
        }

        $groups = array_unique($dupeGroups);
        $ruleData->set('dupe.groups', $groups);
        $ruleData->set('dupe.groups_total', sizeof($groups));

        $groupsInternal = array_unique($dupeGroupsInternal);
        $ruleData->set('dupe.groups_internal', $groupsInternal);
        $ruleData->set('dupe.groups_internal_total', sizeof($groupsInternal));

        $groupsNonInternal = array_unique($dupeGroupsNonInternal);
        $ruleData->set('dupe.groups_non_internal', $groupsNonInternal);
        $ruleData->set('dupe.groups_non_internal_total', sizeof($groupsNonInternal));

        return $ruleData;
    }

    public function all()
    {
        return $this->flatten();
    }

    public function flatten()
    {
        return $this->toRuleData()->all();
    }

    public function getCleanData()
    {
        $ruleData = new RuleData();
        foreach ($this->dataProviderData as $namespace => $response) {
            $ruleData->setData($namespace, $response->getCleanData());
        }
        return $ruleData;
    }

    public function getImmutableData()
    {
        $ruleData = new RuleData();
        foreach ($this->dataProviderData as $namespace => $response) {
            $ruleData->setData($namespace, $response->getImmutableData());
        }
        return $ruleData;
    }
}
