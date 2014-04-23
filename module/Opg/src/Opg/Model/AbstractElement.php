<?php

namespace Opg\Model;

use Opg\Model\AbstractVisitor;
use Opg\Model\AcceptVisitorInterface;
use Infrastructure\Library\StrictPropertyAccessTrait;
use Infrastructure\Library\UndefinedPropertyException;
use ReflectionClass;

abstract class AbstractElement implements AcceptVisitorInterface
{
    use StrictPropertyAccessTrait;

    ### PUBLIC METHODS

    /**
     * @param AbstractVisitor $visitor Entry point for the Visitor Pattern
     */
    public function acceptVisitor(
        AbstractVisitor $visitor
    )
    {
        $visitor->visit($this);

        $class = new ReflectionClass(get_class($this));
        $this->introduceVisitorToClassProperties($class, $visitor);
    }

    ###

    /**
     * @param string $message A human readable message explaining the validation error
     */
    public function addValidationErrorMessage(
        $message
    )
    {
        $this->validationErrorMessages[] = $message;
    }

    ###

    public function getValidationErrorMessage()
    {
        return $this->validationErrorMessages;
    }

    ###

    public function isValid()
    {
        return empty($this->validationErrorMessages);
    }

    ### PRIVATE MEMBERS

    /**
     * @var array
     */
    private $validationErrorMessages = array();

    ### PRIVATE METHODS

    private function introduceVisitorToClassProperties(
        ReflectionClass $class,
        AbstractVisitor $visitor
    ){
        $properties = $class->getProperties();
        foreach ($properties as $property){
            $property->setAccessible(true);

            $value = $property->getValue($this);
            if ($value instanceof AcceptVisitorInterface) {

                $value->acceptVisitor($visitor);
            }
        }

        $parentClass = $class->getParentClass();
        if ($parentClass){
            $this->introduceVisitorToClassProperties($parentClass, $visitor);
        } 
    }
}
