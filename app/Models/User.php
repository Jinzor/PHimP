<?php

namespace App\Models;

use App\Core\Log;
use App\Core\Security;

class User extends Model
{
    const TBNAME = 'users';
    const TBTOKEN = 'users_token';
    const TOKEN_KEY = '21y9BlPomqVZxz6p5pKwMFtH9GddwULlapx4Swma5JB=';

    public static $columns = [
        'id',
        'name',
        'email',
        'username',
        'password',
        'permission',
        'created_at',
        'last',
        'active',
    ];

    var $name = '';
    var $email = '';
    var $username = '';
    var $password = '';
    var $permission = '';
    var $created_at;
    var $last;
    var $active = 1;

    /**
     * Vérifie si l'identifiant renseigné existe
     *
     * @param string $username
     * @return bool
     */
    public static function existingIdentifier($username) {
        if (!empty(trim($username))) {
            return !!User::find(['username' => $username]);
        }

        return false;
    }

    /**
     * Vérifie si l'email renseigné existe.
     *
     * @param string $email
     * @return bool
     */
    public static function existingEmail(string $email) {
        if (!empty(trim($email))) {
            return !!User::find(['email' => $email]);
        }

        return false;
    }

    /**
     * Vérifie si le mot de passe est correct.
     *
     * @param string $requestedPassword
     * @return bool
     */
    public function checkPassword(string $requestedPassword) {
        if (Security::checkPassword($requestedPassword, $this->password)) {
            return true;
        }

        return false;
    }

    /**
     * @param $password
     * @return bool|int
     */
    public function changePassword($password) {
        $this->password = Security::crypt($password);
        return $this->save();
    }

    /**
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function saveData(array $data) {
        if ($this->id == 0) { // Création
            if (isset($data['password']) && !empty($data['password'])) {
                $pass = $data['password'];
                if (isset($data['confirm'])) {
                    $confirm = $data['confirm'];
                    if ($pass != $confirm) {
                        throw new \Exception('Les mots de passe saisies ne correspondent pas.');
                    }
                }
                $data['password'] = Security::crypt($pass);
            } else {
                $data['password'] = null;
            }

            if (User::existingEmail($data['email']) || User::existingIdentifier($data['username'])) {
                throw new \Exception('Un utilisateur avec cet identifiant/email existe déjà.');
            }
        }

        return parent::saveData($data);
    }

    /**
     * Génère un nouveau token pour l'utilisateur
     *
     * @return string
     */
    public function generateToken() {
        $key = Security::randomToken(32);
        if ($this->password == null) {
            $expire = 0;
        } else {
            $expire = (time() + 1800); // 30 minutes
        }
        $str = $key . ':' . $this->id . ':' . $expire;
        return $str . ':' . hash_hmac('sha256', $str, self::TOKEN_KEY);
    }

    /**
     * Vérifie si le token renseigné est correct.
     *
     * @param string $token
     * @param int $uId (optional)
     * @return bool
     */
    public static function checkToken(string $token, $uId = null) {
        list ($key, $userId, $expire, $mac) = explode(':', $token);

        if ($expire > 0 && $expire < time()) {
            Log::logs('Token expiré');
            return false;
        }

        $usr = new User($userId);
        if (($uId != null && $uId != $userId) || $usr->active == 0) {
            Log::logs('Token non correspondant à l\'utilisateur');
            return false;
        }

        if ($expire == 0 && !is_null($usr->password) && !empty($usr->password)) {
            Log::logs('Password already set');
            return false;
        }

        if ($usr->id > 0 && hash_equals(hash_hmac('sha256', $key . ':' . $userId . ':' . $expire, self::TOKEN_KEY), $mac)) {
            return true;
        }

        return false;
    }
}
