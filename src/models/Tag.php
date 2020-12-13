<?php

namespace Abs\TagPkg\Models;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Models\Masters\Item;
use App\Models\Company;
use App\Models\Config;
use App\Models\Masters\Category;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;

class Tag extends BaseModel {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'tags';
	public $timestamps = true;

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
		$this->rules = [
			'name' => [
				'min:3',
			],
		];
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
	];

	protected $casts = [
	];

	public $sortable = [
		'name',
	];

	public $sortScopes = [
		//'id' => 'orderById',
		//'code' => 'orderCode',
		//'name' => 'orderBytName',
		//'mobile_number' => 'orderByMobileNumber',
		//'email' => 'orderByEmail',
	];

	// Custom attributes specified in this array will be appended to model
	protected $appends = [
	];

	//This model's validation rules for input values
	public $rules = [
		//Defined in constructor
	];

	public $fillableRelationships = [
		'company',
		'type',
	];

	public $relationshipRules = [
		'type' => [
			'required',
			//'hasOne:App\Models\Address,App\Models\Address::optionIds',
		],
	];

	// Relationships to auto load
	public static function relationships($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
				'type',
			]);
		} else if ($action === 'read') {
			$relationships = array_merge($relationships, [
				'type',
			]);
		} else if ($action === 'save') {
			$relationships = array_merge($relationships, [
			]);
		} else if ($action === 'options') {
			$relationships = array_merge($relationships, [
			]);
		}

		return $relationships;
	}

	public static function appendRelationshipCounts($action = '', $format = ''): array
	{
		$relationships = [];

		if ($action === 'index') {
			$relationships = array_merge($relationships, [
				'items',
				'categories',
			]);
		} else if ($action === 'options') {
			$relationships = array_merge($relationships, [
			]);
		}

		return $relationships;
	}

	// Dynamic Attributes --------------------------------------------------------------

	// Relationships --------------------------------------------------------------
	public function type(): BelongsTo {
		return $this->belongsTo(Config::class, 'taggable_type_id');
	}

	public function categories(): HasMany {
		return $this->hasMany(Category::class);
	}

	public function items(): HasMany {
		return $this->hasMany(Item::class);
	}

	//--------------------- Query Scopes -------------------------------------------------------
	public function scopeFilterSearch($query, $term): void
	{
		if ($term !== '') {
			$query->where(function ($query) use ($term) {
				$query->orWhere('name', 'LIKE', '%' . $term . '%');
			});
		}
	}

	public function scopeFilterTypeName($query, $typeName): void
	{
		$query->whereHas('type',function ($query) use ($typeName) {
			$query->where('configs.name', $typeName);
		});
	}

	protected static $excelColumnRules = [
		'Taggable Type Name' => [
			'table_column_name' => 'taggable_type_id',
			'rules' => [
				'required' => [
				],
				'fk' => [
					'class' => 'App\Config',
					'foreign_table_column' => 'name',
				],
			],
		],
		'Name' => [
			'table_column_name' => 'name',
			'rules' => [
				'required' => [
				],
			],
		],
	];

	public static function saveFromObject($record_data) {
		$record = [
			'Company Code' => $record_data->company_code,
			'Taggable Type Name' => $record_data->taggable_type_name,
			'Name' => $record_data->name,
		];
		return static::saveFromExcelArray($record);
	}

	public static function saveFromExcelArray($record_data) {
		$errors = [];
		$company = Company::where('code', $record_data['Company Code'])->first();
		if (!$company) {
			return [
				'success' => false,
				'errors' => ['Invalid Company : ' . $record_data['Company Code']],
			];
		}

		if (!isset($record_data['created_by_id'])) {
			$admin = $company->admin();

			if (!$admin) {
				return [
					'success' => false,
					'errors' => ['Default Admin user not found'],
				];
			}
			$created_by_id = $admin->id;
		} else {
			$created_by_id = $record_data['created_by_id'];
		}

		if (empty($record_data['Taggable Type Name'])) {
			$errors[] = 'Taggable Type Name is empty';
		} else {
			$taggable_type = Config::where([
				'config_type_id' => Static::$CONFIG_TYPE_ID,
				'name' => $record_data['Taggable Type Name'],
			])->first();
			if (!$taggable_type) {
				$errors[] = 'Invalid Type Name : ' . $record_data['Taggable Type Name'];
			}
		}

		if (count($errors) > 0) {
			return [
				'success' => false,
				'errors' => $errors,
			];
		}

		$record = Self::firstOrNew([
			'company_id' => $company->id,
			'taggable_type_id' => $taggable_type->id,
			'name' => $record_data['Name'],
		]);

		$record->created_by_id = $created_by_id;
		$record->save();
		return [
			'success' => true,
		];
	}

}
