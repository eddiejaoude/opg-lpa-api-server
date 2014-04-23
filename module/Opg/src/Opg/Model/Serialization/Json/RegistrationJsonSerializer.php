<?php
namespace Opg\Model\Serialization\Json;

use Opg\Model\Serialization\Xml\HealthWelfareRegistrationXmlSerializer;
use Opg\Model\Serialization\Xml\PropertyFinanceRegistrationXmlSerializer;
use Opg\Model\Element\HealthWelfareRegistration;
use Opg\Model\Element\PropertyFinanceRegistration;
use LSS\XML2Array;


class RegistrationJsonSerializer extends BaseJsonSerializer
{
    public function __construct(
        HealthWelfareRegistrationXmlSerializer $healthWelfareXmlSerializer,
        PropertyFinanceRegistrationXmlSerializer $propertyFinanceXmlSerializer
    )
    {
        $this->healthWelfareXmlSerializer = $healthWelfareXmlSerializer;
        $this->propertyFinanceXmlSerializer = $propertyFinanceXmlSerializer;
    }
    
    public function serialize($registration)
    {
        if ($registration instanceof HealthWelfareRegistration) {
            $registrationXml = $this->healthWelfareXmlSerializer->serialize($registration);
        } elseif ($registration instanceof PropertyFinanceRegistration) {
            $registrationXml = $this->propertyFinanceXmlSerializer->serialize($registration);
        } else {
            throw new Exception('Unsupported Registration');
        }
    
        $registrationArray = XML2Array::createArray($registrationXml);
    	return $registrationArray;
//        return json_encode(
//            [
//                "registration" => $registrationArray,
//            ]
//        );
    }
    
}

?>