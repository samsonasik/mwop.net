<?php
interface MailMessage
{
    public function setTo($to);
    public function setSubject($subject);
    public function setBody($body);
    public function setHeaders(MailHeaders $headers);
    
    public function getTo();
    public function getSubject();
    public function getBody();
    public function getHeaders();
}
