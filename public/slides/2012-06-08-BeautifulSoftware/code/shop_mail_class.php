<?php
class Mailer
{
    protected $from = 'shop@example.com';
    protected $bcc  = 'shop-sent@example.com';
    protected $contentType = 'text/plain';
    
    public function setFrom($from) { ... }
    public function setBcc($bcc) { ... }
    public function setContentType($type) { ... }
    public function send($to, $subject, $body)
    {
        $headers = "From: " . $this->from . "\r\n"
                .= "Bcc: " . $this->bcc . "\r\n"
                .= "Content-Type: " . $this->contentType . "\r\n";
        mail($to, $subject, $body, $headers);
    }
}
