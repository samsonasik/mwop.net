<?php
$message = new Message();
$headers = new MessageHeaders();
$headers->addHeader('From', $from)
        ->addHeader('Content-Type', 'text/html');
$message->setTo($to)
        ->setSubject($subject)
        ->setBody($body)
        ->setHeaders($headers);
$transport = new SmtpTransport($config->transport);
$transport->send($message);
