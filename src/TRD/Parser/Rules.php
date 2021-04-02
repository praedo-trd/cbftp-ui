<?php

namespace TRD\Parser;

use TRD\Parser\RuleData;

class Rules
{
    const STATEMENT_OPERATORS = '/^(.*?)\s+(\!)?(iswm|containsany|matches|contains|isin|iswm|>=|<=|>|<|==|!=)\s+(.*?)$/';
    const LOGICAL_OPERATORS = '/\s+(AND|OR|and|or)\s+/';
        
    const TOKEN_COMMENT = '#';
    const TOKEN_KEYWORD_ALL = 'ALL';
    const TOKEN_KEYWORD_ALLOW = 'ALLOW';
    const TOKEN_KEYWORD_DROP = 'DROP';
    const TOKEN_KEYWORD_EXCEPT = 'EXCEPT';
    const TOKEN_KEYWORDS = ['ALLOW', 'DROP', 'EXCEPT'];
    const TOKEN_DATA_START = '[';
    const TOKEN_DATA_END = ']';
    
    const TOKEN_LOGIC_FLIP = '!';
        
    const TOKEN_COMPARISON_OPERATOR_GREATER_THAN_OR_EQUAL_TO = '>=';
    const TOKEN_COMPARISON_OPERATOR_LESS_THAN_OR_EQUAL_TO = '<=';
    const TOKEN_COMPARISON_OPERATOR_GREATER_THAN = '>';
    const TOKEN_COMPARISON_OPERATOR_LESS_THAN = '<';
    const TOKEN_COMPARISON_OPERATOR_EQUAL_TO = '==';
    const TOKEN_COMPARISON_OPERATOR_NOT_EQUAL_TO = '!=';
    const TOKEN_COMPARISON_OPERATOR_WILDCARD_MATCH = 'iswm';
    const TOKEN_COMPARISON_OPERATOR_ARRAY_CONTAINS = 'contains';
    const TOKEN_COMPARISON_OPERATOR_ITEM_IS_IN_ARRAY = 'isin';
    const TOKEN_COMPARISON_OPERATOR_ARRAY_CONTAINS_ANY = 'containsany';
    const TOKEN_COMPARISON_OPERATOR_REGEX_MATCH = 'matches';
    
    const VALID_FUNCTION_STATEMENT = '/([a-z]+)\((.*?)\)/';
    const FUNCTIONS_WHITELIST = [
      'empty'
    ];

    private $rules = array();
    private $data = null;

    public function construct()
    {
    }

