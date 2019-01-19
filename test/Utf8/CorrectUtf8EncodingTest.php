<?php

namespace ApiSkeletonsTest\Utf8;

use PHPUnit\Framework\TestCase;
use ApiSkeletons\Utf8\CorrectUtf8Encoding;

final class CorrectUtf8EncodingTest extends TestCase
{
    public function testProperEncoding()
    {
        $testString = 'abcdefg';

        $tool = new CorrectUtf8Encoding();
        $resultString = $tool($testString);

        $this->assertEquals($testString, $resultString);
    }

    public function testMultipleEncodedUtf8()
    {
        $testString = utf8_encode(utf8_encode("\xe2\x80\x9c"));

        $tool = new CorrectUtf8Encoding();
        $resultString = $tool($testString);

        $this->assertEquals("\xe2\x80\x9c", $resultString);
    }

    public function testInvalidUtf8()
    {
        $testString = utf8_encode(utf8_encode("\xe2\x80\x0c"));

        $tool = new CorrectUtf8Encoding();
        $resultString = $tool($testString);

        $this->assertNotEquals("\xe2\x80\x0c", $resultString);
    }
}
