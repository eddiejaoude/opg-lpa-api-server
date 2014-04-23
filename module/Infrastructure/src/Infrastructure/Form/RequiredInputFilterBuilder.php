<?php

namespace Infrastructure\Form;

use Infrastructure\Form\InputFilterBuilderInterface;
use Infrastructure\Form\RequiredInputFilter;

class RequiredInputFilterBuilder implements InputFilterBuilderInterface
{
    /**
     * @return \Infrastructure\Form\RequiredInputFilter
     */
    public function build()
    {
        return new RequiredInputFilter();
    }
}
