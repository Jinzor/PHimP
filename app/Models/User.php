<?php

namespace App\Models;

use App\Core\App;
use App\Core\Auth;
use App\Core\Dbg;
use App\Core\Request;
use App\Core\Security;
use App\Core\Sql;

class User extends Root
{
    const TBNAME = 'utilisateurs';
    const TBTOKEN = 'utilisateurs_token';
    const LIBELLE = 'utilisateur';
    const TOKEN_KEY = '21y9BlPomqVZxz6p5pKwMFtH9GddwULlapx4Swma5JB=';

    public static $columns = [
        'id',
        'nom',
        'email',
        'societe',
        'utilisateur',
        'mdp',
        'role',
        'creation',
        'actif',
    ];

    var $nom = '';
    var $email = '';
    var $utilisateur = '';
    var $mdp = '';
    var $role = '';
    var $creation = 0;
    var $last = 0;
    var $actif = 1;

    /**
     * Vérifie si l'identifiant renseigné existe
     *
     * @param string $identifiant
     * @return bool
     */
    public static function existingIdentifier($identifiant) {
        if (!empty(trim($identifiant))) {
            $res = Sql::select(self::TBNAME, ['utilisateur' => $identifiant]);
            if ($res && $res->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifie si l'email renseigné existe.
     *
     * @param string $email
     * @return bool
     */
    public static function existingEmail($email) {
        if (!empty(trim($email))) {
            $res = Sql::select(self::TBNAME, ['email' => $email]);
            if ($res && $res->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getUrl() {
        if (App::getInstance()->auth()->hasRole(Auth::ROLE_ADMIN)) {
            return route('admin.userView', $this->id);
        }
        return '';
    }

    /**
     * @return bool|int
     */
    public function save() {
        if ($this->id == 0) {
            $this->creation = time();
        }
        return parent::save();
    }

    /**
     * Vérifie si le mot de passe est correct.
     *
     * @param $requestedPassword
     * @return bool
     */
    public function checkPassword($requestedPassword) {
        if (Security::checkPassword($requestedPassword, $this->mdp)) {
            return true;
        }
        return false;
    }

    /**
     * @param $password
     * @return bool|int
     */
    public function changePassword($password) {
        $this->mdp = Security::crypt($password);
        return $this->save();
    }

    /**
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function saveData(array $data) {
        if (peutGererUtilisateurs(App::getInstance()->auth())) {

            if ($this->id == 0) { // Création
                if (isset($data['password']) && !empty($data['password'])) {
                    $mdp = $data['password'];
                    if (isset($data['mdp_confirm'])) {
                        $mdp_confirm = $data['mdp_confirm'];
                        if ($mdp != $mdp_confirm) {
                            throw new \Exception('Les mots de passe saisies ne correspondent pas.');
                        }
                    }
                    $data['mdp'] = Security::crypt($mdp);
                } else {
                    $data['mdp'] = null;
                }

                if (User::existingEmail($data['email']) || User::existingIdentifier($data['utilisateur'])) {
                    throw new \Exception('Un utilisateur avec cet identifiant/email existe déjà.');
                }
            }

            return parent::saveData($data);
        }
        throw new \Exception(Request::ERROR_NOT_AUTHORIZED);
    }

    /**
     * Génère un nouveau token pour l'utilisateur
     *
     * @return string
     */
    public function generateToken() {
        $key = Security::generateRandomToken(32);
        if ($this->mdp == null) {
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
    public static function checkToken($token, $uId = null) {
        list ($key, $userId, $expire, $mac) = explode(':', $token);

        if ($expire > 0 && $expire < time()) {
            Dbg::logs('Token expiré');
            return false;
        }

        $usr = new User($userId);
        if (($uId != null && $uId != $userId) || $usr->actif == 0) {
            Dbg::logs('Token non correspondant à l\'utilisateur');
            return false;
        }

        if ($expire == 0 && !is_null($usr->mdp) && !empty($usr->mdp)) {
            Dbg::logs('Password already set');
            return false;
        }

        if ($usr->id > 0 && hash_equals(hash_hmac('sha256', $key . ':' . $userId . ':' . $expire, self::TOKEN_KEY), $mac)) {
            return true;
        }

        return false;
    }
}
