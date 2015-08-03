<?php

// ==================================================================
// PHPMailer Wrapper Class
// Used for autoload and setting default mailer
// ==================================================================

// Use example:
// For more docs visit https://github.com/PHPMailer/PHPMailer

// $mail = new TipyMailer;

// $mail->From = 'from@example.com';
// $mail->FromName = 'Mailer';
// $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
// $mail->addAddress('ellen@example.com');               // Name is optional
// $mail->addReplyTo('info@example.com', 'Information');
// $mail->addCC('cc@example.com');
// $mail->addBCC('bcc@example.com');

// $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
// $mail->isHTML(true);                                  // Set email format to HTML

// $mail->Subject = 'Here is the subject';
// $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

// $mail->send();


require_once(__DIR__.'/../PHPMailer/class.phpmailer.php');

class TipyMailer extends PHPMailer {

    function __construct() {
        // call PHPMailer constructor with throw exceptions param
        parent::__construct(true);
        // set sendmail as default mailer
        $app = Tipy::getInstance();
        $path = $app->config->get('mail_sendmail_path');
        ini_set('sendmail_path', $path);
        $this->isSendmail();
    }

}

