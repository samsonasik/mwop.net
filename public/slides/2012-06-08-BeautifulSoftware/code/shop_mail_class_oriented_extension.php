<?php
class SubdomainMail extends ShopMail
{
    protected static $from = 'shop@subdomain.example.com';
    protected static $bcc  = 'shop-sent@subdomain.example.com';
}
