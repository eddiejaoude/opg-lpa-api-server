<?php

namespace Infrastructure\Form;

use Zend\InputFilter\InputFilterInterface;

interface InputFilterFactoryInterface
{
    /**
     * @return \Zend\InputFilter\InputFilterInterface
     */
    public function create();
}
