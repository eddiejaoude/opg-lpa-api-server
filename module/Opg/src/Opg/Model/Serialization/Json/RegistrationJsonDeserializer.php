<?php
namespace Opg\Model\Serialization\Json;

use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlDeserializer;
use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlDeserializer;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Security\IdentityFactoryInterface;

class RegistrationJsonDeserializer extends BaseJsonDeserializer
{
    public function __construct(
        HealthWelfareRegistrationXmlDeserializer $healthWelfareXmlDeserializer,
        PropertyFinanceRegistrationXmlDeserializer $propertyFinanceXmlDeserializer,
        IdentifierFactoryInterface $identiferFactory,
        IdentityFactoryInterface $identityFactory
    )
    {
        $this->healthWelfareXmlDeserializer = $healthWelfareXmlDeserializer;
        $this->propertyFinanceXmlDeserializer = $propertyFinanceXmlDeserializer;
        
        $this->identifierFactory = $identiferFactory;
        $this->identityFactory = $identityFactory;
    }
        
    public function deserialize($data)
    {
        return parent::getDocument($data, 'registration');
    }
    
}

?>