<?php

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection\GetIteratorTest
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
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\GetIteratorTest\IntegerCollection;

    use Iterator;
    use PHPUnit_Framework_TestCase as TestCase;

    class GetIteratorTest extends TestCase
    {
        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testGetIterator()
        {
            $integers = new IntegerCollection();

            $integers['a'] = 123;
            $integers['b'] = 456;

            $iterator = $integers->getIterator();

            $this->assertTrue($iterator instanceof Iterator);

            $sum = 0;
            foreach ($iterator as $element) {
                $sum += $element;
            }

            $this->assertEquals(579, $sum);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testGetIteratorWhenEmpty()
        {
            $integers = new IntegerCollection();
            $iterator = $integers->getIterator();
            $this->assertTrue($iterator instanceof Iterator);
        }
    }
}
