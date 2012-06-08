<?php
define('MYENV', 'Subdomain');

$mailer = MYENV . 'Mail::send';
call_user_func($mailer, $to, $subject, $body);
