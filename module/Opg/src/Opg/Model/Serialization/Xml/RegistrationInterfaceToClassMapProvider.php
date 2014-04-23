<?php

namespace Opg\Model\Serialization\Xml;

class RegistrationInterfaceToClassMapProvider
{
    ### PUBLIC METHODS

    /**
     * @return array
     */
    public function getInterfaceToClassMap()
    {
        return [
            'Opg\Model\Element\ApplicantRoleInterface' => [
                'attorney'          => 'Opg\Model\Element\Attorney',
                'donor'             => 'Opg\Model\Element\Donor',
                'trust-corporation' => 'Opg\Model\Element\TrustCorporation',
            ],
            'Opg\Model\Element\AttorneyDeclarationInterface' => [
                'attorney-declaration'          => 'Opg\Model\Element\AttorneyDeclaration',
                'trust-corporation-declaration' => 'Opg\Model\Element\TrustCorporationDeclaration',
            ]
        ];
    }
}
