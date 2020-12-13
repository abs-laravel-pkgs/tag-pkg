<?php

namespace Abs\TagPkg\Controllers\Api;

use Abs\BasicPkg\Traits\CrudTrait;
use App\Http\Controllers\Controller;
use App\Models\Tag;

class TagPkgApiController extends Controller {
	use CrudTrait;
	public $model = Tag::class;
	public $successStatus = 200;
}
