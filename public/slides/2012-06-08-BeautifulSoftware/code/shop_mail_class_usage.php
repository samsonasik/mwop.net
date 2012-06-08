<?php
$mailer = new Mailer();
$mailer->setFrom($config->from)
       ->setBcc($config->bcc)
       ->setContentType('text/html');
$mailer->send($to, $subject, $body);
