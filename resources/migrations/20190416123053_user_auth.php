<?php

use App\Models\User;
use Phinx\Migration\AbstractMigration;

class UserAuth extends AbstractMigration
{
    public function change() {

        $table = $this->table(User::TBNAME);
        $table->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('email', 'string', ['limit' => 255])
            ->addColumn('username', 'string', ['limit' => 255])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('permission', 'string', ['limit' => 255])
            ->addColumn('created_at', 'datetime')
            ->addColumn('last', 'datetime', ['null' => true])
            ->addColumn('active', 'boolean', ['default' => 1])
            ->addColumn('organization_id', 'integer', ['default' => -1])
            ->addIndex(['username', 'email'], ['unique' => true])
            ->create();

        $table = $this->table(User::TBTOKEN, ['id' => false]);
        $table->addColumn('user_id', 'integer')
            ->addColumn('token', 'text')
            ->addColumn('expire', 'datetime')
            ->addColumn('type', 'string')
            ->create();
    }
}
