<?php

namespace Infrastructure\Form;

use Infrastructure\Form\InputFilterBuilderInterface;
use Infrastructure\Form\NullInputFilter;

class NullInputFilterBuilder implements InputFilterBuilderInterface
{
    /**
     * @return \Infrastructure\Form\NullInputFilter
     */
    public function build()
    {
        return new NullInputFilter();
    }
}
