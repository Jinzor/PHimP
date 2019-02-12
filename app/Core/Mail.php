<?php

namespace App\Core;

use App\Models\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    private $phpMailer;
    const SESSION_NOSPAM = 'no_spam';
    const DEFAULT_FROM_NAME = '';
    const DEFAULT_FROM_ADDRESS = '';

    public function __construct() {
        $this->phpMailer = new PHPMailer(isDev());
        $this->phpMailer->isSMTP();
        $this->phpMailer->isHTML(true);
        $this->phpMailer->CharSet = 'UTF-8';
        $this->phpMailer->Host = "";
    }

    /**
     * Mail d'initialisation du mot de passe.
     * TODO template
     *
     * @param User $user
     * @return bool
     */
    public static function initializePassword(User $user) {
        $token = $user->generateToken();
        $link = site_url() . 'creation?u=' . $user->id . '&token=' . $token;
        if (!empty($token)) {
            $content = 'Bonjour,<br><br>
            Un compte associé à votre adresse email a été créé.<br>
            Pour créer votre mot de passe, merci de cliquer sur le lien suivant : <a href="' . $link . '">Connexion</a><br><br>
            Cordialement<br><br>';
            $content .= '<small>Ceci est un email automatique, merci de ne pas y répondre directement.</small>';
            return (new Mail())
                ->to($user->email)
                ->from(self::DEFAULT_FROM_ADDRESS, '')// TODO name
                ->send("Création de votre compte", $content);
        }
        Dbg::error('Mail : Erreur token');
        return false;
    }

    public static function changePassword(User $user) {
        $token = $user->generateToken();
        $link = site_url() . 'creation?u=' . $user->id . '&token=' . $token;
        if (!empty($token)) {
            $content = 'Bonjour ' . $user->nom . ',<br><br>
            Pour modifier votre mot de passe, merci de cliquer sur le lien suivant : <a href="' . $link . '">Modifier le mot de passe</a><br>
            Ce lien est valide pendant 30 minutes.<br><br>
            Cordialement<br><br>';
            $content .= '<small>Ceci est un email automatique, merci de ne pas y répondre directement.</small>';
            return (new Mail())
                ->to($user->email)
                ->from(self::DEFAULT_FROM_ADDRESS)// TODO name
                ->send("Modification de votre mot de passe", $content);
        }
        Dbg::error('Mail : Erreur token');
        return false;
    }

    /**
     * @param $to
     * @param bool $verifySpam
     * @return Mail
     */
    private function to($to, $verifySpam = true) {
        try {
            if (isDev()) {
                $this->addAddress('', false); // TODO define a dev mail
                return $this;
            }

            if (is_array($to)) {
                foreach ($to as $adr) {
                    $this->addAddress($adr, $verifySpam);
                }
            } else {
                $this->addAddress($to, $verifySpam);
            }
        } catch (\Exception $e) {
            Dbg::critical($e->getMessage());
        }

        return $this;
    }

    /**
     * @param string $address
     * @param bool $verifySpam
     * @return bool
     * @throws \Exception
     */
    private function addAddress(string $address, $verifySpam = true) {
        if ($verifySpam && self::isAlreadySentTo($address)) {
            throw new \Exception('Mail déjà envoyé à l\'adresse ' . $address);
        }
        if (Str::checkEmail($address)) {
            $_SESSION[self::SESSION_NOSPAM][$address] = time() + 1800;
            $this->phpMailer->addAddress($address);
            return true;
        }
        throw new \Exception('Incorrect mail address ' . $address);
    }

    /**
     * Si un mail a déjà été envoyé récemment (- 30min) à la même adresse
     *
     * @param $email
     * @return bool
     */
    public static function isAlreadySentTo($email) {
        return isset($_SESSION[self::SESSION_NOSPAM]) && key_exists($email,
                $_SESSION[self::SESSION_NOSPAM]) && $_SESSION[self::SESSION_NOSPAM][$email] > time();
    }

    /**
     * @param $from
     * @param $fromName
     * @param $replyTo
     * @return Mail
     */
    private function from($from, $fromName = null, $replyTo = null) {
        try {
            if (!is_null($from)) {
                $this->phpMailer->setFrom($from);
            }

            if (!is_null($fromName)) {
                $this->phpMailer->FromName = self::DEFAULT_FROM_NAME;
            }

            $this->phpMailer->addReplyTo(!is_null($replyTo) ? $replyTo : self::DEFAULT_FROM_ADDRESS);
        } catch (Exception $e) {
            Dbg::critical($e->getMessage());
        }
        return $this;
    }

    /**
     * @param $subjet
     * @param $body
     * @return bool
     */
    private function send(string $subjet, string $body) {

        try {
            if (!empty($this->phpMailer->getToAddresses())) {
                $this->phpMailer->Subject = $subjet;
                $this->phpMailer->Body = $body;

                Dbg::info('Envoi du mail à ' . $this->phpMailer->getToAddresses()[0][0]);
                return $this->phpMailer->send();
            }
        } catch (Exception $e) {
            Dbg::critical($e->getMessage());
        }

        return false;
    }

}