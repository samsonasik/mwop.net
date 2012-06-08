<?php
function shop_mail($to, $subject, $body, $shop = 'original')
{
    switch ($shop) {
        case: 'subdomain':
            $from = 'shop@subdomain.example.com';
            $bcc  = 'shop-sent@subdomain.example.com';
            break;
        case: 'original':
        default:
            $from = 'shop@example.com';
            $bcc  = 'shop-sent@example.com';
            break;
    }

    $headers = "From: $from\r\n"
            .= "Bcc: $bcc\r\n";
    mail($to, $subject, $body, $headers);
}
