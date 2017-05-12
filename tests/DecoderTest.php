<?php

namespace Akatsuki\Component\Bencode\Test;

use Akatsuki\Component\Bencode\Exception\InvalidSourceException;
use PHPUnit\Framework\TestCase;
use Akatsuki\Component\Bencode\Decoder;

/**
 * Class DecoderTest
 *
 * @package Akatsuki\Component\Bencode\Test
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class DecoderTest extends TestCase
{
    /**
     * @var Decoder
     */
    private $decoder;

    public function setUp()
    {
        $this->decoder = new Decoder();
    }
    
    /**
     * Test that strings are properly decoded
     *
     */
    public function testCanDecodeString()
    {
        $this->assertEquals("string", $this->decoder->decode('6:string'));
    }

    /**
     * Test that an unterminated string triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testUnterminatedStringThrowsException()
    {
        $this->decoder->decode("6:stri");
    }

    /**
     * Test that a zero-padded string length triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testZeroPaddedLengthInStringThrowsException()
    {
        $this->decoder->decode("03:foo");
    }

    /**
     * Test that a missing colon triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testMissingColonInStringThrowsException()
    {
        $this->decoder->decode("3foo");
    }

    /**
     * Test that integers are properly decoded
     *
     */
    public function testCanDecodeInteger()
    {
        $this->assertEquals("42", $this->decoder->decode("i42e"));
        $this->assertEquals("-42", $this->decoder->decode("i-42e"));
        $this->assertEquals("0", $this->decoder->decode("i0e"));
    }

    /**
     * Test that an empty integer triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testEmptyIntegerThrowsException()
    {
        $this->decoder->decode("ie");
    }

    /**
     * Test that a non-digit in an integer trigger an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testNonDigitCharInIntegerThrowsException()
    {
        $this->decoder->decode("iae");
    }

    /**
     * Test that a zero-padded integer triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testLeadingZeroInIntegerThrowsException()
    {
        $this->decoder->decode("i042e");
    }

    /**
     * Test that an unterminated integer triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testUnterminatedIntegerThrowsException()
    {
        $this->decoder->decode("i42");
    }

    /**
     * That that lists are properly decoded
     *
     */
    public function testCanDecodeList()
    {
        $this->assertEquals(array("foo", "bar"), $this->decoder->decode("l3:foo3:bare"));
    }

    /**
     * Test that an unterminated lists triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testUnterminatedListThrowsException()
    {
        $this->decoder->decode("l3:foo3:bar");
    }

    /**
     * Test that dictionaries are properly decoded
     *
     */
    public function testDecodeDictionary()
    {
        $this->assertEquals(["foo" => "bar"], $this->decoder->decode("d3:foo3:bare"));
    }

    /**
     * Test that an unterminated dictionary triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testUnterminatedDictThrowsException()
    {
        $this->decoder->decode("d3:foo3:bar");
    }

    /**
     * Test that a duplicate dictionary key triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testDuplicateDictionaryKeyThrowsException()
    {
        $this->decoder->decode("d3:foo3:bar3:foo3:bare");
    }

    /**
     * Test that a non-string dictionary key triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testNonStringDictKeyThrowsException()
    {
        $this->decoder->decode("di42e3:bare");
    }

    /**
     * Test that an unknown entity triggers an exception
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testUnknownEntityThrowsException()
    {
        $this->decoder->decode("a3:fooe");
    }

    /**
     * Test that attempting to decode a non-string triggers an exception
     *
     * @expectedException \TypeError
     */
    public function testDecodeNonStringThrowsException()
    {
        $this->decoder->decode([]);
    }

    /**
     * Test that multiple entities must be in a list or dictionary
     *
     * @expectedException Akatsuki\Component\Bencode\Exception\InvalidSourceException
     */
    public function testDecodeMultipleTypesOutsideOfListOrDictShouldThrowException()
    {
        $this->decoder->decode("3:foo3:bar");
    }
}
