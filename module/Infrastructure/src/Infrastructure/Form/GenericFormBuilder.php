<?php

namespace Infrastructure\Form;

use Infrastructure\Form\FormBuilderInterface;
use Infrastructure\Form\GenericFormDefinitionInterface as FormDefinition;
use Infrastructure\Form\GenericInputFilterBuilder as InputFilterBuilder;
use Infrastructure\Form\StatefulForm;
use Infrastructure\Library\PersistentDataInterface as PersistentData;

use Zend\Form\Element\Csrf;
use Zend\Form\Element\Select;
use Zend\Form\Element\Submit;
use Zend\Form\ElementInterface;
use Zend\Form\FormInterface;

class GenericFormBuilder /* implements FormBuilderInterface */
{
    ### COLLABORATORS

    /**
     * @var \Infrastructure\Form\GenericInputFilterBuilder
     */
    private $inputFilterBuilder;

    ### CONSTRUCTOR

    public function __construct(
        InputFilterBuilder $inputFilterBuilder
    )
    {
        $this->inputFilterBuilder = $inputFilterBuilder;
    }

    ### PUBLIC MEMBERS

    const CSRF_TOKEN_NAME     = 'security';
    const EMPTY_SELECT_OPTION = 'Please Select --';
    const SUBMIT_NAME         = 'submit';
    const SUBMIT_VALUE        = 'Submit';

    ### PUBLIC METHODS

    /**
     * @return \Zend\Form\FormInterface
     */
    public function build(
        FormDefinition $formDefinition,
        PersistentData $persistentData
    )
    {
        // @todo look to decouple StatefulForm (rather use a FormFactory and Zend\Form\FormInterface)
        $form = new StatefulForm($persistentData);

        $this->addCsrfToken($form);
        $this->addElements($form, $formDefinition);
        $this->addSubmitElement($form);

        $form->setName($formDefinition->getFormName());
        $form->resetStatefulData();

        $inputFilter = $this->inputFilterBuilder->build($formDefinition);
        $form->setInputFilter($inputFilter);

        return $form;
    }

    ### PROTECTED METHODS

    protected function addElements(
        FormInterface $form,
        FormDefinition $formDefinition
    )
    {
        $elementDefinitions = $formDefinition->getElementDefinitions();
        foreach ($elementDefinitions as $name => $elementDefinition) {

            if ($elementDefinition instanceof ElementInterface) {

                $form->add($elementDefinition);
                continue;
            }

            list($label, $type, $required, $options) = $elementDefinition;

            if ($type == 'select') {

                $select = new Select($name);
                $select->setEmptyOption(self::EMPTY_SELECT_OPTION);
                $select->setLabel($label);
                $select->setValueOptions($options);

                $form->add($select);

            } else {

                $form->add(
                    array(
                        'name'       => $name,
                        'attributes' => array('type'  => $type ),
                        'options'    => array('label' => $label),
                    )
                );
            }
        }
    }

    ### PRIVATE METHODS

    private function addCsrfToken(
        FormInterface $form
    )
    {
        $csrf = new Csrf(self::CSRF_TOKEN_NAME);
        $form->add($csrf);
    }

    ###

    private function addSubmitElement(
        FormInterface $form
    )
    {
        $submit = new Submit(self::SUBMIT_NAME);
        $submit->setValue(self::SUBMIT_VALUE);
        $form->add($submit);
    }
}
