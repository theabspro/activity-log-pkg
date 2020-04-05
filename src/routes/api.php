<?php
Route::group(['namespace' => 'Abs\EmployeePkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'employee-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});