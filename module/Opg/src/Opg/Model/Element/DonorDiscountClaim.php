<?php

namespace Opg\Model\Element;

use Opg\Model\AbstractElement;

class DonorDiscountClaim extends AbstractElement
{
    ### CONSTRUCTOR

    /**
     * @param string $isApplyingForDiscount
     * @param string $isDamageAwardRecipient
     * @param string $isEarningLowIncome
     * @param string $isReceivingBenefits
     */
    public function __construct(
        $isApplyingForDiscount,
        $isDamageAwardRecipient,
        $isEarningLowIncome,
        $isReceivingBenefits
    )
    {
        $this->isApplyingForDiscount = $isApplyingForDiscount;
        $this->isDamageAwardRecipient = $isDamageAwardRecipient;
        $this->isEarningLowIncome = $isEarningLowIncome;
        $this->isReceivingBenefits = $isReceivingBenefits;
    }

    ### PUBLIC METHODS

    public function isApplyingForDiscount()
    {
        return $this->isApplyingForDiscount;
    }

    public function isDamageAwardRecipient()
    {
        return $this->isDamageAwardRecipient;
    }

    public function isEarningLowIncome()
    {
        return $this->isEarningLowIncome;
    }

    public function isReceivingBenefits()
    {
        return $this->isReceivingBenefits;
    }

    ### PRIVATE MEMBERS

    /**
     * @hint YesOrNo
     * @var string
     */
    private $isApplyingForDiscount;

    /**
     * @hint YesOrNo
     * @var string
     */
    private $isDamageAwardRecipient;

    /**
     * @hint YesOrNo
     * @var string
     */
    private $isEarningLowIncome;

    /**
     * @hint YesOrNo
     * @var string
     */
    private $isReceivingBenefits;
}
