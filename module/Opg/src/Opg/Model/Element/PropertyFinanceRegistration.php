<?php

namespace Opg\Model\Element;

use Infrastructure\Library\SerializableInterface;

class PropertyFinanceRegistration extends AbstractRegistration implements SerializableInterface
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
        $additionalInformation
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
    }

    ### PUBLIC METHODS

    public function getSerializableData()
    {
        throw \RuntimeException('Not Implemented');
    }
}
