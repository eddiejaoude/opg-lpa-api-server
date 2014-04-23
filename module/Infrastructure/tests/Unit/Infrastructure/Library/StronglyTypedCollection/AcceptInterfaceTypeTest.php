<?php

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptInterfaceTypeTest {

    use Infrastructure\Library\StronglyTypedCollection;

    interface DummyInterface {}

    class WhateverType implements DummyInterface {}

    class WhicheverType implements DummyInterface {}

    class DummyCollection extends StronglyTypedCollection {}

}

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection {

    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptInterfaceTypeTest\DummyCollection;
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptInterfaceTypeTest\DummyInterface;
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptInterfaceTypeTest\WhateverType;
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptInterfaceTypeTest\WhicheverType;

    use PHPUnit_Framework_TestCase as TestCase;

    class AcceptInterfaceTypeTest extends TestCase
    {
        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testValidObjectsAreStoredAndRetrieved()
        {
            $collection = new DummyCollection();
            $elementA   = new WhateverType();
            $elementB   = new WhicheverType();

            $collection['a'] = $elementA;
            $collection['b'] = $elementB;

            $this->assertCount(2, $collection);
            $this->assertTrue($collection['a'] instanceof DummyInterface);
            $this->assertTrue($collection['b'] instanceof DummyInterface);
            $this->assertTrue($collection['a'] instanceof WhateverType);
            $this->assertTrue($collection['b'] instanceof WhicheverType);
            $this->assertEquals($elementA, $collection['a']);
            $this->assertEquals($elementB, $collection['b']);
        }
    }
}
