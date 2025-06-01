<?php

namespace Burdock\SendMail;

use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SendMail
{
    public $mailer = null;

    /**
     * SendMail constructor.
     * @param string $dsn 'smtp://account@email.domain:password@smtp.host.fqdn:port'
     */
    public function __construct(string $dsn=null)
    {
        if ($dsn) {
            $transport = Transport::fromDsn($dsn);
            $this->mailer = new Mailer($transport);
        }
    }

    public static function getSmtp($dsn)
    {
        $transport = Transport::fromDsn($dsn);
        $sendmail = new SendMail();
        $sendmail->mailer = new Mailer($transport);
        return $sendmail;
    }

    public static function getGmail($user, $pass)
    {
        $transport = new GmailSmtpTransport($user, $pass);
        $sendmail = new SendMail();
        $sendmail->mailer = new Mailer($transport);
        return $sendmail;
    }

    /**
     * @param $params = [
     *    'from' => [ 'email' => 'sender@email.domain', 'name' => 'Full Text Name' ],
     *    'tos'  => [[ 'email' => 'hoge@burdock.io', 'name' => 'Flower Garden' ]],
     *    'bccs' => [],
     *    'ccs'  => [[ 'email' => 'fuga@burdock.io', 'name' => 'Forest Flower' ]],
     *    'replyTo' => 'sender@email.domain',
     *    'subject' => 'メールの件名',
     *    'text' => 'メールの本文',
     *    'attachments' => [
     *        [
     *            'path' => 'https://burdock.io/data/20200209.pdf',
     *            'filename' => '20200209.pdf',
     *            'mime' => 'application/pdf'
     *        ]
     *    ]
     * ]
     * @return void
     * @throws TransportExceptionInterface
     */
    public function send($params): void
    {
        $email = self::createMessage($params);
        $this->mailer->send($email);
    }

    private static function createMessage($params): \Symfony\Component\Mime\Email
    {
        $email = new Email();
        $address = new Address($params['from']['email'], $params['from']['name']);
        $email->from($address);
        if (isset($params['tos'])) {
            foreach ($params['tos'] as $to) {
                $address = new Address($to['email'], $to['name']);
                $email->addTo($address);
            }
        }
        if (isset($params['ccs'])) {
            foreach ($params['ccs'] as $cc) {
                $address = new Address($cc['email'], $cc['name']);
                $email->addCc($address);
            }
        }
        if (isset($params['bccs'])) {
            foreach ($params['bccs'] as $bcc) {
                $address = new Address($bcc['email'], $bcc['name']);
                $email->addBcc($address);
            }
        }
        if (isset($params['replyTo'])) {
            $email->replyTo($params['replyTo']);
        }
        $email->subject($params['subject']);
        $email->text($params['text']);
        if (isset($params['attachments'])) {
            foreach ($params['attachments'] as $attachment) {
                $email->attachFromPath($attachment['path']);
            }
        }
        return $email;
    }
}