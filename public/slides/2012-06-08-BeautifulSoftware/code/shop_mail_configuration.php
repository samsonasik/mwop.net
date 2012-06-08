<?php
$config = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
$config->env = "Subdomain';

$mailer = $config->env . 'Mail::send';
call_user_func($mailer, $to, $subject, $body);
