<?php

namespace Infrastructure\Form;

use Zend\InputFilter\InputFilter;

class InputFilterFactory implements InputFilterFactoryInterface
{
    /**
     * @return \Zend\InputFilter\InputFilter
     */
    public function create()
    {
        return new InputFilter();
    }
}
