<?php declare(strict_types=1);

require_once(__DIR__ . '/../app/env.php');
require_once __DIR__ .'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

final class BoxOfficeMojoTest extends TestCase
{
    protected static $bom;
  
    public static function setUpBeforeClass(): void
    {
        $container = require_once(__DIR__ . '/_MockContainer.php');
        self::$bom = new \TRD\DataProvider\BoxOfficeMojoDataProvider($container);
    }
  
    public function testLookup(): void
    {
        $result = self::$bom->lookup('toy story 4', true, array('id' => 'tt1979376', 'poster_hash' => '2873A81B4DE249C4131A9948A1A6B5AF', 'country' => 'UK'));
        $this->assertInstanceOf(\TRD\DataProvider\DataProviderResponse::class, $result);
        
        $data = $result->getData();
        $this->assertNotEmpty($data);
        $this->assertMatchesRegularExpression('/tt\d+/i', $data['id']);
        $this->assertIsInt($data['screens_us']);
        $this->assertIsInt($data['screens_uk']);
        $this->assertGreaterThan(0, $data['screens_us']);
        $this->assertGreaterThan(0, $data['screens_uk']);
        $this->assertTrue($data['has_screens']);
        $this->assertFalse($data['limited']);
        $this->assertTrue($data['wide']);
    }
}
