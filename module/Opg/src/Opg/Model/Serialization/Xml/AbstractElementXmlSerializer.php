<?php

namespace Opg\Model\Serialization\Xml;

use Opg\Model\AbstractElement;
use Opg\Model\AbstractElementCollection;

use DateTime;
use Infrastructure\Library\CamelCaseToHyphenatedWordsConversionStrategy;
use Infrastructure\Library\SerializableInterface;
use Infrastructure\Library\XmlSerializerInterface;
use ReflectionClass;

class AbstractElementXmlSerializer implements XmlSerializerInterface
{
    ### PUBLIC METHODS

    public function serialize(
        SerializableInterface $application
    )
    {
        return $this->serializeObject($application);
    }

    ### PUBLIC METHODS

    private function serializeObject(
        $object
    )
    {
        $xml = '';

        $camelCaseToHyphenatedWordsConversionStrategy = new CamelCaseToHyphenatedWordsConversionStrategy();

        $class = new ReflectionClass($object);
        $methods = $class->getMethods();

        $ignoredMethodNames = array(
            'getMetadata',
            'getSerializableData',
            'getValidationErrorMessage',
            'isValid',
        );

        foreach ($methods as $method){
            $methodName = $method->getName();

            if ((substr($methodName, 0, 3) != 'get'
                 && substr($methodName, 0, 2) != 'is')
                || in_array($methodName, $ignoredMethodNames)) {

                continue;
            }

            if (substr($methodName, 0, 3) == 'get') {
                $name = substr($methodName, 3);
            } elseif (substr($methodName, 0, 2) == 'is') {
                $name = $methodName;
            }

            $name  = $camelCaseToHyphenatedWordsConversionStrategy->convert($name);
            $value = $method->invoke($object);

            if ($value instanceof AbstractElement) {

                $xml .= ('<'.$name.'>');
                $xml .= $this->serializeObject($value);
                $xml .= ('</'.$name.'>');

            } elseif ($value instanceof AbstractElementCollection) {

                if (count($value) > 0) {

                    $xml .= ('<'.$name.'>');

                    foreach ($value as $element) {

                        $elementType = get_class($element);
                        $elementName = substr($elementType, strrpos($elementType, '\\')+1);
                        $elementName = $camelCaseToHyphenatedWordsConversionStrategy->convert($elementName);

                        $xml .= ('<'.$elementName.'>');
                        $xml .= $this->serializeObject($element);
                        $xml .= ('</'.$elementName.'>');
                    }

                    $xml .= ('</'.$name.'>');

                } else {

                    $xml .= ('<'.$name.'/>');
                }

            } else {

                if ($value instanceof DateTime) {
                    $value = $value->format(DateTime::ATOM);
                }

                if (is_scalar($value)
                    || method_exists($value, '__toString')) {

                    if (!empty($value)) {

                        $xml .= ('<'.$name.'>');
                        $xml .= htmlspecialchars((string) $value);
                        $xml .= ('</'.$name.'>');

                    } else {

                        $xml .= ('<'.$name.'/>');
                    }
                }
            }
        }

        return $xml;
    }
}
