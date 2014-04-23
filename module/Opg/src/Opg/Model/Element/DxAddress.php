<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class DxAddress extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $dxNumber
     * @param string $dxExchange
     */
    public function __construct(
        $dxNumber,
        $dxExchange
    )
    {
        $this->dxNumber = $dxNumber;
        $this->dxExchange = $dxExchange;
    }

    ### PUBLIC METHODS

    public function getDxNumber()
    {
        return $this->dxNumber;
    }

    public function getDxExchange()
    {
        return $this->dxExchange;
    }

    ### PRIVATE MEMBERS

    /**
     * @var string
     */
    private $dxNumber;

    /**
     * @var string
     */
    private $dxExchange;
}
