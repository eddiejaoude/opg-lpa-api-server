<?php

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AllowedTypeConfigurationTest {

    use Infrastructure\Library\StronglyTypedCollection;

    class DummyClassType {}

    class DummyClassTypeCollection extends StronglyTypedCollection {}

    class MissingClassTypeCollection extends StronglyTypedCollection {}

    class Moose extends StronglyTypedCollection
    {
        public function getAllowedElementType()
        {
            return 'Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AllowedTypeConfigurationTest\DummyClassType';
        }
    }

    class Reindeer extends StronglyTypedCollection {}

}

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection {

    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AllowedTypeConfigurationTest\DummyClassType;
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AllowedTypeConfigurationTest\DummyClassTypeCollection;
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AllowedTypeConfigurationTest\MissingClassTypeCollection;
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AllowedTypeConfigurationTest\Moose;
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AllowedTypeConfigurationTest\Reindeer;

    use PHPUnit_Framework_TestCase as TestCase;

    class AllowedTypeConfigurationTest extends TestCase
    {
        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testAllowedTypeIsInferredFromClassNameSuffix()
        {
            $collection = new DummyClassTypeCollection();

            $this->assertEquals(
                'Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AllowedTypeConfigurationTest\DummyClassType',
                $collection->getAllowedElementType(),
                "Where the class name ends with 'Collection' the preceding part of the name is used."
            );

            $collection[] = new DummyClassType();

            $this->assertCount(1, $collection);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testAllowedTypeIsUsedFromMethodOverride()
        {
            $collection = new Moose();

            $this->assertEquals(
                'Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AllowedTypeConfigurationTest\DummyClassType',
                $collection->getAllowedElementType()
            );

            $collection[] = new DummyClassType();

            $this->assertCount(1, $collection);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementTypeNotDiscoverableException
         */
        public function testAllowedTypeCannotBeFound()
        {
            $collection = new MissingClassTypeCollection();
            $collection->getAllowedElementType();
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementTypeNotDiscoverableException
         */
        public function testAllowedTypeCannotBeInferredFromClassName()
        {
            $collection = new Reindeer();
            $collection->getAllowedElementType();
        }
    }
}
