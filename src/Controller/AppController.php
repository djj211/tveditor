<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Cake\Controller\Component\CookieComponent;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
     
    public function initialize()
    {
        parent::initialize();
		
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'loginRedirect' => [
                'controller' => '',
                'action' => 'index'
            ],
            'logoutRedirect' => [
                'controller' => '',
                'action' => 'login',
            ]
        ]);
		$this->loadComponent('Cookie', [
			'expires' => '14 days',
			'httpOnly' => true
		]);
    }
    
	public function beforeFilter(Event $event)
	{
	    parent::beforeFilter($event);
				
		if ($this->Cookie->read('User') && $this->Auth->user('id'))
		{			
			$this->updateCookie();	
		}
		else if ($this->Cookie->read('User') && !$this->Auth->user('id'))
		{
			$loginTable = TableRegistry::get('UsersKeepLogins');
			$cookieUser = $loginTable->find('all');
			$cookieUser->contain(['Users']);
			$cookieUser->select(['Users.role', 'Users.username', 'seriesId', 'token', 'user_id']);
			$cookieUser->where(['token' => $this->Cookie->read('User.token'), 'UsersKeepLogins.username' => $this->Cookie->read('User.username')]);
			$cookieUser = $cookieUser->first();
			
			if ($cookieUser->seriesId == $this->Cookie->read('User.series') && 
				$cookieUser->token == $this->Cookie->read('User.token')) {
					
					$data = [
						'id' => $cookieUser->user_id,
						'username' => $cookieUser->user->username,
						'role' => $cookieUser->user->role
					];
					
					$user = $this->Auth->setUser($data);
					 if ($user) {
					 	$this->clearReset($cookieUser->user_id);
						$this->updateCookie();	
	            		return $this->redirect($this->Auth->redirectUrl());
	        		}					
				}
		}
	}
    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
		
		//Themes Bitches!
		if ($this->Auth->user('id')) {
			$id = $this->Auth->user('id');
			
			$this->loadModel('Users');
			
			$user = $this->Users->get($id, [
				'contain' => 'UsersPreferences',
				'fields' => ['UsersPreferences.theme', 'username']
			]);
			
			$theme = $user->users_preference->theme;
			
			if ($theme != 'Default')
			{
				$this->viewBuilder()->theme($theme);
			}
			
			$this->set('myAccount', $user->username);
			
		}
			$cont = $this->request->controller;
			
			$titleHead = "";
			
			if ($cont == "Shows") {
				$titleHead = $cont;
			}
			else {
				$titleHead = $this->request->action;
			}
			
			$this->set('titleHead', ucfirst($titleHead));
		
    }
	
	public function loginCookie() {
		$series = Security::hash($this->generateRandomString() . $this->Auth->user('username'), 'sha256', true);
		$token = Security::hash($this->generateRandomString() . $this->Auth->user('username'), 'sha256', true);
		$username = Security::hash($this->Auth->user('username'), 'sha256', true);
				
		$loginTable = TableRegistry::get('UsersKeepLogins');
		$keep = $loginTable->newEntity();
		$keep->user_id = $this->Auth->user('id');
		$keep->seriesId = $series;
		$keep->token = $token;
		$keep->username = $username;
			
		$loginTable->save($keep);
				
		$this->Cookie->write('User', [
			'username' => $username,
			'series' => $series,
			'token' => $token
		]);
		
	}
	
	public function generateRandomString($length = 20) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
	
	public function clearCookie() {
		
		$loginTable = TableRegistry::get('UsersKeepLogins');
		$loginTable->deleteAll([
			'user_id' => $this->Auth->user('id'),
			'token' => $this->Cookie->read('User.token')			
		]);
		
		$this->Cookie->delete('User');
	}
	
	public function updateCookie() {
		$this->loadModel('UsersKeepLogins');
		
		$username = $this->Cookie->read('User.username');
		$token = $this->Cookie->read('User.token');
		$seriesOld = $this->Cookie->read('User.series');
		
		$keep = $this->UsersKeepLogins->findByToken($token, [
			'where' => (['token' => $token])
		]);
		
		$keep = $keep->first();
		
		if ($keep->seriesId == $seriesOld && $keep->token == $token)
		{		
			$series = Security::hash($this->generateRandomString() . $this->Auth->user('username'), 'sha256', true);
						
			$data = [
				'seriesId' => $series
			];
			
			$keep = $this->UsersKeepLogins->patchEntity($keep, $data);
			
			$this->UsersKeepLogins->save($keep);
			
			$this->Cookie->write('User', [
				'username' => $username,
				'series' => $series,
				'token' => $token
			]);
		}
		else {
			$this->Flash->error(__('Whoa! Something went wrong. Please try again.'));
			return $this->redirect(['controller' => 'users', 'action' => 'logout']);
		}
	}

	public function clearReset($user_id) {
		$resetTable = TableRegistry::get('UsersResetPasswords');
		$resetTable->deleteAll([
			'user_id' => $user_id		
		]);
	}
}
