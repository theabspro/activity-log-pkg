<?php
namespace Abs\EmployeePkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class EmployeePkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//Employees
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'employees',
				'display_name' => 'Employees',
			],
			[
				'display_order' => 1,
				'parent' => 'employees',
				'name' => 'add-employee',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'employees',
				'name' => 'edit-employee',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'employees',
				'name' => 'delete-employee',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}