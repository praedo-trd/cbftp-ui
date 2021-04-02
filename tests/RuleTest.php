<?php declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use TRD\Parser\RuleResponse\IsTrue;
use TRD\Parser\RuleResponse\IsFalse;
use TRD\Parser\InvalidRule;

final class RuleTest extends TestCase
{
    private $parser = null;
  
    protected function setUp(): void
    {
        $this->parser = new \TRD\Parser\Rules();
    }
    
    private function rule($rule, $data = null)
    {
        if ($data instanceof \TRD\Parser\RuleData) {
            $this->parser->addData($data);
        }
        return $this->parser->parseRule($rule);
    }
  
    public function testKeywordAll(): void
    {
        $this->assertInstanceOf(IsTrue::class, $this->rule('ALL'));
    }
    
    public function testInvalidRuleMissingLogicWordAtEnd(): void
    {
        $this->expectException(InvalidRule::class);
        $this->rule('INVALID RULE BECAUSE NO LOGIC WORD AT THE END');
    }
    
    public function testInvalidRuleLogicWordInWrongPlace(): void
    {
        $this->expectException(InvalidRule::class);
        $this->rule('CONTAINS ALLOW IN THE WRONG PLACE');
    }
    
    public function testInvalidRuleMultipleLogicWords(): void
    {
        $this->expectException(InvalidRule::class);
        $this->rule('CONTAINS ALLOW IN THE RIGHT PLACE BUT TWICE - ALLOW');
    }
    
    public function testInvalidRuleSpaceAroundOperators(): void
    {
        $this->expectException(InvalidRule::class);
        $this->rule('1==1 DROP');
    }
    
    public function testOperatorLessThanEqualTo(): void
    {
        $this->assertInstanceOf(IsFalse::class, $this->rule('2 <= 1 ALLOW'));
        $this->assertInstanceOf(IsTrue::class, $this->rule('1 <= 2 ALLOW'));
        $this->assertInstanceOf(IsTrue::class, $this->rule('2 <= 2 ALLOW'));
    }
    
    public function testOperatorGreaterThanEqualTo(): void
    {
        $this->assertInstanceOf(IsTrue::class, $this->rule('2 >= 1 ALLOW'));
        $this->assertInstanceOf(IsTrue::class, $this->rule('2 >= 2 ALLOW'));
        $this->assertInstanceOf(IsFalse::class, $this->rule('1 >= 2 ALLOW'));
    }
    
    public function testOperatorLessThan(): void
    {
        $this->assertInstanceOf(IsFalse::class, $this->rule('2 < 1 ALLOW'));
        $this->assertInstanceOf(IsFalse::class, $this->rule('2 < 2 ALLOW'));
        $this->assertInstanceOf(IsTrue::class, $this->rule('1 < 2 ALLOW'));
    }
    
    public function testOperatorGreaterThan(): void
    {
        $this->assertInstanceOf(IsTrue::class, $this->rule('2 > 1 ALLOW'));
        $this->assertInstanceOf(IsFalse::class, $this->rule('1 > 2 ALLOW'));
        $this->assertInstanceOf(IsFalse::class, $this->rule('2 > 2 ALLOW'));
    }
    
    public function testOperatorEqualTo(): void
    {
        $this->assertInstanceOf(IsTrue::class, $this->rule('1 == 1 ALLOW'));
        $this->assertInstanceOf(IsFalse::class, $this->rule('2 == 1 ALLOW'));
        //$this->assertInstanceOf(IsTrue::class, $this->rule('"" ==  ALLOW'));
    }
    
    public function testOperatorNotEqualTo(): void
    {
        $this->assertInstanceOf(IsFalse::class, $this->rule('1 != 1 ALLOW'));
        $this->assertInstanceOf(IsTrue::class, $this->rule('2 != 1 ALLOW'));
        
        $data = new \TRD\Parser\RuleData();
        $data->set('rlsname.group', 'SOMETHING');
        $this->assertInstanceOf(IsTrue::class, $this->rule('[rlsname.group] != SOMETHING DROP', $data));
    }
    
