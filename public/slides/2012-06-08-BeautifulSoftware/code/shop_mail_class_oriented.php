<?php
class ShopMail
{
    protected static $from = 'shop@example.com';
    protected static $bcc  = 'shop-sent@example.com';
    
    public static function send($to, $subject, $body) 
    {
        $headers = "From: " . static::$from . "\r\n"
                .= "Bcc: " . static::$bcc . "\r\n";
        mail($to, $subject, $body, $headers);
    }
}
