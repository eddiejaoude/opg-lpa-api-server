<?php

namespace Infrastructure\Form;

use Infrastructure\Library\NullObjectInterface;
use Zend\InputFilter\BaseInputFilter;

class NullInputFilter extends BaseInputFilter implements NullObjectInterface
{
}
