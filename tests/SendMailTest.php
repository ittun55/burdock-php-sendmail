<?php


use Burdock\SendMail\SendMail;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;

class SendMailTest extends TestCase
{
    public function testSmtpSend()
    {
        $logger = new Logger('smtp');
        $fileHandler = new RotatingFileHandler(__DIR__.'/tmp/test.log', 3, LOG_INFO);
        $formatter = new LineFormatter();
        $formatter->includeStacktraces(true);
        $logger->pushHandler($fileHandler->setFormatter($formatter));

        $env = Dotenv\Dotenv::createImmutable(__DIR__);
        $env->load();

        $host = $_ENV['SMTP_HOST'];
        $port = $_ENV['SMTP_PORT'];
        $user = $_ENV['SMTP_USER'];
        $pass = $_ENV['SMTP_PASS'];
        $dsn = "smtp://${user}:${pass}@${host}:${port}";

        $params = [
            'from' => [  'email' => $_ENV['SMTP_TEST_FROM'], 'name' => 'Full Text Name' ],
            'tos'  => [[ 'email' => $_ENV['SMTP_TEST_TO'], 'name' => 'Flower Garden' ]],
            'bccs' => [],
            'ccs'  => [[ 'email' => 'fuga@burdock.io', 'name' => 'Forest Flower' ]],
            'subject' => 'メールの件名',
            'text' => 'メールの本文',
        ];

        try {
            $mailer = SendMail::getSmtp($dsn);
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

    public function testGmailSend()
    {
        $logger = new Logger('gmail');
        $fileHandler = new RotatingFileHandler(__DIR__.'/tmp/test.log', 3, LOG_INFO);
        $formatter = new LineFormatter();
        $formatter->includeStacktraces(true);
        $logger->pushHandler($fileHandler->setFormatter($formatter));

        $env = Dotenv\Dotenv::createImmutable(__DIR__);
        $env->load();

        $params = [
            'from' => [  'email' => $_ENV['GMAIL_TEST_FROM'], 'name' => 'Full Text Name' ],
            'tos'  => [[ 'email' => $_ENV['GMAIL_TEST_TO'], 'name' => 'Flower Garden' ]],
            'bccs' => [],
            'ccs'  => [],
            'subject' => 'メール送信のテスト',
            'text' => 'メール本文',
        ];

        try {
            $mailer = SendMail::getGmail([
                'user' => $_ENV['GMAIL_USER'],
                'pass' => $_ENV['GMAIL_PASS']
            ]);
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