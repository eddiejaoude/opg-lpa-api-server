<?php

namespace Infrastructure\Form;

use Zend\Form\FormInterface;

interface FormBuilderInterface
{
    /**
     * @return \Zend\Form\FormInterface
     */
    public function build();
}
