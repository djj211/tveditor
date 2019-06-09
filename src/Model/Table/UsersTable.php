<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
	public static function defaultConnectionName()
	{
		return 'CommandCenter';
	}
	public function initialize(array $config)
	{
		$this->hasOne('UsersPreferences', [
			'className' => 'UsersPreferences',
			'dependent' => true
		]);	
		
		$this->hasMany('UsersLoginAttempts', [
			'className' => 'UsersLoginAttempts',
			'dependent' => true
		]);	
		
		$this->hasMany('UsersKeepLogins', [
			'className' => 'UsersKeepLogins',
			'dependent' => true
		]);	
		
		$this->hasOne('UsersResetPasswords', [
			'className' => 'UsersResetPasswords',
			'dependent' => true
		]);	
		
	}
    public function validationDefault(Validator $validator)
    {
        return $validator
            ->integer('id')
            ->allowEmpty('id', 'create')
            ->notEmpty('username', 'A username is required')
			->notEmpty('password', 'A password is required', 'create')
			->notEmpty('confirm_password', 'You must retype your password', 'create')
			->notEmpty('email', 'An Email  is required')
            ->notEmpty('role', 'A role is required')
			->add('password', [
				'match'=> [
					'rule' => ['compareWith', 'confirm_password'],
					'message' => 'Passwords do not match.',
				],
				'minLength' => [
					'rule' => ['minLength', 6],
					'message' => 'Password must be at least 6 characters.'
				],
				'custom' => [
					'rule' => [$this, 'strongPassword'],
					'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one of the following: !@#$%^&*.'
				]
			])
			->add('username', [
				'unique' => [			
					'rule' => 'validateUnique',
					'provider' => 'table',
					'message' => 'Username Already Exists.'
				],
				'alphaNumeric' => [
					'rule' => 'alphaNumeric',
					'message' => 'Username can only contain numbers and letters.'
				]
			])	
			->add('email', 
					['unique' => [			
					'rule' => 'validateUnique',
					'provider' => 'table',
					'message' => 'Email Already Exists.'
				]
			])			
            ->add('role', 'inList', [
                'rule' => ['inList', ['admin', 'manage', 'read']],
                'message' => 'Please enter a valid role'
            ])
			->add('email', 'validFormat', [
				'rule' => 'email',
				'message' => 'E-Mail must be valid'
			]);
    }

	public function validationReset(Validator $validator)
	{
		return $validator
			->notEmpty('password', 'A password is required')
			->notEmpty('confirm_password', 'You must retype your password')
			->add('password', [
				'matchReset'=> [
					'rule' => ['compareWith', 'confirm_password'],
					'message' => 'Passwords do not match.',
				],
				'minLengthReset' => [
					'rule' => ['minLength', 6],
					'message' => 'Password must be at least 6 characters.'
				],
				'customReset' => [
					'rule' => [$this, 'strongPassword'],
					'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one of the following: !@#$%^&*.'
				]
			]);			
	}

	public function strongPassword($value, $context) {
		$number = false;
		$upper = false;
		$lower = false;
		$special = false;
				
		if (preg_match('#[0-9]#', $value))
		{
			$number = true;
		}
		else 
		{
			$number = false;
		}
		if (preg_match('/[A-Z]/', $value))
		{
			$upper = true;
		}
		else 
		{
			$upper = false;
		}
		if (preg_match('/[a-z]/', $value))
		{
			$lower = true;
		}
		else 
		{
			$lower = false;
		}
		if (preg_match('/[!@#$%^&*]/', $value))
		{
			$special = true;
		}
		else 
		{
			$special = false;
		}
		
		if ($number == true && $upper == true && $lower == true && $special == true)
		{
			return true;
		}
		else {
			return false;
		}
	}
	
}
?>