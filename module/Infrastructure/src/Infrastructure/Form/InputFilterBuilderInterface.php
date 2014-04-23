<?php

namespace Infrastructure\Form;

use Zend\InputFilter\InputFilterInterface;

interface InputFilterBuilderInterface
{
    /**
     * @return \Zend\InputFilter\InputFilterInterface
     */
    public function build();
}
