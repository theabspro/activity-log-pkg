<?php

Route::group(['namespace' => 'Abs\EmployeePkg', 'middleware' => ['web', 'auth'], 'prefix' => 'employee-pkg'], function () {

	//EMPLOYEES
	Route::get('/employee/get-list', 'EmployeeController@getEmployeeList')->name('getEmployeeList');
	Route::get('/employee/get-form-data', 'EmployeeController@getEmployeeFormData')->name('getEmployeeFormData');
	Route::post('/employee/save', 'EmployeeController@saveEmployee')->name('saveEmployee');
	Route::get('/employee/delete', 'EmployeeController@deleteEmployee')->name('deleteEmployee');

});