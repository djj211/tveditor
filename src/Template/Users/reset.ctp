<div class="users center">
<?php echo $this->Form->create($user, [
	'context' => ['validator' => 'reset']
]) ?>
    <fieldset>
        <div class="login-form">
        	<div class="login-info">
        	Edit User's Username, Password, Email, or Role:
        	<div class="login-field">
	        	<?= $this->Form->input('users_reset_password.reset', ['class' => 'glowing-border', 'type' => 'password', 'value' => '']) ?>
	        </div>
	        <div class="login-field">
	        	<?= $this->Form->input('password', ['class' => 'glowing-border', 'value' => '']) ?>
	        </div>
	        <div class="login-field">
	        	<?= $this->Form->input('confirm_password', ['type' => 'password', 'class' => 'glowing-border', 'value' => '']) ?>
	        </div>
	        <?= $this->Form->button('Apply', ['type' => 'submit', 'class' => 'usrs-btns', 'id' => 'usr_acct']); ?>
       		</div>
       </div>
    </fieldset>
<?= $this->Form->end() ?>
</div>