<?php

namespace Opg\Model\Serialization\Xml;

class ApplicationInterfaceToClassMapProvider
{
    ### PUBLIC METHODS

    /**
     * @return array
     */
    public function getInterfaceToClassMap()
    {
        return [
            'Opg\Model\Element\AttorneyInterface' => [
                'attorney'          => 'Opg\Model\Element\Attorney',
                'trust-corporation' => 'Opg\Model\Element\TrustCorporation',
            ],
        ];
    }
}
