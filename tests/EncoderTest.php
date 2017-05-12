<?php

namespace Akatsuki\Component\Bencode\Test;

use Akatsuki\Component\Bencode\BencodeSerializable;
use PHPUnit\Framework\TestCase;
use Akatsuki\Component\Bencode\Encoder;

/**
 * Class EncoderTest
 *
 * @package Akatsuki\Component\Bencode\Test
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class EncoderTest extends TestCase
{
    /**
     * @var Encoder
     */
    private $encoder;

    public function setUp()
    {
        $this->encoder = new Encoder();
    }
    
    /**
     * Test that strings are properly encoded
     **/
    public function testCanEncodeString()
    {
        $this->assertEquals("6:string", $this->encoder->encode("string"));
    }

    /**
     * Test that integers are properly encoded
     **/
    public function testCanEncodeInteger()
    {
        $this->assertEquals("i42e", $this->encoder->encode(42));
        $this->assertEquals("i-42e", $this->encoder->encode(-42));
        $this->assertEquals("i0e", $this->encoder->encode(0));
    }

    /**
     * Test that lists are properly encoded
     **/
    public function testCanEncodeList()
    {
        $this->assertEquals("l3:foo3:bare", $this->encoder->encode(array("foo", "bar")));
    }

    /**
     * Test that dictionaries are properly encoded
     **/
    public function testCanEncodeDictionary()
    {
        $this->assertEquals("d3:foo3:bare", $this->encoder->encode(array("foo" => "bar")));
    }

    /**
     * Test that objects with public properties are properly encoded
     **/
    public function testCanEncodeObject()
    {
        $object = new \stdClass;
        $object->string = "foo";
        $object->integer = 42;

        $this->assertEquals("d7:integeri42e6:string3:fooe", $this->encoder->encode($object));
    }

    /**
     * Test that objects with public properties are properly encoded
     **/
    public function testCanEncodeObjectWithBencodeSerializable()
    {
        $object = new class implements BencodeSerializable {
            public function bencodeSerialize(): array
            {
                return [
                    'string' => 'foo',
                    'integer' => 42
                ];
            }
        };

        $this->assertEquals("d7:integeri42e6:string3:fooe", $this->encoder->encode($object));
    }

    /**
     * Test regression of issue #1
     *
     * Encoder should treat numeric strings as strings rather than
     * integers.
     */
    public function testIssue1Regression()
    {
        $data = array(
            "Numeric string value" => "1",
            "1" => "Numeric string key",
        );

        $this->assertEquals("d20:Numeric string value1:11:118:Numeric string keye", $this->encoder->encode($data));
    }
}
