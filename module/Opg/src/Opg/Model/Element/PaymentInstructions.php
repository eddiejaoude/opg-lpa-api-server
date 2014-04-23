<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class PaymentInstructions extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $paymentMethod
     * @param string $instructions
     */
    public function __construct(
        $paymentMethod,
        $instructions
    )
    {
        $this->paymentMethod = $paymentMethod;
        $this->instructions = $instructions;
    }

    ### PUBLIC METHODS

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    ###

    public function getInstructions()
    {
        return $this->instructions;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $instructions;
}
