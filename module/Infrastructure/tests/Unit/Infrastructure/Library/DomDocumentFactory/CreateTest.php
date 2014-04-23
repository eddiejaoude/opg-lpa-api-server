<?php

namespace Tests\Unit\Infrastructure\Library\DomDocumentFactory;

use Infrastructure\Library\DomDocumentFactory;

use DOMDocument;
use PHPUnit_Framework_TestCase as TestCase;

class CreateTest extends TestCase
{
    public function testCreate()
    {
        $domDocumentFactory = new DomDocumentFactory();
        $domDocument = $domDocumentFactory->create();
        $this->assertTrue($domDocument instanceof DOMDocument);
    }
}
