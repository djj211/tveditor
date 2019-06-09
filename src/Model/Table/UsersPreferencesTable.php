<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersPreferencesTable extends Table
{
	public static function defaultConnectionName()
	{
		return 'CommandCenter';
	}
	public function initialize(array $config)
	{
		$this->belongsTo('Users');	
	}
	public function validationDefault(Validator $validator)
    {
        return $validator
        	->add('plex_id', [
				'numeric' => [			
					'rule' => ['numeric', ['empty' => true]],
					'message' => 'Must be a valid Plex ID Number.'
				],
				'alphaNumeric' => [
					'rule' => 'alphaNumeric',
					'message' => 'Username can only contain numbers and letters.'
				]
			])	
            ->add('theme', 'inList', [
                'rule' => ['inList', ['Default', 'Fire', 'Ice', 'Forest', 'Midnight']],
                'message' => 'Please enter a valid theme'
            ]);
	}
	
}
?>