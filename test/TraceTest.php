<?php
use Four026\Phable\Trace;

/**
 * Testing passphrase generator
 */
class TraceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Trace
     */
    private $trace;

    function setUp()
    {
        $this->trace = new Trace(new Four026\Phable\Grammar(__DIR__ . '/testWordList.json'));
        $this->trace->setSeed(42);
    }

    function testGetText()
    {
        $this->assertEquals('Once Hilda the Hawk went on an adventure to the Scenic Meadow.', $this->trace->getText());
    }
}
