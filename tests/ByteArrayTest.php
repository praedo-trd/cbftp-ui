<?php declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use TRD\Utility\ByteArray;

final class ByteArrayTest extends TestCase
{
    public function testAppendFromEmpty(): void
    {
        $bytes = new ByteArray(null);
        $bytes->append("IRC message\n");
        $ret = $bytes->pickUntilByte("\n");
        $this->assertEquals($ret, "IRC message");
    }
  
    public function testHasBytes(): void
    {
        $bytes = new ByteArray("IRC message\n");
        $this->assertGreaterThan(0, sizeof($bytes->getBytes()));
        $this->assertEquals(12, sizeof($bytes->getBytes()));
    }
    
    public function testHasBytesMultipleMessages(): void
    {
        $bytes = new ByteArray("IRC message\nIRC message 2\n");
        $this->assertGreaterThan(0, sizeof($bytes->getBytes()));
        $this->assertEquals(26, sizeof($bytes->getBytes()));
    }
    
    public function testAsString(): void
    {
        $string = "Message";
        $bytes = new ByteArray($string);
        $this->assertEquals($string, $bytes->asString());
    }
    
    public function testBasicRead(): void
    {
        $string = "IRC message\n";
        $bytes = new ByteArray($string);
        $ret = $bytes->pickUntilByte("\n");
        $this->assertNotEquals(false, $ret);
        $this->assertEquals($ret, "IRC message");
    }
    
    public function testMultiRead(): void
    {
        $string = "IRC message\nIRC message 2\n";
        $bytes = new ByteArray($string);
        $ret = $bytes->pickUntilByte("\n");
        $this->assertNotEquals(false, $ret);
        $this->assertEquals($ret, "IRC message");
        $ret = $bytes->pickUntilByte("\n");
        $this->assertNotEquals(false, $ret);
        $this->assertEquals($ret, "IRC message 2");
    }
    
    public function testLoop(): void
    {
        $string = "IRC message\nIRC message 2\n";
        $bytes = new ByteArray($string);
        $iterations = 0;
        $strings = [];
        while ($str = $bytes->pickUntilByte("\n")) {
            $strings[] = $str;
            $iterations++;
        }
        $this->assertEquals($iterations, 2);
        $this->assertEquals($strings, [
          'IRC message', 'IRC message 2'
        ]);
    }
    
    public function testSplitRead(): void
    {
        $string = "IRC message\nIRC mess";
        $bytes = new ByteArray($string);
        $iterations = 0;
        $strings = [];
        while ($str = $bytes->pickUntilByte("\n")) {
            $strings[] = $str;
            $iterations++;
        }
        $this->assertEquals($bytes->asString(), "IRC mess");
        $bytes->append("age 2\nIRC message 3\n");
        while ($str = $bytes->pickUntilByte("\n")) {
            $strings[] = $str;
            $iterations++;
        }
        $this->assertEquals($iterations, 3);
        $this->assertEquals($strings, [
          'IRC message', 'IRC message 2', 'IRC message 3'
        ]);
    }
}
