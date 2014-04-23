<?php

namespace Infrastructure\Library;

use Infrastructure\Library\CollectionElementNotFoundException;
use Infrastructure\Library\CollectionElementNotSupportedException;
use Infrastructure\Library\CollectionElementTypeNotDiscoverableException;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
* @todo add a constructor to accept an array of elements
*/
abstract class StronglyTypedCollection implements ArrayAccess, Countable, IteratorAggregate
{
    ### PUBLIC MEMBERS

    const COLLECTION_CLASS_NAME_SUFFIX = 'Collection';
    const INTERFACE_NAME_SUFFIX        = 'Interface';

    ### PUBLIC METHODS

    /**
     * @return int The number of elements in this collection
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @return string The type of the element allowed in this collection.
     *                The default behaviour is to return a class name matching the name of the
     *                Collection class with the "Collection" suffix removed.
     *                When overriding this method should return one of:
     *                - Any valid class name
     *                - Any valid interface name
     *                - Any php variable type except "NULL" and "unknown type"
     *                The type returned is not allowed to change between calls.
     * @throws CollectionElementTypeNotDiscoverableException
     */
    public function getAllowedElementType()
    {
        $collectionClassName = get_class($this);
        $collectionClassNameSuffix = substr($collectionClassName, -strlen(self::COLLECTION_CLASS_NAME_SUFFIX));
        if ($collectionClassNameSuffix == self::COLLECTION_CLASS_NAME_SUFFIX) {

            $elementClassName = substr($collectionClassName, 0, -strlen(self::COLLECTION_CLASS_NAME_SUFFIX));
            $elementInterfaceName = $elementClassName.self::INTERFACE_NAME_SUFFIX;

            if (interface_exists($elementInterfaceName)) {
                return $elementInterfaceName;
            }

            if (class_exists($elementClassName)) {
                return $elementClassName;
            }
        }

        throw new CollectionElementTypeNotDiscoverableException();
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    /**
     * @return mixed
     * @throws CollectionElementNotFoundException
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new CollectionElementNotFoundException();
        }

        return $this->elements[$offset];
    }

    /**
     * @throws CollectionElementNotSupportedException
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->isValidElementType($value)) {
            throw new CollectionElementNotSupportedException();
        }

        if ($offset === null) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }

    /**
     * @throws CollectionElementNotFoundException
     */
    public function offsetUnset($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new CollectionElementNotFoundException();
        }

        unset($this->elements[$offset]);
    }

    ### PRIVATE MEMBERS

    /**
     * @var string[]
     */
    private static $allowedNativeTypeNames = array(
        'array',
        'boolean',
        'double',
        'integer',
        'resource',
        'string'
    );

    /**
     * @var array
     */
    private $elements = array();

    ### PRIVATE METHODS

    /**
     * @return bool
     */
    private function isValidElementType($value)
    {
        $allowedElementType = $this->getAllowedElementType();

        if ($allowedElementType === null
            || $allowedElementType == 'NULL'
            || $allowedElementType == 'unknown type') {
            return false;
        }

        if (in_array($allowedElementType, self::$allowedNativeTypeNames)) {
            return (gettype($value) === $allowedElementType);
        }

        return ($value instanceof $allowedElementType);
    }
}
