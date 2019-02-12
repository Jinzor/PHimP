<?php

namespace App\Controllers;

class AdminController extends Controller
{
    protected $layout = 'master';
    protected $section = 'admin';

    public function __construct() {
        parent::__construct();
        if (peutAccederAdmin($this->app->auth()) === false) {
            $this->error_403();
            exit();
        }
    }

    public function index() {
        //
    }
}