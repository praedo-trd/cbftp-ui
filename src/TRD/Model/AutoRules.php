<?php

namespace TRD\Model;

class AutoRules extends \TRD\Model\Model
{
    protected $name = 'autorules';
    protected $refreshInterval = 60;

    public function addRule($newRule)
    {
        $newRule = trim($newRule);

        $found = false;
        foreach ($this->data as $rule) {
            if ($newRule === $rule) {
                $found = true;
            }
        }

        if (!$found) {
            $this->data->rules[] = $newRule;
        }
    }

    public function removeRule($rule)
    {
        foreach ($this->data->rules as $k => $r) {
            if ($r === $rule) {
                unset($this->data->rules["$k"]);
            }
        }
    }

    public function evaluate($chain, $data)
    {
        $parser = new \TRD\Parser\Rules();

        $rulesData = $this->getData();
        $rules = $rulesData->rules;

        if ($rules !== null) {
            $newData = clone $data;
            $newData->attachData('chain', $chain);
            $parser->addData($newData->toRuleData());
            $rules = $parser->sortRules($rules);
            foreach ($rules as $rule) {
                $rule = trim($rule);
                if (!empty($rule)) {
                    try {
                        if ($parser->parseRule($rule) instanceof \TRD\Parser\RuleResponse\IsTrue) {
                            return $rule;
                        }
                    } catch (\Exception $e) {
                    }
                }
            }
        }
        return false;
    }
}
