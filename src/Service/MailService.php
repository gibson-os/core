<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Exception;
use GibsonOS\Core\Dto\Mail;
use GibsonOS\Core\Exception\MailException;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    public function send(Mail $mail): void
    {
        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = $mail->getHost();
            $mailer->Port = $mail->getPort();
            $mailer->SMTPAuth = true;
            $mailer->Username = $mail->getUsername();
            $mailer->Password = $mail->getPassword();

            $smtpEncryption = $mail->getEncryption();

            if ($smtpEncryption !== null) {
                $mailer->SMTPSecure = $smtpEncryption->value;
            }

            $from = $mail->getFrom();
            $mailer->setFrom($from->getAddress(), $from->getName());

            $replyTo = $mail->getReplyTo();

            if ($replyTo !== null) {
                $mailer->addReplyTo($replyTo->getAddress(), $replyTo->getName());
            }

            foreach ($mail->getTo() as $to) {
                $mailer->addAddress($to->getAddress(), $to->getName());
            }

            foreach ($mail->getCc() as $cc) {
                $mailer->addCC($cc->getAddress(), $cc->getName());
            }

            foreach ($mail->getBcc() as $bcc) {
                $mailer->addBCC($bcc->getAddress(), $bcc->getName());
            }

            $mailer->isHTML();
            $mailer->Subject = $mail->getSubject();
            $mailer->Body = $mail->getHtml();
            $mailer->AltBody = $mail->getPlain();

            $mailer->send();
        } catch (Exception $exception) {
            throw new MailException(
                sprintf('Mail could not be sent. Error: %s', $mailer->ErrorInfo),
                previous: $exception,
            );
        }
    }
}
