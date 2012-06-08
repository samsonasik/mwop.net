<?php
$mailer  = new Mailer(new SmtpTransport);
$headers = new MailHeaders();
// or $headers = $mailer->getHeaders();

$headers['From']         = $config->from;
$headers['Bcc']          = $config->bcc;
$headers['Content-Type'] = 'text/html';
$mailer->setHeaders($headers) // if instantiated separately
       ->send($to, $subject, $body);
