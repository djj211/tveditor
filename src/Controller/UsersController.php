<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use Cake\Controller\Component\CookieComponent;
use Cake\Mailer\Email;
use Cake\Utility\Security;

class UsersController extends AppController
{
	public function beforeFilter(Event $event)
	{
	    parent::beforeFilter($event);
	    // Allow users to register and logout.
	    // You should not add the "login" action to allow list. Doing so would
	    // cause problems with normal functioning of AuthComponent.
	    $this->Auth->allow(['logout', 'login', 'forgot', 'reset']);
	}
	
	public function login()
	{
		$this->viewBuilder()->layout('login');
		if ($this->Cookie->read('User'))
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
	            		return $this->redirect($this->Auth->redirectUrl());
	        		}					
				}
		}
		
	    if ($this->request->is('post')) {
	    	
			$user = $this->Users->findByUsername($this->request->data['username'], [
				'fields' => ['id']
			]);
			
			$user = $user->first();
						
			if($user)
			{
				$attemptsTable = TableRegistry::get('UsersLoginAttempts');
				$attempt = $attemptsTable->newEntity();
				$attempt->user = $user;
				$attempt->time = Time::now('America/New_York');
				
				$attemptsTable->save($attempt);
					
				$valid_attempts = new Time('1 hours ago', 'America/New_York');
				
				$attempts = $attemptsTable->find('all');
				$attempts->select('time');
				$attempts->where(['user_id' => $user->id, 'time >' => $valid_attempts]);
							
				if ($attempts->count() > 5)
				{
					$this->Flash->error(__('Account locked, please try again later'));
				}
				else {
					$user = $this->Auth->identify();
			        if ($user) {
			        	
			            $this->Auth->setUser($user);
			        	$attemptsTable->deleteAll([
			        		'user_id' => $this->Auth->user('id')
			        	]);
						
						$this->clearReset($this->Auth->user('id'));
						
						if ($this->request->data['keep_me_logged_id'])
						{
							$this->loginCookie();
						}
						else {
							$this->clearCookie();
						}
						
			            return $this->redirect($this->Auth->redirectUrl());
			        }
					
					$this->Flash->error(__('Invalid username or password, try again'));
				}
			}
			else {
				$this->Flash->error(__('Invalid username or password, try again'));
			}
		}
		else {
			$this->isloggedIn();
		}
	}
	
	public function logout()
	{
		$this->clearCookie();
	    return $this->redirect($this->Auth->logout());
	}

    public function index()
    {
		$role = $this->Auth->user('role');
		if ($role == "admin")
		{
			$this->viewBuilder()->layout('admin');
			$users=$this->Users->find('all');
	  		$this->set('users', $users);
		}
		else {
			$this->viewBuilder()->layout('unauthorized');
			$this->Flash->error(__('Unauthorized: You are not authorized to access that content.'));
			return $this->redirect($this->referer());
		}
		
		$this->set('role', $role);
		
    }

    public function myaccount()
    {
    	$id = $this->Auth->user('id');
		
		$user = $this->Users->get($id, [
			'contain' => 'UsersPreferences'
		]);
		
    	if ($this->request->is(['patch', 'post', 'put'])) {
    		
    		$newUsername = $this->request->data['username'];
			$newEmail = $this->request->data['email'];
			$newPassword = $this->request->data['password'];
			
			if ($newPassword)
			{
				$data = [
					'username' => $newUsername,
					'email' => $newEmail,
					'users_preference' => $this->request->data['users_preference'],
					'password' => $newPassword,
					'confirm_password' => $this->request->data['confirm_password']
				];
			}
			else {
				$data = [
					'username' => $newUsername,
					'email' => $newEmail,
					'users_preference' => $this->request->data['users_preference']
				];
			}
			
			$user = $this->Users->patchEntity($user, $data, [
				'associated' => 'UsersPreferences'
			]);
			
			if($this->Users->save($user))
			{
				$this->Flash->success(__('Sucessfully Updated Account!'));
			}
			else 
			{
				$this->Flash->error(__(' Your Account Could Not be Saved. Please Review any Errors.'));
			}
	    }
		
    	$role = $this->Auth->user('role');
		if ($role == "admin")
		{
			$this->viewBuilder()->layout('admin');
		}
		else if ($role == "read")
		{
			$this->viewBuilder()->layout('read');
		}
		
		$this->set('user', $user);
    }

    public function add()
    {
    	$id = $this->Auth->user('id');
		$role = $this->Auth->user('role');
		
		if ($role != 'admin')
		{
			$this->viewBuilder()->layout('unauthorized');
			$this->Flash->error(__('Unauthorized: You are not authorized to access that content.'));
			return $this->redirect($this->referer());
		}
		else 
		{
			$this->viewBuilder()->layout('admin');
						
	        $user = $this->Users->newEntity();
	        if ($this->request->is('post')) {
				$user = $this->Users->patchEntity($user, $this->request->data, [
					'associated' => 'UsersPreferences'
				]);
	            if ($this->Users->save($user)) {
	                $this->Flash->success(__('Successfully Added new User!'));
	                return $this->redirect(['action' => 'add']);
	            }
	            $this->Flash->error(__('Unable to add the user. Please Review any Errors.'));
	        }
			
	        $this->set('user', $user);
			
		}
    }
	
	public function delete($id)
	{
		$role = $this->Auth->user('role');
		if ($role != 'admin')
		{
			$this->viewBuilder()->layout('unauthorized');
			$this->Flash->error(__('Unauthorized: You are not authorized to access that content.'));
			return $this->redirect($this->referer());
		}
		else 
		{				
			$user = $this->Users->get($id, [
				fields => 'id'
			]);
			
			if($this->Users->delete($user))
			{
				$this->Flash->success(__('The user has been deleted.'));
	        } else {
	            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
	        }
			return $this->redirect(['action' => 'index']);
		}
	}
	
    public function edit($id)
    {
    	$role = $this->Auth->user('role');
		
		if ($role != 'admin')
		{
			$this->viewBuilder()->layout('unauthorized');
			$this->Flash->error(__('Unauthorized: You are not authorized to access that content.'));
			return $this->redirect($this->referer());
		}
		else 
		{
			$this->viewBuilder()->layout('admin');
				
			$user = $this->Users->get($id, [
				'contain' => 'UsersPreferences'
			]);
			
	    	if ($this->request->is(['patch', 'post', 'put'])) {
	    		
	    		$newUsername = $this->request->data['username'];
				$newEmail = $this->request->data['email'];
				$newPassword = $this->request->data['password'];
				$newRole = $this->request->data['role'];
				
				if ($newPassword)
				{
					$data = [
						'username' => $newUsername,
						'email' => $newEmail,
						'role' => $newRole,
						'users_preference' => $this->request->data['users_preference'],
						'password' => $newPassword,
						'confirm_password' => $this->request->data['confirm_password']
					];
				}
				else {
					$data = [
						'username' => $newUsername,
						'email' => $newEmail,
						'users_preference' => $this->request->data['users_preference'],
						'role' => $newRole
					];
				}
				
				$user = $this->Users->patchEntity($user, $data, [
					'associated' => 'UsersPreferences'
				]);
				
				if($this->Users->save($user))
				{
					$this->Flash->success(__('Sucessfully Updated Account!'));
				}
				else 
				{
					$this->Flash->error(__(' Your Account Could Not be Saved. Please Review any Errors.'));
				}
		    }
		
			$this->set('user', $user);
		}
		
    }

	public function forgot() {
		$this->isloggedIn();
		
		$this->viewBuilder()->layout('login');
		
		if ($this->request->is(['Post'])) {
			$user = $this->Users->findByUsername($this->request->data['username'], [
				'fields' => 'email'
			]);
			
			$user = $user->first();
			
			if($user) {
				$ticket = Security::hash($this->generateRandomString() . $this->request->data['username'], 'sha256', true);
				$key = $this->generateRandomString(6);
				
				$resetTable = TableRegistry::get('UsersResetPasswords');
				$reset = $resetTable->newEntity();
				$reset->user_id = 10;
				$reset->ticket = $ticket;
				$reset->reset = $key;
				$reset->time = Time::now('America/New_York');
				
				if($resetTable->save($reset)) {
				
					$email = new Email('default');
					$email->from(['djj211.tveditor@gmail.com' => 'TV Admin'])
						->to($user->email)
						->emailFormat('html')
						->subject('TvEditor Password Reset');
					if ($email->send('To reset your password please navigate to the url below and enter the following key: 
								<b>' . $key . '</b><br /><br />		
							<a href="http://djjserver.duckdns.org:8080/tveditor/users/reset?t=' . $ticket . '">Click here to reset your password</a><br /><br />
							Or copy and paste the following: <br /><br />
							http://djjserver.duckdns.org:8080/tveditor/users/reset?t=' . $ticket . '<br /><br />Thank you, <br /><br /> Your System Admin
						')) {
						$this->Flash->success(__('An email has been sent to your account. Please follow instructions in that email.'));
						return $this->redirect(['action' => 'login']);
					}
					else {
						$this->Flash->error(__('Error. Please try again'));
					}
				}
				else {
					$this->Flash->error(__('Error. Please try again'));
				}
			}			
			else {
				$this->Flash->error(__('Could not find username. Please try again'));
			}
		}
	}

	public function reset() {
		$this->isloggedIn();
		
		$this->viewBuilder()->layout('login');
				
		if ($this->request->is(['patch', 'post', 'put'])) {
			$valid_ticket = new Time('10 minutes ago', 'America/New_York');
			$resetTable = TableRegistry::get('UsersResetPasswords');
			$reset = $resetTable->find('all');
			$reset->select('user-id');
			$reset->where(['ticket' => $this->request->query('t'), 'time >=' => $valid_ticket]);
			$reset = $reset->first();
			if($reset) {
				if ($reset->reset == $this->request->data['users_reset_password']['reset']) {
						
					$user = $this->Users->get($reset->user_id);
					
					$data = [
						'password' =>  $this->request->data['password'],
						'confirm_password' => $this->request->data['confirm_password']
					];

					$user = $this->Users->patchEntity($user, $data);
					
					if ($this->Users->save($user)) {
		                $this->Flash->success(__('Successfully Reset Your Password! Please Login.'));
		                $this->clearReset($reset->user_id);
		                return $this->redirect(['action' => 'login']);
	           	 	}
	            	$this->Flash->error(__('Unable to update the password. Please Review any Errors.'));
	        
				}	
				else {
					$this->Flash->error(__('Your Key is Invalid. Please try again.'));
				}
			}
			else {
				$this->viewBuilder()->layout('expired');
				$this->Flash->error(__('Expired: The page you tried to access has expired. Please try again.'));
				return $this->redirect(['action' => 'login']);
			}
		}
		else {
			if ($this->request->query('t')) {
				$valid_ticket = new Time('10 minutes ago', 'America/New_York');
				$resetTable = TableRegistry::get('UsersResetPasswords');
				$reset = $resetTable->find('all');
				$reset->select('user_id');
				$reset->where(['ticket' => $this->request->query('t'), 'time >=' => $valid_ticket]);
				$reset = $reset->first();
				
				if($reset) {
					$user = $this->Users->get($reset->user_id, [
						'fields' => 'id'
					]);
				}
				else {
					$this->viewBuilder()->layout('expired');
					$this->Flash->error(__('Expired: The page you tried to access has expired. Please try again.'));
					return $this->redirect(['action' => 'login']);
				}
			}
			else {
				$this->viewBuilder()->layout('expired');
				$this->Flash->error(__('Expired: The page you tried to access has expired. Please try again.'));
				return $this->redirect(['action' => 'login']);
			}
		}

		$this->set('user', $user);
	}

	public function isloggedIn() {
		$user = $this->Auth->user('id');
	    if ($user) {;
	    	return $this->redirect($this->Auth->redirectUrl());
	    }
	}
}
?>