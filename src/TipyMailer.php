<?php
/**
 * Tipy Mailer
 *
 * @package tipy
 */

require_once(__DIR__.'/../vendor/PHPMailer/class.phpmailer.php');

/**
 * Tipy Mailer uses PHPMailer to send messages
 *
 * {@link https://github.com/PHPMailer/PHPMailer}
 *
 * <code>
 * $mail = new TipyMailer;
 *
 * $mail->From = 'from@example.com';
 * $mail->FromName = 'Mailer';
 * $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
 * $mail->addAddress('ellen@example.com');               // Name is optional
 * $mail->addReplyTo('info@example.com', 'Information');
 * $mail->addCC('cc@example.com');
 * $mail->addBCC('bcc@example.com');
 *
 * $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
 * $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
 * $mail->isHTML(true);                                  // Set email format to HTML
 *
 * $mail->Subject = 'Here is the subject';
 * $mail->Body = 'This is the HTML message body <b>in bold!</b>';
 * $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
 *
 * $mail->send();
 * </code>
 *
  */
class TipyMailer extends PHPMailer {

    /**
     * @internal
     */
    public function __construct() {
        // call PHPMailer constructor with throw exceptions param
        parent::__construct(true);
        $app = TipyApp::getInstance();
        $path = $app->config->get('mail_sendmail_path');
        ini_set('sendmail_path', $path);
        $this->isSendmail();
    }
}
