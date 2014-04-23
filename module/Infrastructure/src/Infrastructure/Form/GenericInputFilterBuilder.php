<?php

namespace Infrastructure\Form;

use Infrastructure\Form\GenericFormDefinitionInterface as FormDefinition;
use Infrastructure\Form\InputFilterBuilderInterface;
use Infrastructure\Form\InputFilterFactoryInterface;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputInterface;
use Zend\InputFilter\InputFilterInterface;

class GenericInputFilterBuilder /* implements InputFilterBuilderInterface */
{
    ### COLLABORATORS

    /**
     * @var \Zend\InputFilter\Factory
     */
    private $inputFactory;

    /**
     * @var \Infrastructure\Form\InputFilterFactoryInterface
     */
    private $inputFilterFactory;

    ### CONSTRUCTOR

    public function __construct(
        InputFactory $inputFactory,
        InputFilterFactoryInterface $inputFilterFactory
    )
    {
        $this->inputFactory = $inputFactory;
        $this->inputFilterFactory = $inputFilterFactory;
    }

    ### PUBLIC METHODS

    /**
     * @return \Zend\InputFilter\InputFilterInterface
     */
    public function build(
        FormDefinition $formDefinition
    )
    {
        $inputFilter = $this->inputFilterFactory->create();
        $this->addInputs($formDefinition, $inputFilter);
        return $inputFilter;
    }

    ### PROTECTED METHODS

    protected function addInputs(
        FormDefinition $formDefinition,
        InputFilterInterface $inputFilter
    )
    {
        $elementDefinitions = $formDefinition->getElementDefinitions();
        foreach ($elementDefinitions as $name => $definition) {

            if ($definition instanceof InputInterface
                || $definition instanceof InputFilterInterface) {

                $inputFilter->add($definition);
                continue;
            }

            list(, , $required, ) = $definition;
            $inputFilter->add(
                $this->inputFactory->createInput(
                    array(
                        'name'     => $name,
                        'required' => $required,
                    )
                )
            );
        }
    }
}
