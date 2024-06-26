<?php
namespace Ratchet\Http;

use Ratchet\RatchetTestCase;

/**
 * @covers Ratchet\Http\HttpRequestParser
 */
class HttpRequestParserTest extends RatchetTestCase {
    protected $parser;

    /**
     * @before
     */
    public function before() {
        $this->parser = new HttpRequestParser;
    }

    public function headersProvider() {
        return array(
            array(false, "GET / HTTP/1.1\r\nHost: socketo.me\r\n")
          , array(true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n")
          , array(true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n1")
          , array(true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖")
          , array(true,  "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie✖\r\n\r\n")
          , array(true, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\nHixie\r\n")
        );
    }

    /**
     * @dataProvider headersProvider
     */
    public function testIsEom($expected, $message) {
        $this->assertEquals($expected, $this->parser->isEom($message));
    }

    public function testBufferOverflowResponse() {
        $conn = $this->_getMock('\Ratchet\ConnectionInterface');

        $this->parser->maxSize = 20;

        $this->assertNull($this->parser->onMessage($conn, "GET / HTTP/1.1\r\n"));

        $this->_setExpectedException('OverflowException');

        $this->parser->onMessage($conn, "Header-Is: Too Big");
    }

    public function testReturnTypeIsRequest() {
        $conn = $this->_getMock('\Ratchet\ConnectionInterface');
        $return = $this->parser->onMessage($conn, "GET / HTTP/1.1\r\nHost: socketo.me\r\n\r\n");

        $this->assertInstanceOf('\Psr\Http\Message\RequestInterface', $return);
    }
}
