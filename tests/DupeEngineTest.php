<?php declare(strict_types=1);

require_once(__DIR__ . '/../app/env.php');
require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use TRD\DupeEngine\Source;
use TRD\DupeEngine\Engine;
use TRD\DupeEngine\EngineResult;

final class DupeEngineTest extends TestCase
{
    public function testBasicCase(): void
    {
        $sources = array(
          new Source('Some.Show.S01E01.720p.WEBRip.x264-GRP1')
        );
        $engine = new Engine($sources);
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.720p.WEBRip.x264-GRP2', 'source.firstWins')->isDupe());
    }
    
    public function testTargetIsNotSource(): void
    {
        $sources = array(
          new Source('Some.Show.S01E01.720p.WEBRip.x264-GRP1')
        );
        $engine = new Engine($sources);
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.720p.WEBRip.x264-GRP1', 'source.firstWins')->isDupe());
    }
  
    public function testBasicFirstWins(): void
    {
        $engine = new Engine([
          new Source('Some.Show.S01E01.HDTV.x264-Group')
        ]);
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.WEBRIP.x264-Group', 'source.firstWins')->isDupe()); // dupe format
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.WEB.x264-Group', 'source.firstWins')->isDupe()); // dupe format
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.HDTV.x264-AnotherGroup', 'source.firstWins')->isDupe()); // actual dupe
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.INTERNAL.HDTV.x264-AnotherGroup', 'source.firstWins')->isDupe()); // internal never dupes
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.FRENCH.HDTV.x264-AnotherGroup', 'source.firstWins')->isDupe()); // another language should not be a dupe
    }
    
    public function testProperRepack(): void
    {
        $engine = new Engine([
          new Source('Some.Show.S01E01.HDTV.x264-Group')
        ]);
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.HDTV.PROPER.x264-AnotherGroup', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.HDTV.REPACK.x264-AnotherGroup', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.HDTV.RERIP.x264-AnotherGroup', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.HDTV.REAL.PROPER.x264-AnotherGroup', 'source.firstWins')->isDupe());
    }
    
    public function testLanguages(): void
    {
        $sources = array(
          new Source('Some.Show.S01E01.FRENCH.HDTV.x264-Group'),
          new Source('Some.Show.S01E03.Offen.fuer.alles.German.1080p.HDTV.x264-Group')
        );

        $engine = new Engine($sources);
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.WEBRIP.x264-Group', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.WEB.x264-Group', 'source.firstWins')->isDupe(), );
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.HDTV.x264-AnotherGroup', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.INTERNAL.HDTV.x264-AnotherGroup', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E03.1080p.BluRay.X264-Group', 'source.firstWins')->isDupe());
    }
    
    public function testComplicatedHierarchies(): void
    {
        $sources = array(
          new Source('Some.Show.S01E01.PROPER.HDTV.x264-Group'),
        );

        $engine = new Engine($sources);
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.REPACK.HDTV.x264-Group', 'source.firstWins')->isDupe());
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.RERIP.HDTV.x264-Group', 'source.firstWins')->isDupe());
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.PROPER.HDTV.x264-AnotherGroup', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.REAL.PROPER.HDTV.x264-AnotherGroup', 'source.firstWins')->isDupe());
    }
    
    public function testRegexFilter(): void
    {
        $sources = array(
          new Source('Some.Show.S01E01.CONVERT.1080p.HDTV.x264-Group'),
        );

        $engine = new Engine($sources);
        $engine->addFilterRegex('/[_.]CONVERT[_.]/i');
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.1080p.HDTV.x264-Group', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.RERIP.1080p.HDTV.x264-Group', 'source.firstWins')->isDupe(), );
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.PROPER.1080p.HDTV.x264-AnotherGroup', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.REAL.PROPER.1080p.HDTV.x264-AnotherGroup', 'source.firstWins')->isDupe());
        
        $result = $engine->isDupe('Some.Show.S01E01.1080p.HDTV.x264-Group', 'source.firstWins');
        $sources = $result->getSources();
        $this->assertEquals(sizeof($sources), 0);
    }
    
    public function testBasicPriority(): void
    {
        $sources = array(
        new Source('Some.Show.S01E01.1080p.HDTV.x264-Group'),
      );

        $engine = new Engine($sources);
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.1080p.WEB.x264-Group', 'source.priority', array('priority' => 'hdtv,web'))->isDupe());
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.1080p.HDTV.x264-Group', 'source.priority', array('priority' => 'hdtv,web'))->isDupe());
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.1080p.WEB.x264-Group', 'source.priority', array('priority' => 'web,hdtv'))->isDupe());
    }
    
    public function testMorePriority(): void
    {
        $sources = array(
        new Source('Some.Show.S01E01.1080p.WEB.x264-Group'),
      );

        $engine = new Engine($sources);
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.1080p.WEB.x264-Group', 'source.priority', array('priority' => 'web,hdtv'))->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.1080p.HDTV.x264-Group', 'source.priority', array('priority' => 'web,hdtv'))->isDupe());
        $this->assertTrue($engine->isDupe('Some.Show.S01E01.1080p.HDTV.x264-Group', 'source.priority', array('priority' => 'hdtv,web'))->isDupe());
    }
    
    public function testPriorityRegex(): void
    {
        $sources = array(
          new Source('Some.Show.S01E01.CONVERT.1080p.WEB.x264-Group'),
        );

        $engine = new Engine($sources);
        $engine->addFilterRegex('/[_.]CONVERT[_.]/i');
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.1080p.WEB.x264-Group', 'source.priority', array('priority' => 'web,hdtv'))->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.1080p.HDTV.x264-Group', 'source.priority', array('priority' => 'web,hdtv'))->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.1080p.HDTV.x264-Group', 'source.priority', array('priority' => 'hdtv,web'))->isDupe());
    }
    
    public function testInternalSources(): void
    {
        $sources = array(
          new Source('Some.Show.S01E01.INTERNAL.1080p.WEB.x264-Group'),
        );

        $engine = new Engine($sources);
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.1080p.WEB.x264-Group', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.1080p.HDTV.x264-Group', 'source.firstWins')->isDupe());
        $this->assertFalse($engine->isDupe('Some.Show.S01E01.1080p.HDTV.x264-Group', 'source.firstWins')->isDupe());
    }
    
    public function testReportedBug1(): void
    {
        $sources = array(
          new Source('Some.Show.S08E04.720p.WEBRip.x264-GRP1')
      );
        $engine = new Engine($sources);
        $this->assertTrue($engine->isDupe('Some.Show.S08E04.720p.HDTV.x264-GRP2', 'source.firstWins')->isDupe());
        $this->assertTrue($engine->isDupe('Some.Show.S08E04.720p.HDTV.x264-GRP2', 'source.priority', array('priority' => 'hdtv,web,webrip'))->isDupe());
    }
}