    public function addData(RuleData $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function parseRule($rule)
    {
        $rule = trim($rule);

        if (substr($rule, 0, 1) === self::TOKEN_COMMENT) {
            return new \TRD\Parser\RuleResponse\IsComment;
        }

        if ($rule === self::TOKEN_KEYWORD_ALL) {
            return new \TRD\Parser\RuleResponse\IsTrue;
        }

        // no keyword or too many
        $logic = $this->multi_explode(self::TOKEN_KEYWORDS, $rule);
        if (sizeof($logic) == 1 or sizeof($logic) > 2) {
            throw new InvalidRule(sprintf('Make sure you use the %s keyword only once', implode(', ', self::TOKEN_KEYWORDS)));
        }

        // keyword not at the end
        $keywordMatch = $this->str_ends(self::TOKEN_KEYWORDS, $rule);
        if ($keywordMatch === false) {
            throw new InvalidRule(sprintf('Make sure every rule ends with %s', implode(', ', self::TOKEN_KEYWORDS)));
        }

        // split by logical operators
        // TODO: support && and ||
        $statements = preg_split(self::LOGICAL_OPERATORS, $logic[0]);
        $statements = $this->trim($statements); // clean up

        $finalExpression = trim($logic[0]);

        foreach ($statements as $statement) {
          
            // split statement up into form X OPERATOR Y
            preg_match(self::STATEMENT_OPERATORS, $statement, $statementParts);
            $statementParts = $this->trim($statementParts);
            
            // also see if it's a valid special statement
            preg_match(self::VALID_FUNCTION_STATEMENT, $statement, $emptyParts);
            
            // function statement
            if (sizeof($emptyParts) === 3) {
                if (!in_array($emptyParts[1], self::FUNCTIONS_WHITELIST)) {
                    throw new InvalidRule(sprintf('Only the following functions are supported: %s', implode(', ', self::FUNCTIONS_WHITELIST)));
                }
              
                $part = $this->evaluateStatementPart($emptyParts[2], false);
                $finalExpression = $this->transformExpression(
                    $statement,
                    $part === '' || $part === null,
                    $finalExpression,
                    false
                );
            }
            // normal statement
            elseif (sizeof($statementParts) === 5) {
                $operator = $statementParts[3];
                $leftPart = $this->evaluateStatementPart($statementParts[1]);
                $rightPart = $this->evaluateStatementPart($statementParts[4], $operator !== self::TOKEN_COMPARISON_OPERATOR_REGEX_MATCH);
                $logicFlip = $statementParts[2] === self::TOKEN_LOGIC_FLIP;
                                
                // if the data could not be evaluated we need to return false
                if (
                  (is_string($leftPart)
                  and
                  substr($leftPart, 0, 1) === self::TOKEN_DATA_START
                  and
                  substr($leftPart, -1) === self::TOKEN_DATA_END)
                  or
                  (is_string($rightPart)
                  and
                  substr($rightPart, 0, 1) === self::TOKEN_DATA_START and substr($rightPart, -1) === self::TOKEN_DATA_END)
                ) {
                    return new \TRD\Parser\RuleResponse\IsFalse;
                }
                
                if ($rightPart === "true") {
                    $rightPart = true;
                }
                if ($rightPart === "false") {
                    $rightPart = false;
                }

                switch ($operator) {
                    case self::TOKEN_COMPARISON_OPERATOR_GREATER_THAN_OR_EQUAL_TO:
                        $finalExpression = $this->transformExpression(
                            $statement,
                            ($leftPart >= $rightPart),
                            $finalExpression
                        );
                    break;
                    case self::TOKEN_COMPARISON_OPERATOR_LESS_THAN_OR_EQUAL_TO:
                        $finalExpression = $this->transformExpression(
                            $statement,
                            ($leftPart <= $rightPart),
                            $finalExpression
                        );
                    break;
                    case self::TOKEN_COMPARISON_OPERATOR_GREATER_THAN:
                        $finalExpression = $this->transformExpression(
                            $statement,
                            ($leftPart > $rightPart),
                            $finalExpression
                        );
                    break;
                    case self::TOKEN_COMPARISON_OPERATOR_LESS_THAN:
                        $finalExpression = $this->transformExpression(
                            $statement,
                            ($leftPart < $rightPart),
                            $finalExpression
                        );
                    break;
                    case self::TOKEN_COMPARISON_OPERATOR_EQUAL_TO:
                        $finalExpression = $this->transformExpression(
                            $statement,
                            (strtoupper($leftPart) == strtoupper($rightPart)),
                            $finalExpression
                        );
                    break;
                    case self::TOKEN_COMPARISON_OPERATOR_NOT_EQUAL_TO:
                        $finalExpression = $this->transformExpression(
                            $statement,
                            (strtoupper($leftPart) != strtoupper($rightPart)),
                            $finalExpression
                        );
                    break;
                    case self::TOKEN_COMPARISON_OPERATOR_WILDCARD_MATCH:

                        if (substr_count($statementParts[1], '*') > 0) {
                            throw new InvalidRule('Wildcards go on the right side of the statement, not left.');
                        }

                        //$regex = '/^'.$this->evaluateWildcardToRegex($rightPart).'$/i';

                        $finalExpression = $this->transformExpression(
                            $statement,
                            $logicFlip xor fnmatch($rightPart, $leftPart, FNM_CASEFOLD) //(preg_match($regex, $leftPart))
                            ,
                            $finalExpression
                        );
                    break;
                    case self::TOKEN_COMPARISON_OPERATOR_ARRAY_CONTAINS:

                        $contains = false;
                        if (is_array($leftPart)) {
                            if (in_array(strtoupper($rightPart), array_map('strtoupper', $leftPart))) {
                                $contains = true;
                            }
                        } else {
                            if (in_array(strtoupper($rightPart), explode(',', strtoupper($leftPart)))) {
                                $contains = true;
                            }
                        }

                        $finalExpression = $this->transformExpression(
                            $statement,
                            $logicFlip xor $contains,
                            $finalExpression
                        );


                    break;
                    case self::TOKEN_COMPARISON_OPERATOR_ITEM_IS_IN_ARRAY:

                        $isin = false;
                        if (is_array($rightPart)) {
                            if (in_array(strtoupper($leftPart), array_map('strtoupper', $rightPart))) {
                                $isin = true;
                            }
                        } else {
                            if (in_array(strtoupper($leftPart), explode(',', strtoupper($rightPart)))) {
                                $isin = true;
                            }
                        }

                        $finalExpression = $this->transformExpression(
                            $statement,
                            $logicFlip xor $isin,
                            $finalExpression
                        );

                    break;
                    case self::TOKEN_COMPARISON_OPERATOR_ARRAY_CONTAINS_ANY:

                        $contains = false;
                        if (is_array($leftPart)) {
                            if (sizeof(array_intersect(array_map('strtoupper', $leftPart), explode(',', strtoupper($rightPart)))) > 0) {
                                $contains = true;
                            }
                        } else {
                            if (sizeof(array_intersect(explode(',', strtoupper($leftPart)), explode(',', strtoupper($rightPart)))) > 0) {
                                $contains = true;
                            }
                        }

                        $finalExpression = $this->transformExpression(
                            $statement,
                            $logicFlip xor $contains,
                            $finalExpression
                        );

                    break;

                    case self::TOKEN_COMPARISON_OPERATOR_REGEX_MATCH:
                                                                                                                
                      $matches = false;
                      if (is_string($leftPart) and is_string($rightPart)) {
                          if (@preg_match($rightPart, $leftPart) === 1) {
                              $matches = true;
                          }
                      }
                                            
                      $finalExpression = $this->transformExpression(
                          $statement,
                          $logicFlip xor $matches,
                          $finalExpression,
                          false
                      );
                                                                                        
                    break;
                }
            } else {
                throw new InvalidRule('Your rule statements should contain a left part, an operator and a right part: ' . $statement);
            }
        }

        try {
            $result = eval('return (' . $finalExpression . ');');
        } catch (\ParseError $e) {
            throw new InvalidRule(sprintf('Unable to evaluate rule %s - Final expression: %s', $rule, $finalExpression));
        }
                
        if ($keywordMatch == 'EXCEPT' and $result) {
            return new \TRD\Parser\RuleResponse\IsExcept;
        }

        if ($keywordMatch == 'ALLOW' and !$result) {
            return new \TRD\Parser\RuleResponse\IsFalse;
        }

        if ($keywordMatch == 'DROP' and $result) {
            return new \TRD\Parser\RuleResponse\IsFalse;
        }

        return new \TRD\Parser\RuleResponse\IsTrue;
    }

    public function transformExpression($statement, $result, $incoming, $removeParentheses = true)
    {
        if ($removeParentheses) {
            $statement = str_replace(array('(',')'), '', $statement);
        }
        return str_replace_first(
            $statement,
            $this->transformEvaluation($result),
            $incoming
        );
    }

    public function transformEvaluation($result)
    {
        return ($result ? 'true === true' : 'false === true');
    }

    public function evaluateStatementPart($part, $removeParentheses = true)
    {
        if ($removeParentheses) {
            $part = str_replace(array('(', ')'), '', $part);
        }

        // check the total opening and closing tags match up
        $openingBrackets = substr_count($part, '[');
        $closingBrackets = substr_count($part, ']');
                
        // we have brackets but the total don't add up
        if (
            ($openingBrackets > 0 or $closingBrackets > 0)
            and $openingBrackets != $closingBrackets
        ) {
            throw new InvalidRule('Opening and closing bracket count doesn\'t match');
        }

        // we have brackets, and they are the same count, but wrong order
        if ($openingBrackets == $closingBrackets and strpos($part, '[') > strpos($part, ']')) {
            throw new InvalidRule('Brackets appear to be the wrong way around');
        }
                
//        // no brackets but we have dots
//        if (substr_count($part, '.') > 0 and ($openingBrackets == 0 or $closingBrackets == 0)) {
//            var_dump($part);
//            throw new InvalidRule('Rule seems to contain data but no brackets');
//        }

        if (substr($part, 0, 1) === '[' and substr($part, -1) === ']' and $bit = $this->get_string_between($part, '[', ']')) {
            if ($this->data instanceof RuleData and $this->data->has($bit)) {
                return $this->data->get($bit);
            }
        }

        // if (preg_match('/^\[(.*?)\]$/i', $part, $matches)) {
        //     if ($this->data instanceof RuleData AND $this->data->has($matches[1])) {
        //         return $this->data->get($matches[1]);
        //     }
        // }
                
        return $part;
    }

    public function evaluateWildcardToRegex($wildcard)
    {
        return str_replace('*', '.*?', $wildcard);
    }

    public function sortRules($rules)
    {
        $sortedRules = array();
        foreach ($rules as $rule) {
            if (substr($rule, -6) == 'EXCEPT') {
                array_unshift($sortedRules, $rule);
            } else {
                array_push($sortedRules, $rule);
            }
        }
        return $sortedRules;
    }

    private function logicFlip($evaluation, $flipped = false)
    {
        if ($flipped === true) {
            if ($evaluation === true) {
                return false;
            } else {
                return true;
            }
        } else {
            return $evaluation;
        }
    }

    public function parse()
    {
        $this->rules = $this->sortRules($this->rules);

        foreach ($this->rules as $rule) {
            if ($this->parseRule($rule) instanceof \TRD\Parser\RuleResponse\IsExcept) {
                return true;
            } elseif ($this->parseRule($rule) instanceof \TRD\Parser\RuleResponse\IsFalse) {
                return false;
            }
        }

        return true;
    }

    // optimisisations
    private function multi_explode($delimiters, $string)
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    private function str_ends($options, $str)
    {
        foreach ($options as $o) {
            if (strtolower(substr($str, -1 * strlen($o))) === strtolower($o)) {
                return $o;
            }
        }
        return false;
    }

    private function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
    private function trim($arr)
    {
        foreach ($arr as $k => $v) {
            $arr["$k"] = trim($v);
        }
        return $arr;
    }
}

function str_replace_first($search, $replace, $subject)
{
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}
