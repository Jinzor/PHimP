<?php

namespace App\Controllers;

use App\Core\Auth;

class AdminController extends Controller
{
    protected $layout = 'master';
    protected $section = 'admin';
    protected $requireAuth = true;
    protected $requireRole = [Auth::ROLE_ADMIN];

    public function dashboard() {
        $this->render('dashboard.index', [
            'a' => 'b',
        ]);
    }
}