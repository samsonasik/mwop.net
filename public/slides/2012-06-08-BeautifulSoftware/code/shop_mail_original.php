<?php
function shop_mail($to, $subject, $body)
{
    $headers = "From: shop@example.com\r\n"
            .= "Bcc: shop-sent@example.com\r\n";
    mail($to, $subject, $body, $headers);
}
