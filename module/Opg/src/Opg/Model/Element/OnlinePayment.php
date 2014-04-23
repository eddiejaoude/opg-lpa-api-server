<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class OnlinePayment extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $opgPaymentRef
     * @param string $worldpayRef
     */
    public function __construct(
        $opgPaymentRef,
        $worldpayRef
    )
    {
        $this->opgPaymentRef = $opgPaymentRef;
        $this->worldpayRef = $worldpayRef;
    }

    ### PUBLIC METHODS

    public function getOpgPaymentRef()
    {
        return $this->opgPaymentRef;
    }

    ###

    public function getWorldpayRef()
    {
        return $this->worldpayRef;
    }
    
    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $opgPaymentRef;

    /**
     * @var string
     */
    private $worldpayRef;
    
}
