<?php
interface MailTransport
{
    public function send($to, $subject, $body, $headers);
}

class MailHeaders extends ArrayObject
{
    public function toString()
    {
        $headers = '';
        foreach ($this as $header => $value) {
            $headers .= $header . ': ' . $value . "\r\n";
        }
        return $headers;
    }
}
