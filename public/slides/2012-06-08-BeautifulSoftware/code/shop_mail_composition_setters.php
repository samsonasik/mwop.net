<?php
class Mailer
{
    protected $headers, $transport;
    
    public function setHeaders(MailHeaders $headers) {
        $this->headers = $headers;
        return $this;
    }
    
    public function getHeaders() {
        return $this->headers;
    }
    
    public function setTransport(MailTransport $transport) {
        $this->transport = $transport;
        return $this;
    }

    /* ... */
}
