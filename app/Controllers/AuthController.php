<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\User;

class AuthController extends Controller
{
    protected $layout = 'backoffice-no-auth';
    protected $requireAuth = false;

    /**
     * Vue login
     */
    public function pageLogin() {
        if ($this->app->auth()->isAuth()) {
            $this->redirect(url()); // Déjà authentifié
        }
        $alert = $this->app->getAlert();
        $this->render('auth/login', [
            'login'   => '',
            'error'   => isset($alert['error']) ? $alert['error'] : '',
            'message' => isset($alert['message']) ? $alert['message'] : '',
        ]);
    }

    /**
     * Vue de création de mot de passe
     */
    public function pageCreatePassword() {
        if ($this->app->auth()->isAuth()) {
            $this->redirect(url()); // Déjà authentifié
        }
        $userId = Request::valueRequest('u');
        $token = Request::valueRequest('token');
        if (!empty($userId) && !empty($token)) {
            $user = new User($userId);
            if (User::checkToken($token, $user->id)) {
                $this->render('auth/password-creation', [
                    'user'  => $user,
                    'token' => $token,
                    'error' => $this->app->getAlert('error'),
                ]);
                exit();
            }
        }
        $this->redirect(url() . 'login', ['error' => 'Le lien n`est pas valide ou a expiré']);
    }

    /**
     * Créé le mot de passe pour un utilisateur
     */
    public function createPassword() {
        $userId = intval(Request::valuePost('user_id', 0));
        $token = Request::valuePost('token');
        $pass1 = Request::valuePost('password', true, true);
        $pass2 = Request::valuePost('password_confirm', true, true);
        $user = new User($userId);
        if ($user->id > 0 && User::checkToken($token, $userId)) {
            if (!is_null($pass1) && $pass1 == $pass2) {
                $user->changePassword($pass1);
                $this->redirect(url() . 'login', ['message' => 'Votre mot de passe a été configuré']);
                exit();
            } else {
                $this->redirect(url() . 'creation?u=' . $userId . '&token=' . $token, ['error' => 'Saisissez votre mot de passe.']);
                exit();
            }
        } else {
            $this->redirect(url() . 'login', ['error' => 'Impossible de créer un nouveau mot de passe']);
            exit();
        }
    }

    /**
     * Authentification
     */
    public function auth() {
        $username = Request::valueRequest('username');
        $password = Request::valueRequest('password');
        $remember = Request::valueRequest('remember', 0);
        $redirect = Request::valueRequest('redirect');

        if (empty($username) || empty($password)) {
            $this->redirect(route('auth.login') . ($redirect ? '&redirect=' . $redirect : ''), [
                'error' => 'Veuillez saisir vos identifiants.',
            ]);
            return;
        }

        $connectedUser = $this->app->auth()->login($username, $password, $remember == 1);
        if (is_null($connectedUser) || !$connectedUser) {
            $this->redirect(route('auth.login') . ($redirect ? '&redirect=' . $redirect : ''), [
                'error' => 'Erreur lors de l\'authentification, identifiants incorrects.',
            ]);
            return;
        }

        if (!empty($redirect)) {
            $this->redirect($redirect);
        } else {
            $this->redirect(route('dashboard.index')); // TODO Define default page
        }

        return;
    }

    public function logout() {
        $this->app->auth()->logout();
        $this->redirect(url() . 'login');
    }
}