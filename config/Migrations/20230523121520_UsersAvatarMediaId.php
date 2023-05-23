<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class UsersAvatarMediaId extends AbstractMigration
{
	public $autoId = false;

	/**
	 * Up Method.
	 *
	 * More information on this method is available here:
	 * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
	 * @return void
	 */
	public function up()
	{
		$this->table('users')
			->addColumn('avatar_media_id', 'integer', [
				'after' => 'id',
				'default' => null,
				'length' => 11,
				'null' => true,
				'signed' => false,
			])
			->addIndex([
				'avatar_media_id'
			])
			->update();

		$this->table('users')
			->addForeignKey(
				'avatar_media_id',
				'media',
				'id',
				[
					'update' => 'RESTRICT',
					'delete' => 'RESTRICT',
				],
				[
					'constraint' => 'fk_users_media',
				]
			)
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
		$this->table('users')
			->dropForeignKey(
				'avatar_media_id'
			)
			->update();

		$this->table('users')
			->removeIndex([
				'avatar_media_id'
			])
			->update();

		$this->table('users')
			->removeColumn('avatar_media_id')
			->update();
	}
}
