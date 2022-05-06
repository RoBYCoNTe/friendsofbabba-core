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
            ->addColumn('fiscal_code', 'char', [
                'after' => 'surname',
                'default' => '',
                'length' => 16,
                'null' => false,
            ])
            ->addColumn('spid_code', 'string', [
                'after' => 'fiscal_code',
                'default' => '',
                'length' => 20,
                'null' => false,
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
