<?php

Route::group(['namespace' => 'Abs\TagPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'tag-pkg'], function () {
	//FAQs
	Route::get('/tags/get-list', 'TagController@getTagList')->name('getTagList');
	Route::get('/tag/get-form-data', 'TagController@getTagFormData')->name('getTagFormData');
	Route::post('/tag/save', 'TagController@saveTag')->name('saveTag');
	Route::get('/tag/delete', 'TagController@deleteTag')->name('deleteTag');
});

Route::group(['namespace' => 'Abs\TagPkg', 'middleware' => ['web'], 'prefix' => 'tag-pkg'], function () {
	//FAQs
	Route::get('/tags/get', 'TagController@getTags')->name('getTags');
});
