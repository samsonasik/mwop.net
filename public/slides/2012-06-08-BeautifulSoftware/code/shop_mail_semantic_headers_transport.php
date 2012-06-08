<?php
interface MailHeaders
{
    public function addHeader($header, $value);
    public function toString();
}

interface MailTransport
{
    public function send(MailMessage $message);
}