    public function testIsIn(): void
    {
        $data = new \TRD\Parser\RuleData();
        $data->set('tvmaze.country', 'United States');
        
        $this->assertInstanceOf(IsTrue::class, $this->rule('[tvmaze.country] isin Australia,Canada,New Zealand,United States,United Kingdom ALLOW', $data));
        $this->assertInstanceOf(IsTrue::class, $this->rule('[tvmaze.country] == United States OR [tvmaze.country] == Canada OR [tvmaze.country] == United Kingdom ALLOW', $data));
    }
    
    public function testOperatorMatches(): void
    {
        $this->assertInstanceOf(IsTrue::class, $this->rule('moo matches /moo/i ALLOW'));
        $this->assertInstanceOf(IsFalse::class, $this->rule('foobar matches /moo/i ALLOW'));
        $this->assertInstanceOf(IsFalse::class, $this->rule('Moo matches /moo/ ALLOW'));
    }
    
    public function testOperatorMatchesWithParentheses(): void
    {
        $this->assertInstanceOf(IsTrue::class, $this->rule('This.Is.Norwegian matches /norwegian\.1080p\.Bluray\.(x|h)264/i EXCEPT'));
        $this->assertInstanceOf(IsFalse::class, $this->rule('Kindred.Spirits.S01E01.Some.Episode.Name.1080p.WEB.x264-GROUP matches /swedish\.(720p|1080p)\.(HDTV|HDTVRiP|WEBRIP|WEB)\.(x|h)264/i ALLOW'));
    }
      
    public function testOperatorMatchesWithSquareBrackets(): void
    {
        $this->assertInstanceOf(IsTrue::class, $this->rule('Apple.Homekit matches /^(Adobe|Ahead|Apple[\._]|Autodesk|Cyberlink|Jasc|Symantec|Macromedia|Magix|McAfee|Microsoft|Pinnacle|Roxio|SUSE|TomTom|Ulead|VMware)/i ALLOW'));
    }
    
    public function testOperatorInverseMatches(): void
    {
        $this->assertInstanceOf(IsFalse::class, $this->rule('moo !matches /moo/ ALLOW'));
        $this->assertInstanceOf(IsTrue::class, $this->rule('Foo !matches /moo/ ALLOW'));
    }
    
    public function testDataSubstitutions(): void
    {
        $data = new \TRD\Parser\RuleData();
        $data->set('imdb.votes', 1000);
        
        $this->assertInstanceOf(IsTrue::class, $this->rule('[imdb.votes] > 500 ALLOW', $data));
        $this->assertInstanceOf(IsFalse::class, $this->rule('[imdb.votes] < 500 ALLOW', $data));
    }
    
    public function testEmptyStatement(): void
    {
        $data = new \TRD\Parser\RuleData();
        $data->set('rlsname.language', '');
        $this->assertInstanceOf(IsTrue::class, $this->rule('empty([rlsname.language]) ALLOW', $data));
        
        $data->set('rlsname.language', 'Swedish');
        $this->assertInstanceOf(IsFalse::class, $this->rule('empty([rlsname.language]) ALLOW', $data));

        $data = new \TRD\Parser\RuleData();
        $data->set('tvmaze.web', true);
        $data->set('tvmaze.country_code', '');

        $this->assertInstanceOf(IsTrue::class, $this->rule('[tvmaze.country_code] isin US,UK,CA OR ([tvmaze.web] == true AND empty([tvmaze.country_code])) ALLOW', $data));
    }
    
    public function testInvalidFunction(): void
    {
        $this->expectException(InvalidRule::class);
        $this->assertInstanceOf(InvalidRule::class, $this->rule('fake(1) ALLOW'));
    }
    
    public function testBasicDroppingWithData(): void
    {
        $data = new \TRD\Parser\RuleData();
        $data->set('rlsname', 'anything');
        $this->assertInstanceOf(IsFalse::class, $this->rule('[rlsname] iswm * DROP', $data));
    }
}
