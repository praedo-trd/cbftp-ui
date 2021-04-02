<?php declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use TRD\DataProvider\ReleaseNameDataProvider as RDP;

final class ReleaseNameDataProviderTest extends TestCase
{
    public function testExtractRepeatExtras(): void
    {
        $this->assertEquals(RDP::extractRepeatExtras('TV.Show.S01E01.PROPER.HDTV.x264-Group'), 'PROPER');
        $this->assertEquals(RDP::extractRepeatExtras('TV.Show.S01E01.REPACK.HDTV.x264-Group'), 'REPACK');
        $this->assertEquals(RDP::extractRepeatExtras('TV.Show.S01E01.REAL.PROPER.HDTV.x264-Group'), 'REAL.PROPER');
    }
    
    public function testExtractEpisodeToken(): void
    {
        $this->assertEquals(RDP::extractEpisodeToken('TV.Show.S01E01-E02.PROPER.HDTV.x264-Group'), 'S01E01-E02');
    }
    
    public function testExtractOther(): void
    {
        $this->assertEquals(
            RDP::extractOther('TV.Show.S04E00.Inside.Keeper.of.the.Light.iNTERNAL.720p.WEB.x264-GROUP', [
              'episode' => 0, 'internal' => false,
              'codec' => 'x264', 'source' => 'WEB',
              'resolution' => '720P'
            ])['internal'],
            true
        );
        
        $otherBits = RDP::extractOther('TV.Show.S03E01-E02.SWEDISH.720p.HDTV.x264-Group', [
          'internal' => false,
          'codec' => 'x264', 'source' => 'WEB',
          'resolution' => '720P'
        ]);
        $this->assertEquals($otherBits['language'], 'SWEDISH');
    }
    
    public function testExtractEpisode(): void
    {
        // basic cases
        $this->assertEquals(RDP::extractEpisode('TV.Show.S01E00.PROPER.HDTV.x264-Group'), 0);
        $this->assertEquals(RDP::extractEpisode('TV.Show.S01E01.PROPER.HDTV.x264-Group'), 1);
        
        // only episode
        $this->assertEquals(RDP::extractEpisode('TV.Show.E01.PROPER.HDTV.x264-Group'), 1);
        
        // part syntax
        $this->assertEquals(RDP::extractEpisode('TV.Show.Part.S01.Part10.PROPER.HDTV.x264-Group'), 10);
        $this->assertEquals(RDP::extractEpisode('TV.Show.Part.S01.Part.10.PROPER.HDTV.x264-Group'), 10);
        
        // episode syntax
        $this->assertEquals(RDP::extractEpisode('TV.Show.Part.S01.Episode10.PROPER.HDTV.x264-Group'), 10);
        $this->assertEquals(RDP::extractEpisode('TV.Show.Part.S01.Episode.10.PROPER.HDTV.x264-Group'), 10);
        
        // yyyy.mm.dd syntax
        $this->assertEquals(RDP::extractEpisode('TV.Show.2020.01.01.PROPER.HDTV.x264-Group'), '01.01');
        
        // EXXEXX syntax
        $this->assertEquals(RDP::extractEpisode('TV.Show.S01E01E02.PROPER.HDTV.x264-Group'), '0102');
        $this->assertEquals(RDP::extractEpisode('TV.Show.S01E01-E02.PROPER.HDTV.x264-Group'), '0102');
    }
}
