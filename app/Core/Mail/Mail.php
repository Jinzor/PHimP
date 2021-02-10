<?php

namespace App\Core\Mail;

use App\Core\App;
use App\Core\Log;
use App\Core\Str;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    /** @var PHPMailer */
    private $phpMailer;

    /** @var string */
    const SESSION_NOSPAM = 'no_spam';

    public function __construct() {
        $this->phpMailer = new PHPMailer(isDev());
        $this->phpMailer->isSMTP();
        $this->phpMailer->isHTML(true);
        $this->phpMailer->CharSet = 'UTF-8';
        $this->phpMailer->Host = getenv('SMTP_HOST');
        $this->phpMailer->Port = getenv('SMTP_PORT');
        $this->phpMailer->Password = getenv('SMTP_PASSWORD');
        $this->phpMailer->Username = getenv('SMTP_USERNAME');
        $this->phpMailer->SMTPAuth = !empty(getenv('SMTP_PASSWORD'));
    }

    /**
     * @param $to
     * @param bool $verifySpam
     * @param bool $verifyDebugMode
     * @return Mail
     * @throws Exception
     */
    public function to($to, $verifySpam = false, $verifyDebugMode = true) {

        if ($verifyDebugMode && App::getInstance()->config()->emailDebug == true && !in_array(Str::after($to, "@"),
                App::getInstance()->config()->emailDebugDomainsWhitelist)) {
            $this->addAddress('garbage@boreal-business.net', false);
            return $this;
        }

        if (is_array($to)) {
            foreach ($to as $adr) {
                $this->addAddress($adr, $verifySpam);
            }
        } else {
            $this->addAddress($to, $verifySpam);
        }

        return $this;
    }

    /**
     * @param string $file
     * @param string $filename
     * @param string $mimetype
     * @param string $disposition
     * @return $this
     */
    public function attach(string $file, $filename = '', $mimetype = '', $disposition = MailAttachment::DISPOSITION_CONTENT_ATTACHMENT) {
        try {
            if (file_exists($file)) {
                Log::logs("Set attachment as file $file");
                $this->phpMailer->addAttachment($file, $filename, PHPMailer::ENCODING_BASE64, $mimetype, $disposition);
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return $this;
    }

    /**
     * @param string $data
     * @param string $filename
     * @param string $mimetype
     * @param string $disposition
     * @return $this
     */
    public function attachData(
        string $data,
        $filename = '',
        $mimetype = '',
        $disposition = MailAttachment::DISPOSITION_CONTENT_ATTACHMENT
    ) {
        try {
            if (!empty($data)) {
                Log::logs("Set attachment as data");
                $this->phpMailer->addStringAttachment($data, $filename, PHPMailer::ENCODING_BASE64, $mimetype, $disposition);
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return $this;
    }

    /**
     * @param string $address
     * @param bool $verifySpam
     * @return bool
     * @throws Exception
     */
    protected function addAddress(string $address, $verifySpam = true) {
        if ($verifySpam && self::isAlreadySentTo($address)) {
            throw new Exception('Mail déjà envoyé à l\'adresse ' . $address);
        }
        if (Str::checkEmail($address)) {
            $_SESSION[self::SESSION_NOSPAM][$address] = time() + 30;
            $this->phpMailer->addAddress($address);
            return true;
        }
        throw new Exception('Incorrect mail address ' . $address);
    }

    /**
     * Si un mail a déjà été envoyé récemment (- 30min) à la même adresse
     *
     * @param $email
     * @return bool
     */
    private static function isAlreadySentTo($email) {
        return isset($_SESSION[self::SESSION_NOSPAM]) && key_exists($email,
                $_SESSION[self::SESSION_NOSPAM]) && $_SESSION[self::SESSION_NOSPAM][$email] > time();
    }

    /**
     * @param $from
     * @param $fromName
     * @param $replyTo
     * @return Mail
     */
    public function from($from, $fromName = null, $replyTo = null) {
        try {
            if (!is_null($from)) {
                $this->phpMailer->setFrom($from);
            }

            if (!is_null($fromName)) {
                $this->phpMailer->FromName = $fromName;
            }

            $this->phpMailer->addReplyTo(!is_null($replyTo) ? $replyTo : App::getInstance()->getReplyEmail());
        } catch (Exception $e) {
            Log::error($e);
        }
        return $this;
    }

    /**
     * @param $subject
     * @param $body
     * @return bool
     */
    public function send(string $subject, string $body = null) {
        try {
            if (!empty($this->phpMailer->getToAddresses())) {
                $this->phpMailer->Subject = $subject;
                if (!is_null($body)) {
                    $this->phpMailer->Body = $body;
                }
                $this->phpMailer->AltBody = strip_tags($this->phpMailer->Body);

                Log::info('Envoi du mail à ' . $this->phpMailer->getToAddresses()[0][0]);
                return $this->phpMailer->send();
            }
        } catch (Exception $e) {
            Log::critical($e->getCode() . ' | ' . $e->getMessage());
        }

        return false;
    }

    /**
     * @param $body
     * @return Mail
     */
    public function setBody($body) {
        $this->phpMailer->Body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->phpMailer->Body;
    }

}
