<?php

namespace Infrastructure\Form;

use Zend\InputFilter\BaseInputFilter;
use Zend\InputFilter\InputInterface;

class RequiredInputFilter extends BaseInputFilter
{
    ### PUBLIC METHODS

    /**
     * @param InputInterface|InputFilterInterface $input
     * @param null|string $name
     * @return \Infrastructure\Form\RequiredInputFilter $this
     */
    public function add(
        $input,
        $name = null
    )
    {
        if ($input instanceof InputInterface
            && ($name === null
                || !in_array($name, $this->getIgnoredInputNames()))) {

            $input->setAllowEmpty(false);
            $input->setRequired(true);
        }

        return parent::add($input, $name);
    }

    ###

    /**
     * @return array
     */
    public function getIgnoredInputNames()
    {
        return $this->ignoredInputNames;
    }

    ###

    /**
     * @return \Infrastructure\Form\RequiredInputFilter $this
     */
    public function setIgnoredInputNames(
        array $ignoredInputNames
    )
    {
        $this->ignoredInputNames = $ignoredInputNames;
        return $this;
    }

    ### PRIVATE MEMBERS

    /**
     * By default, an element named 'submit' will be ignored
     * 
     * @var array
     */
    private $ignoredInputNames = array('submit');
}
