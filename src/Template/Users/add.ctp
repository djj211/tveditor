<div class="users center">
<?= $this->Form->create($user) ?>
    <fieldset>
        <div class="login-form">
        	<div class="login-info">
        		Add New User's Username, Password, Email, and Role:
        	<div class="login-field">
        		<?= $this->Form->input('username', ['class' => 'glowing-border']) ?>
        	</div>
	        <div class="login-field">
	        	<?= $this->Form->input('password', ['class' => 'glowing-border']) ?>
	        </div>
	        <div class="login-field">
	        	<?= $this->Form->input('confirm_password', ['type' => 'password', 'class' => 'glowing-border']) ?>
	        </div>
	        <div class="login-field">
	        	<?= $this->Form->input('email', ['class' => 'glowing-border']) ?>
	        </div>
	        <div class="login-field">
        		<?= $this->Form->input('role', [
		            'options' => ['manage' => 'Manage', 'read' => 'Read', 'admin' => 'Admin'],
		             'class' => 'glowing-border'
       			]) ?>
       		</div>
	        <div class="login-field">
        		<?= $this->Form->input('users_preference.theme', [
		            'options' => ['Default' => 'Default', 'Fire' => 'Fire', 'Forest' => 'Forest', 'Ice' => 'Ice', 'Midnight' => 'Midnight'],
		             'class' => 'glowing-border'
       			]) ?>
       		</div>
       		<div class="login-field">
        		<?= $this->Form->input('users_preference.plex_id', ['type' => 'text', 'class' => 'glowing-border']) ?>
        	</div>
       		<?= $this->Form->button('Apply', ['type' => 'submit', 'class' => 'usrs-btns', 'id' => 'add_usr']); ?>
       		</div>
       	</div>
   </fieldset>
<?= $this->Form->end() ?>
</div>