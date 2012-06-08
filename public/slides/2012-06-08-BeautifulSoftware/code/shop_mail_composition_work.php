<?php
class Mailer
{
    protected $headers, $transport;
    
    public function __construct(MailTransport $transport)
    {
        $this->setTransport($transport);
        $this->setHeaders(new MailHeaders());
    }
    public function send($to, $subject, $body)
    {
        $this->transport->send(
            $to, $subject, $body, 
            $this->headers->toString()
        );
    }
}
