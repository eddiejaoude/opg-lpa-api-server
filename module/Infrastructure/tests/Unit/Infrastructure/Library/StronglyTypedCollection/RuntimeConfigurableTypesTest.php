<?php

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptNativeTypesTest
{
    use Infrastructure\Library\StronglyTypedCollection;

    class ExampleConfigurableCollection extends StronglyTypedCollection
    {
        private $allowedElementType;

        public function getAllowedElementType()
        {
            return $this->allowedElementType;
        }

        public function setAllowedElementType($type)
        {
            $this->allowedElementType = $type;
        }
    }
}

namespace Tests\Unit\Infrastructure\Library\StronglyTypedCollection
{
    use Tests\Unit\Infrastructure\Library\StronglyTypedCollection\AcceptNativeTypesTest\ExampleConfigurableCollection;

    use DateTime;
    use PHPUnit_Framework_TestCase as TestCase;
    use stdClass;

    class RuntimeConfigurableTypesTest extends TestCase
    {
        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testConfiguredArrayType()
        {
            $arrays = new ExampleConfigurableCollection();
            $arrays->setAllowedElementType('array');

            $arrays[] = array(123);
            $arrays[] = array(456);

            $this->assertCount(2, $arrays);
            $this->assertEquals(array(123), $arrays[0]);
            $this->assertEquals(array(456), $arrays[1]);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testConfiguredArrayTypeIsSafe()
        {
            $arrays = new ExampleConfigurableCollection();
            $arrays->setAllowedElementType('array');
            $arrays[] = 'array';
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testConfiguredBooleanType()
        {
            $booleans = new ExampleConfigurableCollection();
            $booleans->setAllowedElementType('boolean');

            $booleans[] = true;
            $booleans[] = false;

            $this->assertCount( 2,     $booleans);
            $this->assertEquals(true,  $booleans[0]);
            $this->assertEquals(false, $booleans[1]);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testConfiguredBooleanTypeIsSafe()
        {
            $booleans = new ExampleConfigurableCollection();
            $booleans->setAllowedElementType('boolean');
            $booleans[] = 'true';
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testConfiguredDoubleType()
        {
            $doubles = new ExampleConfigurableCollection();
            $doubles->setAllowedElementType('double');

            $doubles[] = 1.0;
            $doubles[] = 1.2;

            $this->assertCount( 2,   $doubles);
            $this->assertEquals(1,   $doubles[0]);
            $this->assertEquals(1.2, $doubles[1]);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testConfiguredDoubleTypeIsSafe()
        {
            $doubles = new ExampleConfigurableCollection();
            $doubles->setAllowedElementType('double');
            $doubles[] = '1.2';
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testConfiguredIntegerType()
        {
            $integers = new ExampleConfigurableCollection();
            $integers->setAllowedElementType('integer');

            $integers[] = 123;
            $integers[] = 456;

            $this->assertCount( 2,   $integers);
            $this->assertEquals(123, $integers[0]);
            $this->assertEquals(456, $integers[1]);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testConfiguredIntegerTypeIsSafe()
        {
            $integers = new ExampleConfigurableCollection();
            $integers->setAllowedElementType('integer');
            $integers[] = '1';
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testConfiguredResourceType()
        {
            $resources = new ExampleConfigurableCollection();
            $resources->setAllowedElementType('resource');

            $resources[] = opendir(getcwd());
            $resources[] = opendir(getcwd().'/..');

            $this->assertCount(2, $resources);
            $this->assertTrue(is_resource($resources[0]));
            $this->assertTrue(is_resource($resources[1]));
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testConfiguredResourceTypeIsSafe()
        {
            $resources = new ExampleConfigurableCollection();
            $resources->setAllowedElementType('resource');
            $resources[] = 'resource';
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testConfiguredStringType()
        {
            $strings = new ExampleConfigurableCollection();
            $strings->setAllowedElementType('string');

            $strings[] = 'ABC';
            $strings[] = 'DEF';

            $this->assertCount(  2,    $strings);
            $this->assertEquals('ABC', $strings[0]);
            $this->assertEquals('DEF', $strings[1]);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testConfiguredStringTypeIsSafe()
        {
            $strings = new ExampleConfigurableCollection();
            $strings->setAllowedElementType('string');
            $strings[] = 123;
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         */
        public function testConfiguredClassType()
        {
            $dates = new ExampleConfigurableCollection();
            $dates->setAllowedElementType('\DateTime');

            $dates[] = new DateTime();
            $dates[] = new DateTime();

            $this->assertCount(2, $dates);
            $this->assertTrue($dates[0] instanceof DateTime);
            $this->assertTrue($dates[1] instanceof DateTime);
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testConfiguredClassTypeIsSafe()
        {
            $dates = new ExampleConfigurableCollection();
            $dates->setAllowedElementType('\DateTime');
            $dates[] = new stdClass();
        }

        /**
         * @covers Infrastructure\Library\StronglyTypedCollection
         * @expectedException Infrastructure\Library\CollectionElementNotSupportedException
         */
        public function testUnconfigured()
        {
            $configurableCollection = new ExampleConfigurableCollection();
            $configurableCollection[] = 123;
        }
    }
}
