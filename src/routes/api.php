<?php
Route::group(['namespace' => 'Abs\TagPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'tag-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});