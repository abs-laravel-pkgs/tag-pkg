<?php
namespace Abs\TagPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class TagPkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//Tags
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'tags',
				'display_name' => 'Tags',
			],
			[
				'display_order' => 1,
				'parent' => 'tags',
				'name' => 'add-tag',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'tags',
				'name' => 'edit-tag',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'tags',
				'name' => 'delete-tag',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}