<?php

namespace Infrastructure;

interface MailSenderInterface
{
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
    );
}
