<?php

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptClassTypeTest {

    use Infrastructure\Library\StronglyTypedCollection;

    class DummyClassType {}

    class DummyClassTypeCollection extends StronglyTypedCollection {}

}

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection {

    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptClassTypeTest\DummyClassType;
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptClassTypeTest\DummyClassTypeCollection;

    use PHPUnit_Framework_TestCase as TestCase;

    class AcceptClassTypeTest extends TestCase
    {
        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testValidObjectsAreStoredAndRetrieved()
        {
            $collection = new DummyClassTypeCollection();
            $elementA   = new DummyClassType();
            $elementB   = new DummyClassType();

            $collection['a'] = $elementA;
            $collection['b'] = $elementB;

            $this->assertCount(2, $collection);
            $this->assertTrue($collection['a'] instanceof DummyClassType);
            $this->assertTrue($collection['b'] instanceof DummyClassType);
            $this->assertEquals($elementA, $collection['a']);
            $this->assertEquals($elementB, $collection['b']);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testInvalidElementsAreRejected()
        {
            $courses = new DummyClassTypeCollection();
            $courses['X'] = new \StdClass;
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testInvalidElementsAreRejected1()
        {
            $courses = new DummyClassTypeCollection();
            $courses['X'] = null;
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testInvalidElementsAreRejected2()
        {
            $courses = new DummyClassTypeCollection();
            $courses['X'] = 1;
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testInvalidElementsAreRejected3()
        {
            $courses = new DummyClassTypeCollection();
            $courses['X'] = 'whatever';
        }
    }

}
