<?php

use App\Models\User;
use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run() {
        $users = [
            [
                'name'       => 'Admin',
                'email'      => 'admin+' . uniqid() . '@email.com',
                'username'   => 'admin',
                'password'   => password_hash('123', PASSWORD_DEFAULT),
                'permission' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'last'       => null,
                'active'     => 1,
            ],
        ];

        $this->table(User::TBNAME)->insert($users)
            ->save();

    }
}
