<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersLoginAttemptsTable extends Table
{
	public static function defaultConnectionName()
	{
		return 'CommandCenter';
	}
	
	public function initialize(array $config)
	{
		$this->belongsTo('Users');
	}
	
}
?>