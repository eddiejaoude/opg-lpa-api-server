<?php

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection\RejectInvalidNativeTypesTest
{
    use Infrastructure\Library\StronglyTypedCollection;

    class NullCollection extends StronglyTypedCollection
    {
        public function getAllowedElementType()
        {
            return 'NULL';
        }
    }

    class UnkownTypeCollection extends StronglyTypedCollection
    {
        public function getAllowedElementType()
        {
            return 'unknown type';
        }
    }
}

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection
{
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\RejectInvalidNativeTypesTest\NullCollection;
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\RejectInvalidNativeTypesTest\UnkownTypeCollection;

    use PHPUnit_Framework_TestCase as TestCase;

    class RejectInvalidNativeTypesTest extends TestCase
    {
        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testNullsAreRejected()
        {
            $nulls = new NullCollection();
            $nulls['a'] = null;
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testUnknownTypesAreRejected()
        {
            $unkownTypes = new UnkownTypeCollection();
            $unkownTypes['a'] = 123;
        }
    }
}
