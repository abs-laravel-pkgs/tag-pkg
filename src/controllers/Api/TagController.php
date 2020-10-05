<?php

namespace Abs\TagPkg\Api;

use Abs\BasicPkg\Traits\CrudTrait;
use App\Http\Controllers\Controller;
use App\Tag;

class TagController extends Controller {
	use CrudTrait;
	public $model = Tag::class;
	public $successStatus = 200;

}
