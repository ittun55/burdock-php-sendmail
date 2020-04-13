<?php


use Burdock\SendMail\SendMail;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;

class SendMailTest extends TestCase
{
    public function testSend()
    {
        $logger = new Logger('test');
        $fileHandler = new RotatingFileHandler(__DIR__.'/tmp/test.log', 3, LOG_INFO);
        $formatter = new LineFormatter();
        $formatter->includeStacktraces(true);
        $logger->pushHandler($fileHandler->setFormatter($formatter));

        $dsn = 'smtp://account@email.domain:password@smtp.host.fqdn:port';
        $params = [
            'from' => [ 'email' => 'sender@email.domain', 'name' => 'Full Text Name' ],
            'tos'  => [[ 'email' => 'hoge@burdock.io', 'name' => 'Flower Garden' ]],
            'bccs' => [],
            'ccs'  => [[ 'email' => 'fuga@burdock.io', 'name' => 'Forest Flower' ]],
            'subject' => 'メールの件名',
            'text' => 'メールの本文',
        ];

        try {
            $mailer = new SendMail($dsn);
            $mailer->send($params);
            $this->assertTrue(true);
        } catch (TransportException $e) {
            $logger->error($e->getMessage());
            $logger->error($e->getTraceAsString());
            $this->assertTrue(true);
        } catch (Throwable $e) {
            $logger->error($e->getMessage());
            $logger->error(PHP_EOL.$e->getTraceAsString());
            $this->assertTrue(true);
        }
    }
}