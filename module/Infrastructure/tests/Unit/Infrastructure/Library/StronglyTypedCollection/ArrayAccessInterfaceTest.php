<?php

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection\ArrayAccessInterfaceTest
{
    use Infrastructure\Library\StronglyTypedCollection;

    class IntegerCollection extends StronglyTypedCollection
    {
        public function getAllowedElementType()
        {
            return 'integer';
        }
    }
}

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection
{
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\ArrayAccessInterfaceTest\IntegerCollection;

    use Iterator;
    use PHPUnit_Framework_TestCase as TestCase;

    class ArrayAccessInterfaceTest extends TestCase
    {
        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testOffsetExists()
        {
            $integers = new IntegerCollection();

            $integers[] = 123;
            $integers['b'] = 456;

            $this->assertTrue($integers->offsetExists(0));
            $this->assertTrue($integers->offsetExists('b'));
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testOffsetGet()
        {
            $integers = new IntegerCollection();

            $integers[] = 123;
            $integers['b'] = 456;

            $this->assertEquals(123, $integers->offsetGet(0));
            $this->assertEquals(456, $integers->offsetGet('b'));
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotFoundException
         */
        public function testOffsetGetWithInvalidOffset()
        {
            $integers = new IntegerCollection();

            $integers['a'] = 123;
            $integers['b'] = 456;

            $integers->offsetGet('c');
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testOffsetSet()
        {
            $integers = new IntegerCollection();

            $integers->offsetSet('a', 123);
            $integers->offsetSet('b', 456);

            $this->assertEquals(123, $integers->offsetGet('a'));
            $this->assertEquals(456, $integers->offsetGet('b'));
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testOffsetSetWithReplacement()
        {
            $integers = new IntegerCollection();

            $integers->offsetSet('a', 123);
            $integers->offsetSet('b', 456);
            $integers->offsetSet('a', 789);

            $this->assertEquals(789, $integers->offsetGet('a'));
            $this->assertEquals(456, $integers->offsetGet('b'));
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testOffsetUnset()
        {
            $integers = new IntegerCollection();

            $integers->offsetSet('a', 123);
            $integers->offsetSet('b', 456);

            $integers->offsetUnset('a');

            $this->assertFalse($integers->offsetExists('a'));
            $this->assertTrue( $integers->offsetExists('b'));
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotFoundException
         */
        public function testOffsetUnsetWithInvalidOffset()
        {
            $integers = new IntegerCollection();

            $integers->offsetSet('a', 123);
            $integers->offsetSet('b', 456);

            $integers->offsetUnset('c');
        }
    }
}
