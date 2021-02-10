<?php

namespace App\Controllers;

use App\Core\App;

class Controller
{
    protected $app;
    protected $layout = 'backoffice';
    protected $section = '';
    protected $async = false;
    protected $requireAuth = false;
    protected $requireRole = [];
    protected $path = ROOT . "views";

    public function __construct() {
        $this->app = App::getInstance();

        if ((isset($_GET['source']) && $_GET['source'] == 'ajax')) {
            $this->async = true;
        }

        if ($this->requireAuth && $this->app->auth()->isAuth() === false) { // Vérifie si il faut être authentifié
            if (!$this->app->auth()->loginFromCookie()) { // Tentative de reconnexion via "remember me"
                if ($this->async) { // Affiche d'une erreur si appel en Ajax
                    $this->error_401();
                } else {
                    // Sinon simple redirection vers la page de login
                    $this->redirect(route('auth.login') . '&redirect=https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], [
                        'error' => 'Connectez vous pour accéder à la page',
                    ]);
                }
                exit();
            }
        }

        // Vérification des permissions
        if (!empty($this->requireRole)) {
            if ($this->app->auth()->hasAnyRole($this->requireRole) === false) {
                $this->error_403();
                exit();
            }
        }
    }

    /**
     * @param string $view Name of the view.
     * @param array $vars Variables passed into the view.
     */
    protected function render($view, $vars = []) {
        $alert = $this->app->getAlert();
        $vars['app'] = $app = $this->app;
        $vars['section'] = $section = $this->section;
        $content = $this->getContent($view, $vars);
        if ($this->async || is_null($this->layout)) {
            echo $content;
            exit();
        }
        require($this->path . '/layouts/' . $this->layout . '.htm.php');
    }

    protected function getContent($view, $vars = []) {
        ob_start();
        extract($vars);
        require($this->path . '/' . str_replace('.', '/', $view) . '.htm.php');
        return ob_get_clean();
    }

    /**
     * @param string $url
     * @param array|string $data
     * @param int $status HTTP code
     */
    protected function redirect($url, $data = [], $status = 302) {
        if (!empty($data)) {
            App::getInstance()->setAlert($data);
        }
        header('Location: ' . $url, true, $status);
        exit();
    }

    public function error_403() {
        header('HTTP/1.0 403 Forbidden');
        $this->render('generic.403');
        exit();
    }

    public function error_404() {
        header('HTTP/1.0 404 Not Found');
        $this->render('generic.404');
        exit();
    }

    public function error_401() {
        header('HTTP/1.0 401 Unauthorized');
        exit();
    }
}