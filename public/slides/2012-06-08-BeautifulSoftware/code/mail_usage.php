<?php
$headers = "From: " . $config->from . "\r\n"
        .= "Bcc: " . $config->bcc . "\r\n";
mail($to, $subject, $body, $headers);
