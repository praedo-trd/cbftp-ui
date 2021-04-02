<?php declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use TRD\Utility\AnnounceString;

final class AnnounceStringsTest extends TestCase
{
    public function testAnnounceStringMatches1(): void
    {
        $result = AnnounceString::isAnnounceString(
            'SITENAME',
            '-Box- [SECTION] + NEW SHIT IN: Releasename-Group pre\'d 8s ago - (user: user)',
            '-&other- [&section] + NEW SHIT IN: &release pre\'d'
        );
        
        $this->assertIsArray($result);
        $this->assertEquals($result[2], 'SECTION');
        $this->assertEquals($result[3], 'Releasename-Group');
    }

    public function testAnnounceStringMatches2(): void
    {
        $result = AnnounceString::isAnnounceString(
            'SITENAME',
            'IRC #channel something- [SECTION] + New Case: Section/Releasename-GROUP by user@FRiENDS',
            '[&section] + New Case: &other/&release'
        );
        
        $this->assertIsArray($result);
        $this->assertEquals($result[1], 'SECTION');
        $this->assertEquals($result[3], 'Releasename-GROUP');
    }
}
