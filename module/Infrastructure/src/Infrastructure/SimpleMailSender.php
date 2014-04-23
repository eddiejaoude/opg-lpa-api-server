<?php

namespace Infrastructure;

class SimpleMailSender implements MailSenderInterface
{
    ### PUBLIC METHODS

    /**
     * @param string $subject
     * @param string $message
     * @param string $recipientEmailAddress
     * @param string $senderEmailAddress
     * @return bool True if the message was queued for sending, else False
     */
    public function send(
        $subject,
        $message,
        $recipientEmailAddress,
        $senderEmailAddress
    )
    {
        return @mail(
            $recipientEmailAddress,
            $subject,
            $message,
            ('From: '.$senderEmailAddress)
        );
    }
}
