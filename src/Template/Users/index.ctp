<script>
	var user = "<?php echo $role; ?>";
</script>
<div class="center center-users">
<?php echo $this->Html->link('Add New User', '/users/add', ['class' => 'modern-link', 'id' => 'usr_add']); ?>
	<div class="user-wrap selectDisable">
		<div class="inline-row">
			<div class="user-block">User</div>
			<div class="user-block">Email</div>
			<div class="user-block">Role</div>
			<div class="date-block">Modified</div>
			<div class="date-block">Created</div>
			<div class="edit-block"></div>
			<div class="edit-block"></div>
		</div>
		<?php if(!empty($users)): foreach($users as $user): ?>
			<div class="inline-row">
				<div class="user-block"><?php echo $user->username; ?></div>
				<div class="user-block"><?php echo $user->email; ?></div>
				<div class="user-block"><?php echo $user->role; ?></div>
				<div class="date-block"><?php echo $user->modified->format('m-d-Y h:i:sA'); ?></div>
				<div class="date-block"><?php echo $user->created->format('m-d-Y h:i:sA'); ?></div>
				<div class="edit-block">
					<?php echo $this->Html->link('Edit', '/users/edit/' . $user->id, ['id' => 'user_edit', 'class' => 'modern-user edit-usr']); ?>
				</div>
				<div class="edit-block">			
					<?php echo $this->Html->link('Del', '#users/delete/' . $user->id, ['id' => 'user_del', 'class' => 'modern-user edit-usr']); ?>
				</div>
			</div>
	<?php endforeach; else: ?>
	No Users Found
	<?php endif; ?>
	</div>
</div>
<div id="confirmBox"></div>
