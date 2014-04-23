<?php

namespace Opg\Model\Serialization\Xml;

use Opg\Model\Element\HealthWelfareApplication;
use Opg\Model\Element\PropertyFinanceApplication;

use Infrastructure\Library\InputException;

use SimpleXMLElement;

class ApplicationsXmlDeserializer
{
    ### PUBLIC MEMBERS

    /**
     * @var string
     */
    const CONTENT_TYPE = 'application/vnd.opg.lpa.applications+xml';

    ///**
    // * @var string
    // */
    //const XSD_LOCATION = 'module/Opg/src/Opg/Model/Serialization/Xml/Xsd/Application.xsd';

    ### PUBLIC METHODS

    /**
     * @param Application[] $applications
     * @param string $hrefTemplate __ID__ will be replaced by the application id
     * @return string Valid XML
     */
    public function serialize(
        array $applications,
        $hrefTemplate
    )
    {
        if (strpos($hrefTemplate, ':id') === false) {
            throw new InputException('$hrefTemplate missing :id substitution token: '.$hrefTemplate);
        }

        $xmlBuilder = new SimpleXMLElement('<applications/>');

        foreach ($applications as $application) {

            if ($application instanceof HealthWelfareApplication) {
                $contentType = HealthWelfareApplicationXmlSerializer::CONTENT_TYPE;
            } elseif ($application instanceof PropertyFinanceApplication) {
                $contentType = PropertyFinanceApplicationXmlSerializer::CONTENT_TYPE;
            } else {
                throw new InputException('Unrecognised Application type: '.get_class($application));
            }

            $applicationMetadata = $application->getMetadata();
            $id = (string) $applicationMetadata->getIdentifier();

            $childNode         = $xmlBuilder->addChild('application');
            $childNode['href'] = str_replace(':id', $id, $hrefTemplate);
            $childNode['type'] = $contentType;
        }

        return $xmlBuilder->asXML();
    }
}
