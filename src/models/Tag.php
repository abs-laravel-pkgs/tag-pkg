<?php

namespace Abs\TagPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\BaseModel;

class Tag extends BaseModel {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'tags';
	public $timestamps = true;
	protected $fillable = [
		'company_id',
		'taggable_type_id',
		'name',
	];

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
		//dd($record);
		//$result = Self::validateAndFillExcelColumns($record_data, Static::$excelColumnRules, $record);
		//if (!$result['success']) {
		//	return $result;
		//}

		$record->created_by_id = $created_by_id;
		$record->save();
		return [
			'success' => true,
		];
	}

}
