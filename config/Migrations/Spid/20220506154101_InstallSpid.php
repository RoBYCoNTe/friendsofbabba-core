<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class InstallSpid extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up()
    {

        $this->table('user_profiles')
            ->addColumn("phone", "string", [
                'after' => 'surname',
                'default' => '',
                'length' => 30,
                'null' => true
            ])
            ->addColumn('fiscal_code', 'char', [
                'after' => 'phone',
                'default' => '',
                'length' => 16,
                'null' => true,
            ])

            ->addColumn('spid_code', 'string', [
                'after' => 'fiscal_code',
                'default' => '',
                'length' => 20,
                'null' => true,
            ])
            ->addColumn('birth_place', 'string', [
                'after' => 'spid_code',
                'default' => '',
                'length' => 100,
                'null' => true
            ])
            ->addColumn('birth_province', 'string', [
                'after' => 'birth_place',
                'default' => '',
                'length' => 2,
                'null' => true
            ])
            ->addColumn('birth_date', 'date', [
                'after' => 'birth_province',
                'default' => null,
                'length' => null,
                'null' => true
            ])
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down()
    {

        $this->table('user_profiles')
            ->removeColumn('fiscal_code')
            ->removeColumn('spid_code')
            ->update();
    }
}
