<?php

namespace Tests\Unit\Infrastructure\Library\DomDocumentLoader;

use Infrastructure\Library\DomDocumentLoader;
use Infrastructure\Library\FileLocation;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class LoadTest extends TestCase
{
    public function testLoad()
    {
        $xmlDocument = 'SOME_GOOD_XML';
        $xsdLocation = new FileLocation(__FILE__); // Any file that exists will suffice for this test

        $mockDomDocument = $this->getMock('\DOMDocument');

        $mockDomDocument->expects($this->once())
                        ->method('loadXml')
                        ->with($this->equalTo($xmlDocument));

        $mockDomDocument->expects($this->once())
                        ->method('schemaValidate')
                        ->with($this->equalTo($xsdLocation))
                        ->will($this->returnValue(true));

        $mockDomDocumentFactory = $this->getMock('Infrastructure\Library\DomDocumentFactory');

        $mockDomDocumentFactory->expects($this->once())
                               ->method('create')
                               ->will($this->returnValue($mockDomDocument));

        $domDocumentLoader = new DomDocumentLoader($mockDomDocumentFactory);
        $domDocument = $domDocumentLoader->load($xmlDocument, $xsdLocation);

        $this->assertEquals($mockDomDocument, $domDocument);
    }

    ###

    public function testLoadWithoutXsd()
    {
        $xmlDocument = 'SOME_GOOD_XML';

        $mockDomDocument = $this->getMock('\DOMDocument');

        $mockDomDocument->expects($this->once())
                        ->method('loadXml')
                        ->with($this->equalTo($xmlDocument));

        $mockDomDocument->expects($this->never())
                        ->method('schemaValidate');

        $mockDomDocumentFactory = $this->getMock('Infrastructure\Library\DomDocumentFactory');

        $mockDomDocumentFactory->expects($this->once())
                               ->method('create')
                               ->will($this->returnValue($mockDomDocument));

        $domDocumentLoader = new DomDocumentLoader($mockDomDocumentFactory);
        $domDocument = $domDocumentLoader->load($xmlDocument);

        $this->assertEquals($mockDomDocument, $domDocument);
    }

    ###

    /**
     * @expectedException Infrastructure\Library\InvalidXmlException
     * @expectedExceptionMessage XML Invalid
     */
    public function testLoadWithEmptyXml()
    {
        $xmlDocument = '';
        $xsdLocation = new FileLocation(__FILE__); // Any file that exists will suffice for this test

        $mockDomDocumentFactory = $this->getMock('Infrastructure\Library\DomDocumentFactory');

        $mockDomDocumentFactory->expects($this->never())
                               ->method('create');

        $domDocumentLoader = new DomDocumentLoader($mockDomDocumentFactory);
        $domDocumentLoader->load($xmlDocument, $xsdLocation);
    }

    ###

    /**
     * @expectedException Infrastructure\Library\InvalidXmlException
     * @expectedExceptionMessage XML Invalid:
     */
    public function testLoadWithInvalidXml()
    {
        $xmlDocument = 'PRETEND_DODGY_XML';
        $xsdLocation = new FileLocation(__FILE__); // Any file that exists will suffice for this test

        $mockDomDocument = $this->getMock('\DOMDocument');

        $mockDomDocument->expects($this->once())
                        ->method('loadXml')
                        ->with($this->equalTo($xmlDocument));

        $mockDomDocument->expects($this->once())
                        ->method('schemaValidate')
                        ->with($this->equalTo($xsdLocation))
                        ->will($this->returnValue(false));

        $mockDomDocumentFactory = $this->getMock('Infrastructure\Library\DomDocumentFactory');

        $mockDomDocumentFactory->expects($this->once())
                               ->method('create')
                               ->will($this->returnValue($mockDomDocument));

        $domDocumentLoader = new DomDocumentLoader($mockDomDocumentFactory);
        $domDocumentLoader->load($xmlDocument, $xsdLocation);
    }
}
