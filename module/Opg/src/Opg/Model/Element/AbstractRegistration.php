<?php

namespace Opg\Model\Element;

use Infrastructure\Library\InvariantException;
use Infrastructure\Library\UndefinedPropertyValueException;
use Opg\Model\AbstractElement;
use Opg\Model\Element\RegistrationMetadata;

abstract class AbstractRegistration extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $paymentResult
     * @param string $additionalInformation
     */
    public function __construct(
        ApplicantRoleCollection $applicants,
        DonorDeclaration $donorDeclaration,
        AttorneyDeclarationCollection $attorneyDeclarations,
        CertificateProviderDeclarationCollection $certificateProviderDeclarations,
        NotificationCollection $notifications,
        Correspondent $correspondent,
        DonorDiscountClaim $donorDiscountClaim,
        PaymentInstructions $paymentInstructions,
        OnlinePayment $onlinePayment,
        $paymentResult,
        PreviousDonorApplication $previousDonorApplication,
        $additionalInformation
    )
    {
        $this->applicants = $applicants;
        $this->donorDeclaration = $donorDeclaration;
        $this->attorneyDeclarations = $attorneyDeclarations;
        $this->certificateProviderDeclarations = $certificateProviderDeclarations;
        $this->notifications = $notifications;
        $this->correspondent = $correspondent;
        $this->donorDiscountClaim = $donorDiscountClaim;
        $this->paymentInstructions = $paymentInstructions;
        $this->onlinePayment = $onlinePayment;
        $this->paymentResult = $paymentResult;
        $this->previousDonorApplication = $previousDonorApplication;
        $this->additionalInformation = $additionalInformation;
    }

    ### PUBLIC METHODS

    public function getApplicants()
    {
        return $this->applicants;
    }

    ###

    public function getDonorDeclaration()
    {
        return $this->donorDeclaration;
    }

    ###

    public function getAttorneyDeclarations()
    {
        return $this->attorneyDeclarations;
    }

    ###

    public function getCertificateProviderDeclarations()
    {
        return $this->certificateProviderDeclarations;
    }

    ###

    public function getNotifications()
    {
        return $this->notifications;
    }

    ###

    public function getCorrespondent()
    {
        return $this->correspondent;
    }

    ###

    public function getDonorDiscountClaim()
    {
        return $this->donorDiscountClaim;
    }

    ###

    public function getPaymentInstructions()
    {
        return $this->paymentInstructions;
    }

    ###

    public function getPreviousDonorApplication()
    {
        return $this->previousDonorApplication;
    }

    ###

    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    ###

    public function getPaymentResult()
    {
        return $this->paymentResult;
    }
    
    ###

    public function getOnlinePayment()
    {
        return $this->onlinePayment;
    }
    
    ###
    
    /**
     * @throws UndefinedPropertyValueException
     */
    public function getMetadata()
    {
        if ($this->metadata === null) {
            throw new UndefinedPropertyValueException('$metadata has not been set');
        }

        return $this->metadata;
    }

    ###

    /**
     * @throws InvariantException
     */
    public function setMetadata(
        RegistrationMetadata $metadata
    )
    {
        if ($this->metadata !== null) {
            throw new InvariantException('$metadata has already been set');
        }

        $this->metadata = $metadata;
    }

    ### PRIVATE MEMBERS

    /**
     * @var \Opg\Model\Element\ApplicantRoleCollection
     */
    private $applicants;

    /**
     * @var \Opg\Model\Element\DonorDeclaration
     */
    private $donorDeclaration;

    /**
     * @var \Opg\Model\Element\AttorneyDeclarationCollection
     */
    private $attorneyDeclarations;

    /**
     * @var \Opg\Model\Element\CertificateProviderDeclarationCollection
     */
    private $certificateProviderDeclarations;

    /**
     * @var \Opg\Model\Element\NotificationCollection
     */
    private $notifications;

    /**
     * @var \Opg\Model\Element\CorrespondentRoleInterface
     */
    private $correspondent;

    /**
     * @var \Opg\Model\Element\DonorDiscountClaim
     */
    private $donorDiscountClaim;

    /**
     * @var \Opg\Model\Element\PaymentInstructions
     */
    private $paymentInstructions;

    /**
     * @var string
     */
    private $paymentResult;
    
    /**
     * @var \Opg\Model\Element\OnlinePayment
     */
    private $onlinePayment;
    
    /**
     * @var \Opg\Model\Element\PreviousDonorApplication
     */
    private $previousDonorApplication;

    /**
     * @var string
     */
    private $additionalInformation;

    /**
     * @var RegistrationMetadata
     */
    private $metadata;
}
