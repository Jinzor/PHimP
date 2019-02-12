<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    const SESSION_AUTH_ID = 'auth_id';
    const SESSION_AUTH_NOM = 'auth_nom';
    const SESSION_AUTH_UTILISATEUR = 'auth_utilisateur';
    const SESSION_AUTH_EMAIL = 'auth_email';
    const SESSION_AUTH_ROLE = 'auth_role';
    const COOKIE_REMEMBERME = 'rememberme';
    const AUTH_KEY = '9ab8IiAuCdRcpqGs1TpGFxXNOUgwZCqyzuLW7qrcSFM=';

    const ROLE_ADMIN = 'administrateur';

    static $roles = [
        self::ROLE_ADMIN,
        // TODO add roles
    ];

    /**
     * @var User
     */
    var $user = null;

    public function __construct() {
        if ($this->isAuth()) {
            $this->user = new User($_SESSION[self::SESSION_AUTH_ID]);
        } else {
            if (php_sapi_name() == "cli") {
                $this->user = new User(-1);
            }
        }
    }

    /**
     * @return bool
     */
    public function isAuth() {
        if (isset($_SESSION[self::SESSION_AUTH_ID]) && $_SESSION[self::SESSION_AUTH_ID] > 0) {
            return true;
        }
        return false;
    }

    public function logout() {
        $_SESSION[self::SESSION_AUTH_ID] = null;
        $_SESSION[self::SESSION_AUTH_NOM] = null;
        $_SESSION[self::SESSION_AUTH_UTILISATEUR] = null;
        $_SESSION[self::SESSION_AUTH_EMAIL] = null;
        $_SESSION[self::SESSION_AUTH_ROLE] = null;
        setcookie(self::COOKIE_REMEMBERME, null, 1);
        $this->user = null;
    }

    /**
     * @param $username
     * @param $password
     * @param bool $remember
     * @return false|User
     */
    public function login($username, $password, $remember = false) {
        if (Str::contains($username, '@')) {
            $res = Sql::select(User::TBNAME, ['email' => $username, 'actif' => 1]);
        } else {
            $res = Sql::select(User::TBNAME, ['utilisateur' => $username, 'actif' => 1]);
        }
        if ($res && $rec = $res->fetch()) {
            if (!is_null($rec['mdp'])) {
                if (Security::checkPassword($password, $rec['mdp'])) {
                    try {
                        $user = new User($rec['id']);
                        $this->auth($user, $remember);
                        return $user;
                    } catch (\Exception $e) {
                        Dbg::error('Tentative de connexion ' . $username . ' FAILED : ' . $e->getMessage());
                        return false;
                    }
                } else {
                    Dbg::error('Tentative de connexion ' . $username . ' FAILED : mot de passe incorrect');
                    return false;
                }
            } else {
                Dbg::error('Compte non activé');
                return false;
            }
        }

        Dbg::error('Tentative de connexion ' . $username . ' FAILED : compte inexistant');
        return false;
    }

    /**
     * @param User $user
     * @param bool $remember
     * @throws \Exception
     */
    private function auth(User $user, bool $remember = false) {
        $now = time();
        if ($user instanceof User && $user->id > 0 && $user->actif) {
            $_SESSION[self::SESSION_AUTH_ID] = $user->id;
            $_SESSION[self::SESSION_AUTH_NOM] = $user->nom;
            $_SESSION[self::SESSION_AUTH_UTILISATEUR] = $user->utilisateur;
            $_SESSION[self::SESSION_AUTH_EMAIL] = $user->email;
            $_SESSION[self::SESSION_AUTH_ROLE] = $user->role;

            Sql::update(User::TBNAME, ['last' => $now], $user->id);
            $this->user = $user;

            if ($remember) {
                self::rememberMe($user);
            }

            Dbg::success('Tentative de connexion : ' . $user->utilisateur . ' SUCCESS');
        } else {
            throw new \Exception('Utilisateur inexistant ou inactif');
        }
    }

    /**
     * @return bool
     */
    public function loginFromCookie() {
        $cookie = isset($_COOKIE[self::COOKIE_REMEMBERME]) ? $_COOKIE[self::COOKIE_REMEMBERME] : '';
        if ($cookie && !empty($cookie)) {
            list ($userId, $token, $mac) = explode(':', $cookie);
            if (!hash_equals(hash_hmac('sha256', $userId . ':' . $token, self::AUTH_KEY), $mac)) {
                return false;
            }
            $res = Sql::select(User::TBTOKEN, ['id_user' => $userId, 'type' => 'login']);
            if ($res && $res->rowCount() > 0) {
                while ($rec = $res->fetch()) {
                    if (hash_equals($rec['token'], $token)) {
                        try {
                            $user = new User($userId);
                            $this->auth($user);
                            return true;
                        } catch (\Exception $e) {
                            Dbg::warning('Remember me login : ' . $e->getMessage());
                        }
                    }
                }
                Dbg::warning('Remember me login : incorrect token');
            } else {
                Dbg::warning('Remember me login : incorrect user');
            }
        }
        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    private static function rememberMe(User $user) {
        $token = Security::generateRandomToken(128);
        $res = Sql::insert(User::TBTOKEN, ['id_user' => $user->id, 'token' => $token]);
        if ($res !== false) {
            $cookie = $user->id . ':' . $token;
            $mac = hash_hmac('sha256', $cookie, self::AUTH_KEY);
            $cookie .= ':' . $mac;
            Dbg::debug('SET cookie data');
            return setcookie(self::COOKIE_REMEMBERME, $cookie);
        }
        return false;
    }

    /**
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role) {
        $auth = App::getInstance()->auth();
        if (!empty($role) && $auth->user) {
            return $auth->user->role === $role;
        }
        return false;
    }
}
