<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SeriesTable extends Table
{
	public function initialize(array $config)
	{
		$this->belongsTo('series_episodes', [
			'bindingKey' => 'series_id',
			'foreignKey' => 'id',
			'propertyName' => 'seasons'
		]);

	}
}
?>
