<div class="users center">
<?= $this->Flash->render('auth') ?>
<?= $this->Form->create() ?>
    <fieldset>
        <div class="login-form">
        	<div class="login-info">
        	Please Enter Your Username And Password:
        	<div class="login-field">
	        	<?= $this->Form->input('username', ['class' => 'glowing-border']) ?>
	        </div>
	        <div class="login-field">
	        	<?= $this->Form->input('password', ['class' => 'glowing-border']) ?>
	        </div>
	        <div class="login-field">
	        	<?= $this->Form->input('keep_me_logged_id', ['type' => 'checkbox']) ?>
	        </div>
	        <?= $this->Form->button('Login', ['type' => 'submit', 'class' => 'usrs-btns', 'id' => 'login']); ?>
			<div class="login-field forgot_pw_link">
			<?= $this->Html->link('Forgot Password', '/users/forgot') ?>
			</div>
       		</div>
       </div>
    </fieldset>
<?= $this->Form->end() ?>
</div>
