<?php

namespace Abs\TagPkg;
use Abs\BasicPkg\Attachment;
use Abs\TagPkg\Tag;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class TagController extends Controller {

	private $company_id;
	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
		$this->company_id = config('custom.company_id');
	}

	public function getTags(Request $request) {
		$this->data['tags'] = Tag::
			select([
			'tags.question',
			'tags.answer',
		])
			->where('tags.company_id', $this->company_id)
			->orderby('tags.display_order', 'asc')
			->get()
		;
		$this->data['success'] = true;

		return response()->json($this->data);

	}

	public function getTagList(Request $request) {
		$tags = Tag::withTrashed()
			->select([
				'tags.*',
				DB::raw('IF(tags.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('tags.company_id', $this->company_id)
		/*->where(function ($query) use ($request) {
				if (!empty($request->question)) {
					$query->where('tags.question', 'LIKE', '%' . $request->question . '%');
				}
			})*/
			->orderby('tags.id', 'desc');

		return Datatables::of($tags)
			->addColumn('name', function ($tags) {
				$status = $tags->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $tags->name;
			})
			->addColumn('action', function ($tags) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/tag-pkg/tag/edit/' . $tags->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="javascript:;" data-toggle="modal" data-target="#tag-delete-modal" onclick="angular.element(this).scope().deleteTag(' . $tags->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getTagFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$tag = new Tag;
			$attachment = new Attachment;
			$action = 'Add';
		} else {
			$tag = Tag::withTrashed()->find($id);
			$attachment = Attachment::where('id', $tag->logo_id)->first();
			$action = 'Edit';
		}
		$this->data['tag'] = $tag;
		$this->data['attachment'] = $attachment;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveTag(Request $request) {
		//dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'delivery_time.required' => 'Delivery Time is Required',
				'charge.required' => 'Charge is Required',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'unique:tags,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'delivery_time' => 'required',
				'charge' => 'required',
				'logo_id' => 'mimes:jpeg,jpg,png,gif,ico,bmp,svg|nullable|max:10000',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$tag = new Tag;
				$tag->created_by_id = Auth::user()->id;
				$tag->created_at = Carbon::now();
				$tag->updated_at = NULL;
			} else {
				$tag = Tag::withTrashed()->find($request->id);
				$tag->updated_by_id = Auth::user()->id;
				$tag->updated_at = Carbon::now();
			}
			$tag->fill($request->all());
			$tag->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$tag->deleted_at = Carbon::now();
				$tag->deleted_by_id = Auth::user()->id;
			} else {
				$tag->deleted_by_id = NULL;
				$tag->deleted_at = NULL;
			}
			$tag->save();

			if (!empty($request->logo_id)) {
				if (!File::exists(public_path() . '/themes/' . config('custom.admin_theme') . '/img/tag_logo')) {
					File::makeDirectory(public_path() . '/themes/' . config('custom.admin_theme') . '/img/tag_logo', 0777, true);
				}

				$attacement = $request->logo_id;
				$remove_previous_attachment = Attachment::where([
					'entity_id' => $request->id,
					'attachment_of_id' => 20,
				])->first();
				if (!empty($remove_previous_attachment)) {
					$remove = $remove_previous_attachment->forceDelete();
					$img_path = public_path() . '/themes/' . config('custom.admin_theme') . '/img/tag_logo/' . $remove_previous_attachment->name;
					if (File::exists($img_path)) {
						File::delete($img_path);
					}
				}
				$random_file_name = $tag->id . '_tag_file_' . rand(0, 1000) . '.';
				$extension = $attacement->getClientOriginalExtension();
				$attacement->move(public_path() . '/themes/' . config('custom.admin_theme') . '/img/tag_logo', $random_file_name . $extension);

				$attachment = new Attachment;
				$attachment->company_id = Auth::user()->company_id;
				$attachment->attachment_of_id = 20; //User
				$attachment->attachment_type_id = 40; //Primary
				$attachment->entity_id = $tag->id;
				$attachment->name = $random_file_name . $extension;
				$attachment->save();
				$tag->logo_id = $attachment->id;
				$tag->save();
			}

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Tag Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Tag Updated Successfully',
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}

	public function deleteTag(Request $request) {
		DB::beginTransaction();
		try {
			$tag = Tag::withTrashed()->where('id', $request->id)->first();
			if (!is_null($tag->logo_id)) {
				Attachment::where('company_id', Auth::user()->company_id)->where('attachment_of_id', 20)->where('entity_id', $request->id)->forceDelete();
			}
			Tag::withTrashed()->where('id', $request->id)->forceDelete();

			DB::commit();
			return response()->json(['success' => true, 'message' => 'Tag Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
