<?php

namespace Infrastructure\Form;

interface GenericFormDefinitionInterface
{
    /**
     * @return array
     */
    public function getElementDefinitions();

    ###

    /**
     * @return string
     */
    public function getFormName();

    ###

    /**
     * @return string
     */
    public function getFormTitle();
}
