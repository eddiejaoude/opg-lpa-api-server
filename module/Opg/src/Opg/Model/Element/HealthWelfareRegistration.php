<?php

namespace Opg\Model\Element;

use Infrastructure\Library\SerializableInterface;

class HealthWelfareRegistration extends AbstractRegistration implements SerializableInterface
{
    ### CONSTRUCTOR

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
        $additionalInformation,
        DonorDeclaration $lifeSustainingAuthorityDeclaration
    )
    {
        parent::__construct(
            $applicants,
            $donorDeclaration,
            $attorneyDeclarations,
            $certificateProviderDeclarations,
            $notifications,
            $correspondent,
            $donorDiscountClaim,
            $paymentInstructions,
            $onlinePayment,
            $paymentResult,
            $previousDonorApplication,
            $additionalInformation
        );

        $this->lifeSustainingAuthorityDeclaration = $lifeSustainingAuthorityDeclaration;
    }

    ### PUBLIC METHODS

    public function getLifeSustainingAuthorityDeclaration()
    {
        return $this->lifeSustainingAuthorityDeclaration;
    }

    ###

    public function getSerializableData()
    {
        throw \RuntimeException('Not Implemented');
    }

    ### PRIVATE MEMBERS

    /**
     * @var \Opg\Model\Element\DonorDeclaration
     */
    private $lifeSustainingAuthorityDeclaration;
}
