<?php

namespace Infrastructure\Library;

use Infrastructure\Library\DomDocumentLoader;
use Infrastructure\Library\StringConversionStrategyInterface;
use Infrastructure\Library\XmlDeserializerException;
use Infrastructure\Library\XmlDeserializerInterface;

use ArrayAccess;
use DOMNode;
use DOMNodeList;
use ReflectionClass;

class XmlDeserializer implements XmlDeserializerInterface
{
    ### COLLABORATORS

    /**
     * @var DomDocumentLoader
     */
    private $domDocumentLoader;

    /**
     * @var StringConversionStrategyInterface
     */
    private $nameConversionStrategy;

    ### CONSTRUCTOR

    public function __construct(
        DomDocumentLoader $domDocumentLoader,
        StringConversionStrategyInterface $nameConversionStrategy
    )
    {
        $this->domDocumentLoader      = $domDocumentLoader;
        $this->nameConversionStrategy = $nameConversionStrategy;
    }

    ### PUBLIC METHODS

    /**
     * @param string $classOrInterfaceName Class or Interface to deserialize into
     * @param string $xmlDocument Valid XML document
     * @param array $interfaceToClassMap Class implementations of Interfaces to be deserialized
     * @throws XmlDeserializerException
     */
    public function deserialize(
        $classOrInterfaceName,
        $xmlDocument,
        array $interfaceToClassMap = array()
    )
    {   
        $document = $this->domDocumentLoader->load($xmlDocument);

        return $this->deserializeNode(
            $classOrInterfaceName,
            $document->documentElement,
            $interfaceToClassMap
        );
    }

    ### PRIVATE METHODS

    private function addCollectionElements(
        $instance,
        DOMNodeList $nodes,
        array $interfaceToClassMap
    )
    {
        $elementType = $instance->getAllowedElementType();

        foreach ($nodes as $node) {
            if (substr($node->nodeName, 0, 1) != '#') {

                $element = $this->deserializeNode(
                    $elementType, 
                    $node, 
                    $interfaceToClassMap
                );

                $instance[] = $element;
            }
        }
    }

    ###

    private function buildConstructorParameterToClassNameMap(
        ReflectionClass $class
    )
    {
        $constructor = $class->getConstructor();
        if (!$constructor) {
           return array();
        }

        $map = array();
        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {

            $parameterName = $parameter->getName();
            $parameterClass = $parameter->getClass();
            if ($parameterClass === null) {

                $map[$parameterName] = null;
            } else {
                $map[$parameterName] = $parameterClass->getName();
            }
        }

        return $map;
    }

    ###

    private function buildNodeToParameterNameMap(
        DOMNodeList $nodes
    )
    {
        $map = array();

        foreach ($nodes as $node) {
            if (substr($node->nodeName, 0, 1) != '#') {

                $roleAttribute = $node->attributes->getNamedItem('role');
                if ($roleAttribute) {
                    $parameterName = $roleAttribute->value;
                } else {
                    $parameterName = $node->nodeName;
                }

                $map[$node->nodeName] = $this->nameConversionStrategy->convert($parameterName);
            }
        }

        return $map;
    }

    ###

    /**
     * @param string $classOrInterfaceName
     */
    private function deserializeNode(
        $classOrInterfaceName,
        DOMNode $node,
        array $interfaceToClassMap
    )
    {
        if (class_exists($classOrInterfaceName)) {
            $className = $classOrInterfaceName;
        } else {
            $className = null;
            if (interface_exists($classOrInterfaceName)) {
                $interfaceName = $classOrInterfaceName;

                if (isset($interfaceToClassMap[$interfaceName])) {

                    if (is_array($interfaceToClassMap[$interfaceName])) {

                        $nodeName = $node->nodeName;

                        if (isset($interfaceToClassMap[$interfaceName][$nodeName])) {
                            $className = $interfaceToClassMap[$interfaceName][$nodeName];
                        }
                    }

                    if (is_string($interfaceToClassMap[$interfaceName])) {
                        $className = $interfaceToClassMap[$interfaceName];
                    }
                }
            }
        }

        if (!class_exists($className)) {
            throw new XmlDeserializerException('Class or Interface does not exist: '.$classOrInterfaceName);
        }

        $class = new ReflectionClass($className);
        $constructorParameterValues = $this->getConstructorParameterValuesFromNodes($class, $node->childNodes, $interfaceToClassMap);
        $instance = $class->newInstanceArgs($constructorParameterValues);
        if ($instance instanceof StronglyTypedCollection) {
            $this->addCollectionElements($instance, $node->childNodes, $interfaceToClassMap);
        }

        return $instance;
    }

    ###

    private function getConstructorParameterValuesFromNodes(
        ReflectionClass $class,
        DOMNodeList $nodes,
        array $interfaceToClassMap
    )
    {
        $values = array();

        $constructorParameterToClassNameMap = $this->buildConstructorParameterToClassNameMap($class);
        $constructorParameterNames = array_keys($constructorParameterToClassNameMap);

        $nodeToParameterNameMap = $this->buildNodeToParameterNameMap($nodes);

        $parameterNodeDifferences = array_diff(array_keys($constructorParameterToClassNameMap), $nodeToParameterNameMap);
//        if (!empty($parameterNodeDifferences)) {
//        	throw new XmlDeserializerException(print_r(array_keys($constructorParameterToClassNameMap),1));
//            throw new XmlDeserializerException('Constructor parameters and node names do not match for class: '.$class->getName());
//        }

        foreach ($nodes as $node) {
            if (substr($node->nodeName, 0, 1) != '#') {

                $parameterName = $nodeToParameterNameMap[$node->nodeName];
                if (!array_key_exists($parameterName, $constructorParameterToClassNameMap)) {
                    continue;
                }

                $className = $constructorParameterToClassNameMap[$parameterName];
                if ($className === null) {

                    if ($node->hasChildNodes()
                        && ($node->childNodes->length > 1
                            || $node->childNodes->item(0)->nodeType != XML_TEXT_NODE)) {
                        throw new XmlDeserializerException('Constructor parameters and node types do not match for class: '.$class->getName());
                    }

                    $values[$parameterName] = $node->nodeValue;

                } else {

                    $values[$parameterName] = $this->deserializeNode(
                        $className,
                        $node,
                        $interfaceToClassMap
                    );
                }
            }
        }

        $orderedValues = array();
        foreach ($constructorParameterNames as $parameterName) {
            $orderedValues[$parameterName] = (isset($values[$parameterName])? $values[$parameterName]:"");
        }

        return $orderedValues;
    }
}
