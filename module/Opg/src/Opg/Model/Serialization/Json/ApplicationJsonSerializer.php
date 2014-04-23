<?php
namespace Opg\Model\Serialization\Json;

use Opg\Model\Serialization\Xml\HealthWelfareApplicationXmlSerializer;
use Opg\Model\Serialization\Xml\PropertyFinanceApplicationXmlSerializer;
use Opg\Model\Element\HealthWelfareApplication;
use Opg\Model\Element\PropertyFinanceApplication;
use LSS\XML2Array;


class ApplicationJsonSerializer extends BaseJsonSerializer
{
    public function __construct(
        HealthWelfareApplicationXmlSerializer $healthWelfareXmlSerializer,
        PropertyFinanceApplicationXmlSerializer $propertyFinanceXmlSerializer
    )
    {
        $this->healthWelfareXmlSerializer = $healthWelfareXmlSerializer;
        $this->propertyFinanceXmlSerializer = $propertyFinanceXmlSerializer;
    }
        
    public function serialize($application)
    {
        if ($application instanceof HealthWelfareApplication) {
            $applicationXml = $this->healthWelfareXmlSerializer->serialize($application);
        } elseif ($application instanceof PropertyFinanceApplication) {
            $applicationXml = $this->propertyFinanceXmlSerializer->serialize($application);
        } else {
            throw new Exception('Unsupported Application');
        }
        
        $applicationArray = XML2Array::createArray($applicationXml);
        return $applicationArray;
//        return json_encode(
//            [
//                "application" => $applicationArray,
//            ]
//        );
    }
}

?>