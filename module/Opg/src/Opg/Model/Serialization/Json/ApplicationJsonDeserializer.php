<?php
namespace Opg\Model\Serialization\Json;

use Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlDeserializer;
use Opg\Model\Serialization\Xml\PropertyFinanceApplicationXmlDeserializer;
use Infrastructure\Library\IdentifierFactoryInterface;
use Infrastructure\Security\IdentityFactoryInterface;

class ApplicationJsonDeserializer extends BaseJsonDeserializer
{
    public function __construct(
        HealthWelfareApplicationXmlDeserializer $healthWelfareXmlDeserializer,
        PropertyFinanceApplicationXmlDeserializer $propertyFinanceXmlDeserializer,
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
        return parent::getDocument($data, 'application');
    }
    
}

?>