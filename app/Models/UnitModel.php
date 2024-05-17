<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
	protected $DBGroup              = 'default';
	protected $table                = 'unit';
	protected $primaryKey           = 'unit_id';
	protected $useAutoIncrement     = true;
	protected $insertID             = 0;
	protected $returnType           = 'array';
	protected $useSoftDelete        = false;
	protected $protectFields        = true;
	protected $allowedFields        = ['unit_rekening', 'unit_kode', 'unit_tipe', 'unit_nomor', 'unit_nama', 'unit_keterangan', 'unit_nilaitanah', 'unit_user'];

	// Dates
	protected $useTimestamps        = true;
	protected $dateFormat           = 'datetime';
	protected $createdField         = 'created_at';
	protected $updatedField         = 'updated_at';
	protected $deletedField         = 'deleted_at';

	// Validation
	protected $validationRules      = [];
	protected $validationMessages   = [];
	protected $skipValidation       = false;
	protected $cleanValidationRules = true;

	// Callbacks
	protected $allowCallbacks       = true;
	protected $beforeInsert         = [];
	protected $afterInsert          = [];
	protected $beforeUpdate         = [];
	protected $afterUpdate          = [];
	protected $beforeFind           = [];
	protected $afterFind            = [];
	protected $beforeDelete         = [];
	protected $afterDelete          = [];

	public function countTipe($tipe)
	{
		$query = $this->where('unit_tipe', $tipe)->findAll();
		return count($query);
	}
}